<?php

namespace Lkn\HookNotification\Core\WHMCS;

use Lkn\HookNotification\Core\Shared\Infrastructure\Singleton;

final class ApiHandler extends Singleton {
    /**
     * @var array<string, array<int, string>>
     */
    private readonly array $endpoints;

    public function __construct()
    {
        $this->endpoints = [
            'password/reset' => [
                PasswordResetService::class,
                'run',
            ],
            'login/otp/send' => [
                LoginOtpService::class,
                'send',
            ],
            'login/otp/verify' => [
                LoginOtpService::class,
                'verify',
            ],
            'login/magic-link/verify' => [
                LoginOtpService::class,
                'verifyMagicLink',
            ],
        ];
    }

    public function routeEndpoint(string $endpoint): void
    {
        $urlParts = parse_url($endpoint);
        $path     = $urlParts['path'] ?? $endpoint;

        /** @var array<string, string> $queryParams */
        parse_str($urlParts['query'] ?? '', $queryParams);

        // Aceita também parâmetros top-level (?endpoint=...&phone=...&otp=...): o `&`
        // separa os params do endpoint na query string — necessário p/ verify (2+ params).
        foreach ($_GET as $key => $value) {
            if ($key === 'endpoint') {
                continue;
            }

            if (!array_key_exists($key, $queryParams)) {
                $queryParams[$key] = $value;
            }
        }

        $match = $this->extractRouteParams($path);


        if ($match) {
            [$class, $method] = $this->endpoints[$match['matchedEndpoint']];
            $params           = $match['endpointParams'];

            $params = array_merge($match['endpointParams'], $queryParams);

            $classOutput = $this->invoke($class, $method, $params);

            echo json_encode($classOutput, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

            http_response_code(200);
            Header('Content-Type: application/json');
        } else {
            // Fase 3 (observabilidade): rota inválida -> 404 com body JSON (sem corpo vazio).
            http_response_code(404);
            Header('Content-Type: application/json');
            echo json_encode(['error' => 'not_found']);
        }
    }

    /**
     * @param string $received The received endpoint path.
     *
     * @return array{
     *     matchedEndpoint: string,
     *     endpointParams: array<string, string>
     * }|null
     */
    private function extractRouteParams(string $received): ?array
    {
        $endpoints = array_keys($this->endpoints);

        foreach ($endpoints as $endpoint) {
            $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
                return '(?P<' . $matches[1] . '>[^\/]+)';
            }, $endpoint);

            if (preg_match('#^' . $pattern . '$#', $received, $matches)) {
                $namedParams = [];

                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $namedParams[$key] = $value;
                    }
                }

                return [
                    'matchedEndpoint' => $endpoint,
                    'endpointParams' => $namedParams,
                ];
            }
        }

        return null;
    }

    /**
     * @param  string                $class
     * @param  string                $method
     * @param  array<string, string> $params
     *
     * @return array<int|string, mixed>
     */
    private function invoke(string $class, string $method, array $params): array
    {
        $instance = new $class();

        $reflection    = new \ReflectionMethod($instance, $method);
        $orderedParams = [];

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $params)) {
                $orderedParams[] = $params[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $orderedParams[] = $param->getDefaultValue();
            } else {
                throw new \InvalidArgumentException("Missing parameter: $name");
            }
        }

        /** @var array<mixed> $classOutput */
        $classOutput = $reflection->invokeArgs($instance, $orderedParams);

        return $classOutput;
    }

}
