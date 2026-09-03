<?php

namespace Lkn\HookNotification\Core\NotificationReport\Infrastructure;

use DateTime;
use DateTimeZone;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportStatus;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;
use Lkn\HookNotification\Core\Shared\Infrastructure\Repository\BaseRepository;
use WHMCS\Database\Capsule;

final class NotificationReportRepository extends BaseRepository
{
    /**
     * Cached MySQL session offset from UTC (seconds), sampled once per request.
     *
     * @var int|null
     */
    private static ?int $mySqlUtcOffsetSeconds = null;

    public function paginate(int $offset, int $limit, array $filters = [])
    {
        $reportsQuery = $this->query->table('mod_lkn_hook_notification_reports');
        $this->applyFilters($reportsQuery, $filters);

        $reports = $reportsQuery
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $countQuery = $this->query->table('mod_lkn_hook_notification_reports');
        $this->applyFilters($countQuery, $filters);

        $totalReports = $countQuery->count();

        return [
            'reports' => $reports->toArray(),
            'totalReports' => $totalReports,
        ];
    }

    /**
     * Counts by status for the KPI cards (status breakdown over the selected period).
     *
     * @return array{total: int, sent: int, not_sent: int, error: int, resent: int}
     */
    public function getCountsByStatus(array $filters, string $period = '7d'): array
    {
        $query = $this->query->table('mod_lkn_hook_notification_reports');
        $this->applyFilters($query, $filters, true);
        $this->applyPeriod($query, $period);

        $rows = $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        $counts = [
            'total' => 0,
            'sent' => 0,
            'not_sent' => 0,
            'error' => 0,
            'resent' => 0,
        ];

        foreach ($rows as $row) {
            $status = (string) $row->status;

            if (isset($counts[$status])) {
                $counts[$status] = (int) $row->total;
            }

            $counts['total'] += (int) $row->total;
        }

        return $counts;
    }

    /**
     * Distinct notification codes present in the table, for the filter select.
     *
     * @return array<string>
     */
    public function getDistinctNotifications(): array
    {
        return $this->query
            ->table('mod_lkn_hook_notification_reports')
            ->select('notification')
            ->whereNotNull('notification')
            ->where('notification', '!=', '')
            ->distinct()
            ->orderBy('notification', 'asc')
            ->get()
            ->pluck('notification')
            ->toArray();
    }

    public function insertReport(
        int $clientId,
        ?int $categoryId,
        ?NotificationReportCategory $reportCategory,
        NotificationReportStatus $reportStatus,
        ?string $reportMsg,
        ?Platforms $platform,
        string $notificationCode,
        ?Hooks $hook,
        ?int $queueId,
        ?string $target,
    ): int {
        // TODO: add also which NotificationTemplate and whmcsHookParams to allow resend?
        return $this->query->table('mod_lkn_hook_notification_reports')
            ->insert([
                'client_id' => $clientId,
                'category_id' => $categoryId,
                'category' => $reportCategory->value,
                'status' => $reportStatus->value,
                'msg' => $reportMsg,
                'platform' => $platform->value,
                'channel' => null,
                'notification' => $notificationCode,
                'hook' => $hook ? $hook->value : null,
                'queue_id' => $queueId,
                'target' => $target,
            ]);
    }

    public function getReportsForCategory(
        NotificationReportCategory $category,
        int $categoryId
    ): array {
        return $this->query
            ->table('mod_lkn_hook_notification_reports')
            ->orderBy('created_at', 'desc')
            ->where('category', $category->value)
            ->where('category_id', $categoryId)
            ->get()
            ->toArray();
    }

    public function getReportsForLastHour()
    {
        $oneHourAgo = (new DateTime())->modify('-1 hour')->format('Y-m-d H:i:s');

        return $this->query
            ->table('mod_lkn_hook_notification_reports')
            ->where('created_at', '>=', $oneHourAgo)
            ->count();
    }

    public function getFailedReports()
    {
        $oneHourAgo = (new DateTime())->modify('-1 hour')->format('Y-m-d H:i:s');

        return $this->query
            ->table('mod_lkn_hook_notification_reports')
            ->where('created_at', '>=', $oneHourAgo)
            ->where('status', '!=', NotificationReportStatus::SENT->value)
            ->count();
    }

    public function getTopNotificationsForLastHour()
    {
        $oneHourAgo = (new DateTime())->modify('-1 hour')->format('Y-m-d H:i:s');

        return $this->query
            ->table('mod_lkn_hook_notification_reports')
            ->select(
                'notification',
                $this->query::table('mod_lkn_hook_notification_reports')->raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $oneHourAgo)
            ->groupBy('notification')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Epoch timestamps (UTC) of every report in the period window, so the
     * application layer can group them day/hour in the PHP timezone without
     * depending on the MySQL session timezone.
     *
     * @return array<int>
     */
    public function getReportTimestamps(array $filters, string $period): array
    {
        $query = $this->query->table('mod_lkn_hook_notification_reports');
        $this->applyFilters($query, $filters, true);
        $this->applyPeriod($query, $period);

        return $query
            ->selectRaw('UNIX_TIMESTAMP(created_at) AS ts')
            ->get()
            ->map(static fn ($row) => (int) $row->ts)
            ->all();
    }

    /**
     * Top notification codes by volume in the period.
     *
     * @return array<int, array{notification: string, total: int}>
     */
    public function getTopNotifications(array $filters, string $period, int $limit = 10): array
    {
        $query = $this->query->table('mod_lkn_hook_notification_reports');
        $this->applyFilters($query, $filters, true);
        $this->applyPeriod($query, $period);

        return $query
            ->select('notification', Capsule::raw('COUNT(*) AS total'))
            ->whereNotNull('notification')
            ->where('notification', '!=', '')
            ->groupBy('notification')
            ->orderBy('total', 'desc')
            ->orderBy('notification', 'asc')
            ->limit($limit)
            ->get()
            ->map(static fn ($row) => [
                'notification' => (string) $row->notification,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * Top error messages (status=error) by volume in the period.
     *
     * @return array<int, array{msg: string, total: int}>
     */
    public function getTopErrorMessages(array $filters, string $period, int $limit = 10): array
    {
        $query = $this->query->table('mod_lkn_hook_notification_reports');
        $this->applyFilters($query, $filters, true);
        $this->applyPeriod($query, $period);

        return $query
            ->select('msg', Capsule::raw('COUNT(*) AS total'))
            ->where('status', NotificationReportStatus::ERROR->value)
            ->whereNotNull('msg')
            ->where('msg', '!=', '')
            ->groupBy('msg')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get()
            ->map(static fn ($row) => [
                'msg' => (string) $row->msg,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * Applies whitelisted filters to a query builder. Strict, no raw SQL.
     *
     * @param  mixed $query
     * @param  array $filters
     * @param  bool  $forKpi  When true, skips `status` and absolute date filters (the period drives the KPI window).
     */
    private function applyFilters($query, array $filters, bool $forKpi = false): void
    {
        if (!$forKpi && !empty($filters['status'])) {
            $status = NotificationReportStatus::tryFrom($filters['status']);

            if ($status !== null) {
                $query->where('status', $status->value);
            }
        }

        if (!empty($filters['platform'])) {
            $platform = Platforms::tryFrom($filters['platform']);

            if ($platform !== null) {
                $query->where('platform', $platform->value);
            }
        }

        if (!empty($filters['category'])) {
            $category = NotificationReportCategory::tryFrom($filters['category']);

            if ($category !== null) {
                $query->where('category', $category->value);
            }
        }

        if (!empty($filters['notification'])) {
            $query->where('notification', $filters['notification']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', (int) $filters['client_id']);
        }

        if (!empty($filters['q'])) {
            $search = $filters['q'];

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('msg', 'like', "%{$search}%")
                    ->orWhere('target', 'like', "%{$search}%");
            });
        }

        if (!$forKpi) {
            if (!empty($filters['date_from'])) {
                $from = $this->toMySqlDateTime($filters['date_from'], false);

                if ($from !== null) {
                    $query->where('created_at', '>=', $from);
                }
            }

            if (!empty($filters['date_to'])) {
                $to = $this->toMySqlDateTime($filters['date_to'], true);

                if ($to !== null) {
                    $query->where('created_at', '<=', $to);
                }
            }
        }
    }

    /**
     * Applies a relative period window using SQL NOW() so it is independent of
     * any PHP/MySQL timezone misalignment.
     */
    private function applyPeriod($query, string $period): void
    {
        $interval = match ($period) {
            '24h' => '24 HOUR',
            '30d' => '30 DAY',
            default => '7 DAY',
        };

        $query->where('created_at', '>=', Capsule::raw("NOW() - INTERVAL {$interval}"));
    }

    /**
     * Converts a date-only string (Y-m-d) in the PHP timezone into a MySQL
     * session-timezone datetime string, using the sampled MySQL UTC offset.
     *
     * @param  string $value    Date string as typed by the admin.
     * @param  bool   $endOfDay When true, snap to 23:59:59 (date_to), else 00:00:00 (date_from).
     */
    private function toMySqlDateTime(string $value, bool $endOfDay): ?string
    {
        $timezone = new DateTimeZone(date_default_timezone_get());

        $date = DateTime::createFromFormat('Y-m-d', $value, $timezone);

        if ($date === false) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $value, $timezone);
        }

        if ($date === false) {
            return null;
        }

        $date->setTime($endOfDay ? 23 : 0, $endOfDay ? 59 : 0, $endOfDay ? 59 : 0);

        $mysqlEpoch = $date->getTimestamp() + $this->mySqlUtcOffsetSeconds();

        return gmdate('Y-m-d H:i:s', $mysqlEpoch);
    }

    private function mySqlUtcOffsetSeconds(): int
    {
        if (self::$mySqlUtcOffsetSeconds !== null) {
            return self::$mySqlUtcOffsetSeconds;
        }

        $row = $this->query
            ->table('mod_lkn_hook_notification_reports')
            ->selectRaw('TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), NOW()) AS offset_seconds')
            ->limit(1)
            ->first();

        self::$mySqlUtcOffsetSeconds = (int) ($row->offset_seconds ?? 0);

        return self::$mySqlUtcOffsetSeconds;
    }
}
