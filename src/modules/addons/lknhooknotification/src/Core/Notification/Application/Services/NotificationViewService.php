<?php

namespace Lkn\HookNotification\Core\Notification\Application\Services;

use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\Notification\Infrastructure\NotificationTemplateRenderers\MetaWhatsAppTemplateRenderer;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;

final class NotificationViewService
{
    private MetaWhatsAppTemplateRenderer $metaWhatsAppTemplateRenderer;
    private View $view;

    public function __construct(View $view)
    {
        $this->view = new View();
        $this->view->setTemplateDir(__DIR__ . '/../../Http/Views');
        $this->metaWhatsAppTemplateRenderer =  new MetaWhatsAppTemplateRenderer($this->view);
    }

    public function findTemplateByLang(
        AbstractNotification $notification,
        string $lang
    ): ?NotificationTemplate {
        return current(
            array_filter(
                $notification->templates,
                fn(NotificationTemplate $template) => $template->lang === $lang
            )
        ) ?: null;
    }

    public function findTemplateByLangAndPlatform(
        AbstractNotification $notification,
        string $lang,
        Platforms $platform
    ): ?NotificationTemplate {
        return current(
            array_filter(
                $notification->templates,
                fn(NotificationTemplate $template) =>
                    $template->lang === $lang && $template->platform === $platform
            )
        ) ?: null;
    }

    public function getTemplateEditorForPlatform(
        AbstractNotification $notification,
        ?NotificationTemplate $template,
        bool $disableTemplateEditorChanges = false,
    ): string {
        if ($template->platform === Platforms::WHATSAPP) {
            return $this->metaWhatsAppTemplateRenderer->render($notification, $template, $disableTemplateEditorChanges);
        }

        return $this->view->view('template_editors/standard_template_editor', [
            'editing_notification' => $notification,
            'editing_template' => $template,
        ])->render();
    }
}
