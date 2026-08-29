<?php

use Lkn\HookNotification\Core\WHMCS\ApiHandler;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../init.php';
require_once __DIR__ . '/../Core/Shared/param_funcs.php';
require_once __DIR__ . '/Shared/Infrastructure/helpers.php';

try {
    // Fase 1 (hardening): accept POST only.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Header('Content-Type: application/json');
        echo json_encode(['error' => 'method_not_allowed']);
        exit;
    }

    // Fase 1 (hardening): validate the WHMCS client area CSRF token.
    if (!function_exists('check_token') || !check_token()) {
        http_response_code(403);
        Header('Content-Type: application/json');
        echo json_encode(['error' => 'invalid_token']);
        exit;
    }

    // Fase 1 (hardening): same-origin check (Origin or Referer must match SystemURL host).
    $systemUrl   = \WHMCS\Database\Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');
    $systemHost  = is_string($systemUrl) && $systemUrl !== '' ? parse_url($systemUrl, PHP_URL_HOST) : null;
    $origin      = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer     = $_SERVER['HTTP_REFERER'] ?? '';
    $requestHost = null;

    if (is_string($origin) && $origin !== '') {
        $requestHost = parse_url($origin, PHP_URL_HOST);
    } elseif (is_string($referer) && $referer !== '') {
        $requestHost = parse_url($referer, PHP_URL_HOST);
    }

    if (
        $requestHost === null
        || $systemHost === null
        || strcasecmp((string) $requestHost, (string) $systemHost) !== 0
    ) {
        http_response_code(403);
        Header('Content-Type: application/json');
        echo json_encode(['error' => 'invalid_origin']);
        exit;
    }

    /** @var string $endpoint */
    $endpoint = $_GET['endpoint'] ?? '';

    if (empty($endpoint)) {
        echo 'empty endpoint';
    } else {
        ApiHandler::getInstance()->routeEndpoint($endpoint);
    }
} catch (Throwable $th) {
    lkn_hn_log('API error', ['exception' => $th->__toString()]);

    // Never return an empty body: surface a JSON error so the client can handle it
    // and the real exception message is visible for diagnosis.
    http_response_code(500);
    Header('Content-Type: application/json');
    echo json_encode([
        'error' => 'internal_error',
        'debug' => $th->getMessage(),
    ]);
}
