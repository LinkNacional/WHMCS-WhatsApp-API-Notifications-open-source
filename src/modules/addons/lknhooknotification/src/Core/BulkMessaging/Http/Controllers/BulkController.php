<?php

namespace Lkn\HookNotification\Core\BulkMessaging\Http\Controllers;

use DateTime;
use Lkn\HookNotification\Core\BulkMessaging\Application\Services\BulkService;
use Lkn\HookNotification\Core\BulkMessaging\Domain\BulkNotification;
use Lkn\HookNotification\Core\BulkMessaging\Domain\BulkStatus;
use Lkn\HookNotification\Core\BulkMessaging\Domain\ClientProductStatus;
use Lkn\HookNotification\Core\BulkMessaging\Http\NewBulkRequest;
use Lkn\HookNotification\Core\Notification\Application\Services\NotificationViewService;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\Platforms\Common\Application\PlatformService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;

final class BulkController extends BaseController
{
    private readonly PlatformService $platformService;
    private readonly BulkService $bulkService;
    private readonly NotificationViewService $notificationViewService;

    public function __construct(View $view)
    {
        parent::__construct($view);
        $this->platformService         = new PlatformService();
        $this->bulkService             = new BulkService();
        $this->notificationViewService = new NotificationViewService($this->view);
    }

    /**
     * @param  array<string, string> $request
     *
     * @return void
     */
    public function viewBulkMessageList(array $request)
    {
        if (lkn_hn_is_post_request() && isset($request['send-now']) && !empty($request['bulk-id'])) {
            /** @var int $bulkId */
            $bulkId = $request['bulk-id'];

            $result = $this->bulkService->sendNow($bulkId);

            $this->view->alert(
                $result ? 'success' : 'danger',
                $result
                    ? lkn_hn_lang('The bulk #[1] was set to start in the next cron.', [$bulkId])
                    : lkn_hn_lang('The bulk #[1] was not.', [$bulkId])
            );
        }

        $bulks = $this->bulkService->getBulks();

        $viewParams = [
            'bulks' => $bulks,
        ];

        $this->view->view(
            'pages/bulk_list',
            $viewParams
        );
    }

    /**
     * @param  array<string, string|array<string>> $request Comes from edit_create_bulk.tpl
     *
     * @return void
     */
    public function viewNewBulkMessage(array $request): void
    {
        /** @var string $title */
        $title = $request['title'];

        /** @var string $platform */
        $platform = $request['platform'];

        /** @var string $dateToSend */
        $dateToSend = $request['date-to-send'];

        /** @var string $description */
        $description = $request['description'];

        /** @var string $template */
        $template = $request['template'];

        /** @var array<string, array<string>> $filters */
        $filters = $request;

        /** @var int $maxConcurrency */
        $maxConcurrency = intval($request['max-concurrency'] ?? 25);

        $newBulkRequest = new NewBulkRequest(
            status: BulkStatus::IN_PROGRESS,
            title: $title,
            descrip: $description,
            platform: Platforms::tryFrom($platform),
            startAt: $dateToSend ? new DateTime($dateToSend) : null,
            maxConcurrency: $maxConcurrency,
            filters: NewBulkRequest::parseFiltersFromRequest($filters),
            template: $template,
        );

        $clientOptions = null;

        if (
            isset($request['get-matched-clients'])
            || isset($request['create-bulk'])
            || !empty($request['not-sending-clients'])
        ) {
            $clientOptions = $this->bulkService->getClientsByFilter(
                $newBulkRequest->filters,
                allClients: true,
            );
        }

        if (lkn_hn_is_post_request() && isset($request['create-bulk'])) {
            if (empty($clientOptions)) {
                $this->view->alert(
                    'warning',
                    lkn_hn_lang('No client matched the filters. Please, adjust the filters to match at least 2 clients.')
                );
            } else {
                $result = $this->bulkService->createBulk($newBulkRequest, $request);

                if ($result->code === 'success') {
                    header("Location: addonmodules.php?module=lknhooknotification&page=bulks/{$result->data['bulk_id']}");

                    exit;
                }

                $this->view->alert(
                    'success',
                    lkn_hn_lang('Bulk saved.')
                );
            }
        }

        $editingNotification = new BulkNotification();
        $editingTemplate     = new NotificationTemplate(
            $newBulkRequest->platform,
            null,
            $request['message-template'] ?? '',
            []
        );

        $viewParams = [
            'field_options' => [
                'whmcs_client_lang' => lkn_hn_get_language_locales_for_view(),
                'whmcs_client_statuses' => [
                    ['label' => lkn_hn_lang('Active'), 'value' => 'Active'],
                    ['label' => lkn_hn_lang('Inactive'), 'value' => 'Inactive'],
                    ['label' => lkn_hn_lang('Closed'), 'value' => 'Closed'],
                ],
                'whmcs_client_countries' => lkn_hn_get_client_countries_for_view(),
                'whmcs_products' => lkn_hn_get_products_for_view(),
                'whmcs_client_product_status' => ClientProductStatus::forView(),
                'platform_options' => $this->platformService->getEnabledPlatforms(standardOnly: true),
                'client_options' => $clientOptions,
            ],
            'state' => $newBulkRequest,
            'editing_notification' => new BulkNotification(),
            'template_editor_view' => $this->notificationViewService->getTemplateEditorForPlatform(
                $editingNotification,
                $editingTemplate
            ),
        ];

        $this->view->view('pages/edit_create_bulk', $viewParams);
    }

    public function viewEditBulk(int $bulkId, array $request): void
    {
        $bulk = $this->bulkService->getBulk($bulkId);

        if (
            lkn_hn_is_post_request()
            && isset($request['bulk-status'])
            && $bulk->status->value !== $request['bulk-status']
        ) {
            $result = $this->bulkService->updateBulkStatus(
                $bulkId,
                BulkStatus::from($request['bulk-status']),
            );

            if ($result) {
                $this->view->alert(
                    'success',
                    lkn_hn_lang('The bulk status was changed.')
                );
            } else {
                $this->view->alert(
                    'warning',
                    lkn_hn_lang('Could not change the status of the bulk.')
                );
            }

            unset($_POST['bulk-status']);
        }

        if (lkn_hn_is_post_request() && !empty($request['resend-notification'])) {
            /** @var int $bulkMessageId */
            $bulkMessageId = $request['resend-notification'];

            $result = $this->bulkService->resendBulkMessage($bulkId, $bulkMessageId);

            if ($result->code === 'success') {
                $this->view->alert(
                    'success',
                    $result->msg ? $result->msg : '',
                );
            } else {
                /** @var string $error */
                $error = $result->errors ? $result->errors['exception'] : null;

                $this->view->alert(
                    'warning',
                    $result->msg ? $result->msg : '',
                    error: $error
                );
            }
        }

        $bulk = $this->bulkService->getBulk($bulkId);

        $newBulkRequest = new NewBulkRequest(
            status: $bulk->status,
            title: $bulk->title,
            descrip: $bulk->description,
            platform: $bulk->platform,
            startAt: $bulk->startAt,
            maxConcurrency: $bulk->maxConcurrency,
            filters: $bulk->filters,
            template: $bulk->template,
        );


        $clientOptions = $this->bulkService->getClientsByFilter(
            $newBulkRequest->filters
        );

        $bulkNotificationsList = $this->bulkService->getBulkReportForView($bulk->id);

        $editingNotification = new BulkNotification();
        $editingTemplate     = new NotificationTemplate(
            $newBulkRequest->platform,
            null,
            $bulk->template ?? '',
            $bulk->platformPayload,
        );

        $viewParams = [
            'mode' => 'edit',
            'field_options' => [
                'bulk_message_status' => BulkStatus::forView(),
                'whmcs_client_lang' => lkn_hn_get_language_locales_for_view(),
                'whmcs_client_statuses' => [
                    ['label' => lkn_hn_lang('Active'), 'value' => 'active'],
                    ['label' => lkn_hn_lang('Inactive'), 'value' => 'inactive'],
                    ['label' => lkn_hn_lang('Closed'), 'value' => 'closed'],
                ],
                'whmcs_client_countries' => lkn_hn_get_client_countries_for_view(),
                'whmcs_products' => lkn_hn_get_products_for_view(),
                'whmcs_client_product_status' => ClientProductStatus::forView(),
                'platform_options' => $this->platformService->getEnabledPlatforms(standardOnly: true),
                'client_options' => $clientOptions,
            ],
            'state' => $newBulkRequest,
            'bulk' => $bulk,
            'bulk_notifications_list' => $bulkNotificationsList,
            'editing_notification' => new BulkNotification(),
            'template_editor_view' => $this->notificationViewService->getTemplateEditorForPlatform(
                $editingNotification,
                $editingTemplate,
                true,
            ),
        ];

        $this->view->view(
            'pages/edit_create_bulk',
            $viewParams
        );
    }
}
