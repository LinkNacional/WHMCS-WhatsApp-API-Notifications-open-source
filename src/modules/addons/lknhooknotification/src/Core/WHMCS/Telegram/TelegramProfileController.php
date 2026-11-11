<?php

namespace Lkn\HookNotification\Core\WHMCS\Telegram;

use Lkn\HookNotification\Core\Platforms\Telegram\Application\TelegramLinkService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use WHMCS\Database\Capsule;

final class TelegramProfileController
{
    public function handleFooter(array $vars): ?string
    {
        if (!$this->isProfilePage($vars) || !lkn_hn_config(Settings::TELEGRAM_ENABLE)) {
            return null;
        }

        $clientId = $this->clientId($vars);

        if (!$clientId) {
            return null;
        }

        $service   = new TelegramLinkService();
        $csrfToken = function_exists('generate_token') ? (string) generate_token() : '';

        if (lkn_hn_is_post_request() && isset($_POST['lkn_telegram_unlink'])) {
            if (function_exists('check_token') && check_token()) {
                $service->unlinkByClientId($clientId);
            }

            return '<script>window.location.href="clientarea.php?action=details";</script>';
        }

        $link  = $service->getLink($clientId);
        $config = null;

        if ($link) {
            $username = !empty($link->username) ? '@' . (string) $link->username : '';
            $chatId   = $this->maskChatId((string) $link->chat_id);

            $content = '<h3 style="margin-top:0;">Notificacoes via Telegram</h3>'
                . '<p>Conectado ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
                . ' (' . htmlspecialchars($chatId, ENT_QUOTES, 'UTF-8') . ')</p>'
                . '<form method="post" action="clientarea.php?action=details">'
                . '<input type="hidden" name="token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">'
                . '<input type="hidden" name="lkn_telegram_unlink" value="1">'
                . '<button type="submit" class="btn btn-default">Desconectar Telegram</button>'
                . '</form>';
        } else {
            $token = $service->generateToken($clientId);
            $url   = $token ? $service->buildDeepLink($token) : null;

            if (!$url) {
                return null;
            }

            $content = '<h3 style="margin-top:0;">Notificacoes via Telegram</h3>'
                . '<p>Vincule sua conta do Telegram para receber notificacoes por la tambem.</p>'
                . '<a id="lkn-telegram-link-btn" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" class="btn btn-primary">'
                . 'Vincular Telegram</a>'
                . '<div id="lkn-telegram-wait" style="display:none; margin-top:10px;">Aguardando confirmacao no Telegram...</div>'
                . '<div id="lkn-telegram-timeout" style="display:none; margin-top:10px;">'
                . '<a href="clientarea.php?action=details">Ainda nao confirmou? Atualizar status</a></div>';

            $config = json_encode(
                ['apiUrl' => $this->apiUrl(), 'token' => $csrfToken],
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
            );
        }

        $encoded = json_encode($content, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $configJs = $config ?? 'null';

        return '<script>(function(){'
            . 'var cfg=' . $configJs . ';'
            . 'function init(){'
            . 'if(document.getElementById("lkn-telegram-profile-block")){return;}'
            . 'var content=' . $encoded . ';'
            . 'var target=null;'
            . 'var emailInput=document.querySelector("input[name^=email_preferences]");'
            . 'if(emailInput){target=emailInput.closest(".card, .panel");}'
            . 'if(!target){var headings=document.querySelectorAll("h2,h3,h4");'
            . 'for(var i=0;i<headings.length;i++){var txt=(headings[i].textContent||"").toLowerCase();'
            . 'if(txt.indexOf("pref")!==-1&&(txt.indexOf("e-mail")!==-1||txt.indexOf("email")!==-1)){target=headings[i].closest(".card, .panel");break;}}}'
            . 'if(!target){var cards=document.querySelectorAll(".card, .panel");'
            . 'for(var j=0;j<cards.length;j++){var t=(cards[j].textContent||"").toLowerCase();'
            . 'if(t.indexOf("pref")!==-1&&(t.indexOf("e-mail")!==-1||t.indexOf("email")!==-1)){target=cards[j];break;}}}'
            . 'var isCard=target?target.classList.contains("card"):true;'
            . 'var wrapper=document.createElement("div");'
            . 'wrapper.id="lkn-telegram-profile-block";'
            . 'wrapper.className=isCard?"card":"panel panel-default";'
            . 'wrapper.style.marginBottom="20px";'
            . 'var body=document.createElement("div");'
            . 'body.className=isCard?"card-body":"panel-body";'
            . 'body.innerHTML=content;'
            . 'wrapper.appendChild(body);'
            . 'if(target&&target.parentNode){target.parentNode.insertBefore(wrapper,target);}'
            . 'else{var fallback=document.querySelector("#frmMyDetails")||document.querySelector(".client-details")||document.querySelector("#main-body");if(fallback){fallback.appendChild(wrapper);}}'
            . 'if(cfg&&cfg.apiUrl&&document.getElementById("lkn-telegram-link-btn")){'
            . 'var waitEl=document.getElementById("lkn-telegram-wait");'
            . 'var timeoutEl=document.getElementById("lkn-telegram-timeout");'
            . 'var timer=null,attempts=0,waiting=false;'
            . 'function check(){if(!waiting){return;}fetch(cfg.apiUrl+"?endpoint=telegram/status",{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"token="+encodeURIComponent(cfg.token)}).then(function(r){return r.json();}).then(function(d){if(d&&d.linked){waiting=false;if(timer){clearInterval(timer);}window.location.reload();}}).catch(function(){});}'
            . 'function start(){if(waiting){return;}waiting=true;attempts=0;if(waitEl){waitEl.style.display="block";}if(timeoutEl){timeoutEl.style.display="none";}if(timer){clearInterval(timer);}timer=setInterval(function(){attempts++;if(attempts>30){waiting=false;clearInterval(timer);if(waitEl){waitEl.style.display="none";}if(timeoutEl){timeoutEl.style.display="block";}return;}check();},3000);check();}'
            . 'document.getElementById("lkn-telegram-link-btn").addEventListener("click",start);'
            . 'document.addEventListener("visibilitychange",function(){if(!document.hidden){check();}});'
            . 'window.addEventListener("focus",check);'
            . '}'
            . '}'
            . 'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",init);}else{init();}'
            . '})();</script>';
    }

    public function handleClientDelete(array $vars): void
    {
        $clientId = (int) ($vars['userid'] ?? $vars['client_id'] ?? 0);

        if ($clientId > 0) {
            (new TelegramLinkService())->unlinkByClientId($clientId);
        }
    }

    private function isProfilePage(array $vars): bool
    {
        return ($vars['filename'] ?? '') === 'clientarea'
            && ($vars['action'] ?? '') === 'details';
    }

    private function clientId(array $vars): ?int
    {
        $clientId = $vars['clientsdetails']['userid'] ?? $vars['userid'] ?? null;

        return $clientId ? (int) $clientId : null;
    }

    private function maskChatId(string $chatId): string
    {
        $length = strlen($chatId);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($chatId, 0, 3) . str_repeat('*', max(2, $length - 5)) . substr($chatId, -2);
    }

    private function apiUrl(): string
    {
        $systemUrl = (string) Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')
            ->value('value');

        return rtrim($systemUrl, '/')
            . '/modules/addons/lknhooknotification/src/Core/api.php';
    }
}
