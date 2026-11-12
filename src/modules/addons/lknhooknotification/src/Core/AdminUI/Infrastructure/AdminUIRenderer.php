<?php

namespace Lkn\HookNotification\Core\AdminUI\Infrastructure;

use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;
use WHMCS\Database\Capsule;

final class AdminUIRenderer
{
    private array $routes;
    private string $navbarPath;

    public function __construct()
    {
        $this->routes     =  require_once __DIR__ . '/endpoints.php';
        $this->navbarPath = __DIR__ . '/navbar.php';
    }

    public function getView(string $endpoint): string
    {
        $matched = $this->extractRouteParams($endpoint);

        $view = new View();

        $view->assign('csrf_token', lkn_hn_admin_csrf_token());

        if ($matched === null) {
            $view->setTemplateDir(__DIR__ . '/../Http/Views');

            return $view->view('pages/404')->render();
        }

        $matchedEndpoint                      = $matched['matchedEndpoint'];
        $endpointParams                       = $matched['endpointParams'];
        [$controllerClass, $controllerMethod] = $this->routes[$matchedEndpoint]['class'];


        $controllerInstance = new $controllerClass($view);

        if (lkn_hn_is_post_request()) {
            $postParams = json_decode(file_get_contents('php://input'), true);

            if (!is_array($postParams)) {
                $postParams = $_POST;
            }

            $token = $postParams['lkn_hn_token'] ?? null;

            if (
                !lkn_hn_admin_csrf_token_is_valid($token)
                || !lkn_hn_request_is_same_origin()
            ) {
                http_response_code(403);

                return '<div class="alert alert-danger" style="margin-top: 15px;">'
                    . lkn_hn_lang('Invalid security token. Reload the page and try again.')
                    . '</div>';
            }

            unset($postParams['lkn_hn_token'], $postParams['token']);

            // State-changing requests must carry their parameters in the body.
            // $_GET is never merged here: merging it let a plain link overwrite
            // stored settings and trigger other admin actions.
            $requestParams = $postParams;
        } else {
            $requestParams = $_GET;

            unset($requestParams['module'], $requestParams['page']);
        }

        $request = ['request' => $requestParams];

        $controllerMethodParams = array_merge($endpointParams, $request);

        $controllerOutput = call_user_func(
            [$controllerInstance, $controllerMethod],
            ...$controllerMethodParams
        );

        // A controller may have issued a redirect and returned. In that case
        // there is no template to render; rendering would access an
        // uninitialized View::$renderFilename.
        foreach (headers_list() as $header) {
            if (stripos($header, 'Location:') === 0) {
                return '';
            }
        }

        $controllerInstance->view->assign('lkn_hn', [
            'system_url' => Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->first(['value'])->value,
            'navbar' => require $this->navbarPath,
            'current_endpoint' => $endpoint,
        ]);

        return $controllerInstance->view->render();
    }

    private function extractRouteParams(string $received): ?array
    {
        $endpoints = array_keys($this->routes);

        foreach ($endpoints as $endpoint) {
            $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
                return '(?P<' . $matches[1] . '>[^\/]+)';
            }, $endpoint);

            if (preg_match('#^' . $pattern . '$#', $received, $matches)) {
                return [
                    'matchedEndpoint' => $endpoint,
                    'endpointParams' => array_values(array_filter(
                        $matches,
                        'is_string',
                        ARRAY_FILTER_USE_KEY
                    )),
                ];
            }
        }

        return null;
    }
}
