<?php

namespace Lkn\HookNotification\Core\NotificationReport\Application;

use DateTime;
use DateTimeZone;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReport;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportStatus;
use Lkn\HookNotification\Core\NotificationReport\Infrastructure\NotificationReportRepository;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;

final class NotificationReportService
{
    private NotificationReportRepository $notificationReportRepository;

    public function __construct()
    {
        $this->notificationReportRepository = new NotificationReportRepository();
    }

    /**
     * @param  integer $reportsPerPage
     * @param  integer $currentPage
     * @param  array   $filters
     *
     * @return NotificationReport[]
     */
    public function getReportsForView(int $reportsPerPage, int $currentPage, array $filters = []): array
    {
        $offset = ($currentPage - 1) * $reportsPerPage;

        $repoResponse = $this->notificationReportRepository->paginate($offset, $reportsPerPage, $filters);

        $reports = array_map(function ($row) {
            return new NotificationReport(
                $row->id,
                $row->client_id ?? null,
                $row->category_id ?? null,
                $row->category ? NotificationReportCategory::from($row->category) : null,
                NotificationReportStatus::from($row->status),
                $row->msg,
                $row->platform ? Platforms::tryFrom($row->platform) : null,
                $row->notification,
                $row->hook ? Hooks::tryFrom($row->hook) : null,
                new DateTime($row->created_at),
                $row->target,
            );
        }, $repoResponse['reports']);

        return [
            'reports' => $reports,
            'totalReports' => $repoResponse['totalReports'],
        ];
    }

    /**
     * KPI cards: total, sent, not sent, error and success rate over the selected period.
     *
     * @return array{total: int, sent: int, not_sent: int, error: int, success_rate: float}
     */
    public function getKpiStatistics(array $filters, string $period): array
    {
        $counts = $this->notificationReportRepository->getCountsByStatus($filters, $period);

        $successRate = $counts['total'] > 0
            ? round(($counts['sent'] / $counts['total']) * 100, 1)
            : 0.0;

        return [
            'total' => $counts['total'],
            'sent' => $counts['sent'],
            'not_sent' => $counts['not_sent'],
            'error' => $counts['error'],
            'success_rate' => $successRate,
        ];
    }

    /**
     * @return array<string>
     */
    public function getDistinctNotifications(): array
    {
        return $this->notificationReportRepository->getDistinctNotifications();
    }

    public function createReport(
        int $clientId,
        ?int $categoryId,
        ?NotificationReportCategory $reportCategory,
        NotificationReportStatus $reportStatus,
        ?string $reportMsg,
        ?Platforms $platform,
        string $notificationCode,
        ?Hooks $hook,
        ?int $queueId = null,
        ?string $target = null
    ) {
        $insertResult = $this->notificationReportRepository
            ->insertReport(
                $clientId,
                $categoryId,
                $reportCategory,
                $reportStatus,
                $reportMsg,
                $platform,
                $notificationCode,
                $hook,
                $queueId,
                $target
            );

        if (!$insertResult) {
            lkn_hn_log(
                'unable to create report',
                [
                    'clientId' => $clientId,
                    'categoryId' => $categoryId,
                    'reportCategory' => $reportCategory,
                    'reportStatus' => $reportStatus,
                    'reportMsg' => $reportMsg,
                    'platform' => $platform,
                    'notificationCode' => $notificationCode,
                    'hook' => $hook,
                    'queueId' => $queueId,
                    'target' => $target,
                ],
                [
                    'insertResult' => $insertResult,
                ]
            );
        }
    }

    public function getReportsForCategory(
        NotificationReportCategory $category,
        int $categoryId
    ): array {
        $reports = [];

        $rawReports = $this->notificationReportRepository->getReportsForCategory(
            $category,
            $categoryId,
        );

        foreach ($rawReports as $report) {
            $reports[] = new NotificationReport(
                $report->id,
                $report->client_id,
                $report->category_id,
                NotificationReportCategory::tryFrom($report->category),
                NotificationReportStatus::tryFrom($report->status),
                $report->msg,
                Platforms::tryFrom($report->platform),
                $report->notification,
                Hooks::tryFrom($report->hook),
                new DateTime($report->created_at),
                $report->target,
            );
        }

        return $reports;
    }

    public function getStatistics(): array
    {
        return [
            'last_our' => [
                'notifications_sent' => $this->notificationReportRepository->getReportsForLastHour(),
                'failed_sendings' => $this->notificationReportRepository->getFailedReports(),
                'top_notifications' => $this->notificationReportRepository->getTopNotificationsForLastHour(),
            ],
        ];
    }

    /**
     * Aggregates the server-side chart series for the reports view.
     *
     * @return array{
     *   by_day: array<int, array{label: string, total: int, pct: float}>,
     *   by_hour: array<int, array{label: string, total: int, pct: float}>,
     *   top_notifications: array<int, array{notification: string, total: int, pct: float}>,
     *   top_errors: array<int, array{msg: string, total: int, pct: float}>
     * }
     */
    public function getChartData(array $filters, string $period): array
    {
        $timezone = new DateTimeZone(date_default_timezone_get());
        $timestamps = $this->notificationReportRepository->getReportTimestamps($filters, $period);

        $byDayCounts = [];
        $byHourCounts = array_fill(0, 24, 0);

        foreach ($timestamps as $ts) {
            $dt = (new DateTime('@' . $ts))->setTimezone($timezone);
            $dayKey = $dt->format('Y-m-d');
            $byDayCounts[$dayKey] = ($byDayCounts[$dayKey] ?? 0) + 1;
            $byHourCounts[(int) $dt->format('G')] += 1;
        }

        // Fill the day window (inclusive of today) so empty days render as 0.
        $days = $period === '30d' ? 30 : ($period === '24h' ? 1 : 7);
        $now = new DateTime('now', $timezone);

        $byDay = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = (clone $now)->modify("-{$i} days");
            $key = $day->format('Y-m-d');
            $byDay[] = [
                'label' => $day->format('d/m'),
                'title' => $day->format('Y-m-d'),
                'total' => $byDayCounts[$key] ?? 0,
            ];
        }

        $byHour = [];

        for ($h = 0; $h < 24; $h++) {
            $byHour[] = [
                'label' => sprintf('%02d', $h),
                'title' => sprintf('%02d:00', $h),
                'total' => $byHourCounts[$h],
            ];
        }

        return [
            'by_day' => $this->withPercentages($byDay),
            'by_hour' => $this->withPercentages($byHour),
            'top_notifications' => $this->withPercentages(
                $this->notificationReportRepository->getTopNotifications($filters, $period, 10)
            ),
            'top_errors' => $this->withPercentages(
                $this->notificationReportRepository->getTopErrorMessages($filters, $period, 10)
            ),
        ];
    }

    /**
     * Adds a `pct` (percentage of the max value) to each item for bar sizing.
     *
     * @param  array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function withPercentages(array $items): array
    {
        $max = 0;

        foreach ($items as $item) {
            $max = max($max, (int) ($item['total'] ?? 0));
        }

        foreach ($items as &$item) {
            $item['pct'] = $max > 0 ? round(((int) $item['total'] / $max) * 100, 1) : 0.0;
        }

        unset($item);

        return $items;
    }
}
