<?php

namespace Lkn\HookNotification\Core\Shared\Infrastructure\View;

use Smarty;

/**
 * This class is highly tied to the templates (.tpl) files of the project.
 */
final class View
{
    private Smarty $smarty;
    private ?string $renderFilename = null;

    public function __construct()
    {
        $this->smarty = new Smarty();

        $this->smarty->assign('lkn_hn_locales', lkn_hn_get_language_locales_for_view());
        $this->smarty->assign('lkn_hn_custom_fields', lkn_hn_get_client_custom_fields_for_view());
        $this->smarty->assign('lkn_hn_layout_path', __DIR__ . '/../../../AdminUI/Http/Views');
        $this->smarty->assign('lkn_hn_base_endpoint', '?module=lknhooknotification');
        $this->smarty->registerPlugin('function', 'lkn_hn_lang', 'lkn_hn_lang');
    }

    public function setTemplateDir(string $templateDir)
    {
        $this->smarty->setTemplateDir($templateDir);
    }

    public function registerPlugin(string $type, string $name, callable $callback)
    {
        $this->smarty->registerPlugin($type, $name, $callback);
    }

    public function assign(string $name, string|array $value)
    {
        $this->smarty->assign($name, $value);
    }

    /**
     * Adds an alert to the page.
     *
     * Related to layout/alert.tpl
     *
     * @see https://getbootstrap.com/docs/3.4/components/#alerts
     *
     * @param  string $type  - success, info, warning, danger
     * @param  string $msg
     * @param  string $error
     *
     * @return self
     */
    public function alert(string $type, string $msg, ?string $error = null)
    {
        /** @var array<string> */
        $alerts = $this->smarty->getTemplateVars('page_alerts') ?? [];

        $alerts[] = [
            'type' => $type,
            'msg' => $msg,
            'error' => $error,
        ];

        $this->smarty->assign('page_alerts', $alerts);

        return $this;
    }

    /**
     * Renders the page.
     *
     * @param  string $filename
     * @param  array  $params
     *
     * @return self
     */
    public function view(string $filename, array $params = []): self
    {
        $this->smarty->assign('page_params', $params);

        $this->renderFilename = "{$filename}.tpl";

        return $this;
    }

    public function render(): string
    {
        if ($this->renderFilename === null || $this->renderFilename === '') {
            if (function_exists('lkn_hn_log')) {
                lkn_hn_log('View::render called without a template', []);
            }

            return '<div class="alert alert-danger" style="margin:15px;">'
                . 'Unable to render the page. Check the module logs.'
                . '</div>';
        }

        return $this->smarty->fetch($this->renderFilename);
    }
}
