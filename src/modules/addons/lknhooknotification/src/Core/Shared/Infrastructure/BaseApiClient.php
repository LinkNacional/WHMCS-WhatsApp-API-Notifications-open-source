<?php

namespace Lkn\HookNotification\Core\Shared\Infrastructure;

abstract class BaseApiClient
{
    protected function httpRequest(
        string $method,
        string $baseUrl,
        string $endpoint,
        array $headers = [],
        array $body = [],
        array $queryParams = [],
        array $extraCurlOptions = []
    ): ApiResponse {
        $requestUrl = "$baseUrl/$endpoint";

        $queryParamsStr = '';

        if (count($queryParams) > 0) {
            foreach ($queryParams as $key => $value) {
                $queryParamsStr .= "$key=$value&";
            }

            $requestUrl .= '?' . rtrim($queryParamsStr, '&');
        }

        $curlHandle = curl_init();

        $curlOptions = [
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if (in_array($method, ['POST', 'PUT'], true)) {
            if ($body === []) {
                $curlOptions[CURLOPT_POSTFIELDS] = '{}';
            } else {
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode(
                    $body,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }

        if ($extraCurlOptions !== []) {
            $curlOptions = array_merge($curlOptions, $extraCurlOptions);
        }

        curl_setopt_array($curlHandle, $curlOptions);

        $response = curl_exec($curlHandle);

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        curl_close($curlHandle);

        return new ApiResponse(
            $httpCode,
            json_decode($response, true)
        );
    }
}
