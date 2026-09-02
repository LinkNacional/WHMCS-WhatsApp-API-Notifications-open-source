<?php

namespace Lkn\HookNotification\Core\NotificationReport\Http\Controllers;

use Lkn\HookNotification\Core\NotificationReport\Application\NotificationReportService;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportStatus;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;

final class NotificationReportController extends BaseController
{
    private const ALLOWED_PERIODS = ['24h', '7d', '30d'];

    private NotificationReportService $notificationReportService;

    public function __construct(View $view)
    {
        $this->notificationReportService = new NotificationReportService();

        parent::__construct($view);
    }

    public function viewReports(array $request): void
    {
        $currentPage    = max(1, (int) ($request['pageN'] ?? 1));
        $reportsPerPage = 30;

        $filters = $this->parseFilters($request);
        $period  = $this->parsePeriod($request);

        $reportsForView = $this->notificationReportService->getReportsForView($reportsPerPage, $currentPage, $filters);
        $kpi            = $this->notificationReportService->getKpiStatistics($filters, $period);

        $totalPages = (int) ceil($reportsForView['totalReports'] / $reportsPerPage);

        if ($currentPage > $totalPages && $totalPages > 0) {
            $queryString = $this->buildQueryString($filters, $period, 1);

            header('Location: ?module=lknhooknotification&page=notification-reports' . $queryString);

            exit;
        }

        $viewParams = [
            'reports' => $reportsForView['reports'],
            'current_page' => $currentPage,
            'reports_per_page' => $reportsPerPage,
            'total_reports' => $reportsForView['totalReports'],
            'filters' => $filters,
            'period' => $period,
            'kpi' => $kpi,
            'filters_query_string' => $this->buildQueryString($filters, $period),
            'status_options' => $this->statusOptions(),
            'platform_options' => $this->platformOptions(),
            'category_options' => $this->categoryOptions(),
            'notification_options' => $this->notificationReportService->getDistinctNotifications(),
            'period_options' => [
                '24h' => lkn_hn_lang('Last 24 hours'),
                '7d' => lkn_hn_lang('Last 7 days'),
                '30d' => lkn_hn_lang('Last 30 days'),
            ],
        ];

        $this->view->view('pages/reports', $viewParams);
    }

    /**
     * Reads and whitelists GET filters. Invalid values are ignored (never 500).
     *
     * @return array<string, string|int>
     */
    private function parseFilters(array $request): array
    {
        $filters = [];

        foreach (['status', 'platform', 'notification', 'category', 'date_from', 'date_to', 'q'] as $key) {
            if (isset($request[$key]) && is_string($request[$key]) && trim($request[$key]) !== '') {
                $filters[$key] = trim($request[$key]);
            }
        }

        if (isset($request['client_id']) && is_string($request['client_id']) && trim($request['client_id']) !== '') {
            $filters['client_id'] = (int) $request['client_id'];
        }

        return $filters;
    }

    private function parsePeriod(array $request): string
    {
        $period = isset($request['period']) && is_string($request['period']) ? $request['period'] : '7d';

        return in_array($period, self::ALLOWED_PERIODS, true) ? $period : '7d';
    }

    /**
     * Rebuilds the query string (period + filters + optional pageN) for form
     * repopulation and pagination links, preserving active filters.
     */
    private function buildQueryString(array $filters, string $period, ?int $pageN = null): string
    {
        $params = array_merge(['period' => $period], $filters);

        if ($pageN !== null) {
            $params['pageN'] = $pageN;
        }

        $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');

        $queryString = http_build_query($params);

        return $queryString === '' ? '' : '&' . $queryString;
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        $options = ['' => lkn_hn_lang('All statuses')];

        foreach (NotificationReportStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function platformOptions(): array
    {
        $options = ['' => lkn_hn_lang('All platforms')];

        foreach (Platforms::cases() as $platform) {
            $options[$platform->value] = $platform->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function categoryOptions(): array
    {
        $options = ['' => lkn_hn_lang('All categories')];

        foreach (NotificationReportCategory::cases() as $category) {
            $options[$category->value] = $category->label();
        }

        return $options;
    }
}
