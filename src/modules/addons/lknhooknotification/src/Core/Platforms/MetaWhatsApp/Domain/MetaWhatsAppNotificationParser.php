<?php

namespace Lkn\HookNotification\Core\Platforms\MetaWhatsApp\Domain;

use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\AbstractNotificationParser;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\Shared\Infrastructure\BaseApiClient;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;

/**
 * This should return the platform-api-specific paylod based om
 * NotificationTemplate->platformPayload.
 *
 * @see https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages#components-object
 */
final class MetaWhatsAppNotificationParser extends AbstractNotificationParser
{
    /**
     * The association between the notification parameters and the message
     * template.
     *
     * @since 3.0.0
     */
    private AbstractNotification $notification;

    /**
     * The notification parameters, containing a callback for fetching
     * the parameter value.
     *
     * @since 3.0.0
     */
    private NotificationTemplate $template;

    public function parse(
        AbstractNotification $notification,
        NotificationTemplate $template,
        ?BaseApiClient $apiClient = null
    ): array|Result {
        $this->notification = $notification;
        $this->template     = $template;

        $parsed = [];

        if (!empty($this->template->platformPayload['header'])) {
            $parsedHeader = $this->parseHeader();

            if ($parsedHeader instanceof Result) {
                return $parsedHeader;
            }

            $parsed[] = $parsedHeader;
        }

        if (!empty($this->template->platformPayload['body'])) {
            $parsedBody = $this->parseBody();

            if ($parsedBody instanceof Result) {
                return $parsedBody;
            }

            $parsed[] = $parsedBody;
        }

        if (!empty($this->template->platformPayload['button'])) {
            $parsedButtons = $this->parseButtons();

            if ($parsedButtons instanceof Result) {
                return $parsedButtons;
            }

            $parsed = [...$parsed, ...$parsedButtons];
        }

        return $parsed;
    }

    /**
     * @see https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages#header-object
     *
     * @return array|Result
     */
    private function parseHeader(): array|Result
    {
        $componentesPayload = $this->template->platformPayload;

        $headerParamCode   = $componentesPayload['header'][0]['value'];
        $headerParamParser = $this->notification->parameters->getValueGetterForParameter($headerParamCode);

        $paramReplacement = $headerParamParser();
        $headerType       = strtolower($componentesPayload['header'][0]['type']);

        if (!in_array($headerType, ['text', 'document', 'image'], true)) {
            return new Result(
                code: 'header-not-supported',
                msg: "Header of type '$headerType' is not supported yet.",
                data: [
                    'components' => $componentesPayload,
                    'notificationParameters' => $this->notification->parameters,
                ]
            );
        }

        $parsedHeaderComponent = [
            'type' => 'header',
            'parameters' => [
                [
                    'type' => $headerType,
                ],
            ],
        ];

        switch ($headerType) {
            case 'text':
                $parsedHeaderComponent['parameters'][0] = [
                    ...$parsedHeaderComponent['parameters'][0],
                    'text' => $paramReplacement,
                ];

                break;

            case 'image':
                if (filter_var($paramReplacement, FILTER_VALIDATE_URL) === false) {
                    return new Result(
                        code: 'invalid-image-url',
                        msg: 'Invalid URL for image link: ' . $paramReplacement,
                        data: [
                            'components' => $componentesPayload,
                            'notificationParameters' => $this->notification->parameters,
                        ]
                    );
                }

                $parsedHeaderComponent['parameters'][0] = [
                    ...$parsedHeaderComponent['parameters'][0],
                    'image' => ['link' => $paramReplacement],
                ];

                break;

            case 'document':
                if (filter_var($paramReplacement, FILTER_VALIDATE_URL) === false) {
                    return new Result(
                        code: 'invalid-document-url',
                        msg: 'Invalid URL for document link: ' . $paramReplacement,
                        data: [
                            'components' => $componentesPayload,
                            'notificationParameters' => $this->notification->parameters,
                        ]
                    );
                }

                $parsedHeaderComponent['parameters'][0] = [
                    ...$parsedHeaderComponent['parameters'][0],
                    'document' => ['link' => $paramReplacement, 'filename' => lkn_hn_lang('invoice.pdf')],
                ];

                break;
        }

        return $parsedHeaderComponent;
    }

    private function parseBody(): array|Result
    {
        $bodyComponent = [
            'type' => 'body',
        ];

        $parsedBodyParameters = array_map(
            function ($param): array|Result {
                $paramCode   = $param['value'];
                $paramType   = $param['type'];
                $paramParser = $this->notification->parameters->getValueGetterForParameter($paramCode);

                if (empty($paramParser)) {
                    return new Result(
                        code: 'invalid-document-url',
                        msg: "Parameter $paramCode is not supported by the notification",
                        data: ['paramCode' => $paramCode]
                    );
                }

                $paramReplacement = $paramParser();

                $paramComponent = [
                    'type' => $paramType,
                    $paramType => '',
                ];

                switch ($paramType) {
                    case 'text':
                        $paramComponent[$paramType] = $paramReplacement;

                        break;

                    default:
                        return new Result(
                            code: 'invalid-document-url',
                            msg: "Parameter of type $paramType is not supported for the body component by the notification",
                            data: ['paramCode' => $paramCode]
                        );
                }

                return $paramComponent;
            },
            $this->template->platformPayload['body']
        );

        $invalidParsedParams = array_filter(
            $parsedBodyParameters,
            fn(array|Result $item) => $item instanceof Result
        );

        if (count($invalidParsedParams) > 0) {
            return new Result(
                'body-parsed-with-errors',
                errors: $invalidParsedParams
            );
        }

        $bodyComponent['parameters'] = $parsedBodyParameters;

        return $bodyComponent;
    }

    private function parseButtons(): array|Result
    {
        $parsedButtons = array_map(
            function (array $button): array|Result {
                $btnIndex = $button['index'];
                $btnType  = $button['type'];

                if ($btnType !== 'url') {
                    return new Result(
                        'btn-type-not-supported',
                        msg: "Button of type $btnType is not supported by the module",
                        data: ['button' => $button]
                    );
                }

                $parsedButtonParams = array_map(
                    function (array $paramAssoc): array|Result {
                        $paramType = 'text';
                        $paramCode = $paramAssoc['value'];

                        $paramParser = $this->notification->parameters->getValueGetterForParameter($paramCode);

                        if (empty($paramParser)) {
                            return new Result(
                                'body-parsed-with-errors',
                                msg: "Parameter $paramCode is not supported by the notification",
                                data: ['paramAssoc' => $paramAssoc]
                            );
                        }

                        $paramReplacement = $paramParser();

                        $paramComponent = [
                            'type' => $paramType,
                            $paramType => $paramReplacement,
                        ];

                        return $paramComponent;
                    },
                    $button['params']
                );

                $invalidParsedParam = array_filter(
                    $parsedButtonParams,
                    fn(array|Result $item) => $item instanceof Result
                );

                if (count($invalidParsedParam) > 0) {
                    return new Result(
                        'body-parsed-with-errors',
                        errors: ['invalidParsedParam' => $invalidParsedParam]
                    );
                }

                $buttonComponent = [
                    'type' => 'button',
                    'sub_type' => $btnType,
                    'index' => (int) ($btnIndex),
                    'parameters' => $parsedButtonParams,
                ];

                return $buttonComponent;
            },
            $this->template->platformPayload['button']
        );

        $invalidParsedButtons = array_filter(
            $parsedButtons,
            fn(array|Result $item) => $item instanceof Result
        );

        if (count($invalidParsedButtons) > 0) {
            return new Result(
                'body-parsed-with-errors',
                errors: $invalidParsedButtons
            );
        }

        return $parsedButtons;
    }
}
