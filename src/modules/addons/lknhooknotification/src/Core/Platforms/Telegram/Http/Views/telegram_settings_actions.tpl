<div class="telegram-actions" style="margin-top: 20px;">
    <h4>{lkn_hn_lang text="Telegram bot"}</h4>

    <p class="help-block">
        {lkn_hn_lang text="Webhook URL"}:
        <code id="lkn-tg-webhook" style="word-break: break-all;">{$page_params.webhook_url}</code>
        <button
            type="button"
            class="btn btn-link btn-xs"
            onclick="navigator.clipboard.writeText(document.getElementById('lkn-tg-webhook').textContent);this.textContent='{lkn_hn_lang text="Copied"}';"
        >
            {lkn_hn_lang text="Copy"}
        </button>
    </p>

    <div class="alert {if $page_params.status.token && $page_params.status.username && $page_params.status.secret}alert-success{else}alert-warning{/if}">
        <strong>{lkn_hn_lang text="Setup status"}</strong>
        <ul style="margin-bottom: 0;">
            <li>{if $page_params.status.token}&#10003;{else}&#10007;{/if} {lkn_hn_lang text="Bot Token configured"}</li>
            <li>{if $page_params.status.username}&#10003;{else}&#10007;{/if} {lkn_hn_lang text="Bot Username configured"}</li>
            <li>{if $page_params.status.secret}&#10003;{else}&#10007;{/if} {lkn_hn_lang text="Webhook Secret configured"}</li>
            <li>{if $page_params.status.enabled}&#10003;{else}&#10007;{/if} {lkn_hn_lang text="Telegram enabled"}</li>
        </ul>
        {if !$page_params.status.token || !$page_params.status.username || !$page_params.status.secret}
            <p style="margin-top: 10px; margin-bottom: 0;">
                {lkn_hn_lang text="Complete the missing items below before enabling Telegram."}
            </p>
        {/if}
    </div>

    <button type="submit" name="telegram-action" value="test-bot" class="btn btn-default">
        {lkn_hn_lang text="Test bot"}
    </button>
    <button type="submit" name="telegram-action" value="set-webhook" class="btn btn-primary">
        {lkn_hn_lang text="Set webhook"}
    </button>
    <button type="submit" name="telegram-action" value="webhook-info" class="btn btn-default">
        {lkn_hn_lang text="Webhook status"}
    </button>
    <button type="submit" name="telegram-action" value="delete-webhook" class="btn btn-danger">
        {lkn_hn_lang text="Delete webhook"}
    </button>

    <p style="margin-top: 15px;">
        <a href="?module=lknhooknotification&page=telegram-links" class="btn btn-link">
            {lkn_hn_lang text="Manage linked clients"}
        </a>
    </p>

    <hr>

    <h4>{lkn_hn_lang text="How to configure Telegram"}</h4>

    <ol style="line-height: 1.8;">
        <li>
            {lkn_hn_lang text="Open"} <a href="https://t.me/BotFather" target="_blank" rel="noopener noreferrer">@BotFather</a>
            {lkn_hn_lang text="and send /newbot. Choose a name and a username ending in bot; copy the token it returns."}
        </li>
        <li>
            {lkn_hn_lang text="Paste the token into Bot Token and the username (without the @) into Bot Username."}
        </li>
        <li>
            {lkn_hn_lang text="Click Save Settings. The webhook secret is generated automatically."}
        </li>
        <li>
            {lkn_hn_lang text="Click Set webhook, then Webhook status. The status should show the URL and no error."}
        </li>
        <li>
            {lkn_hn_lang text="Check Enable Telegram and click Save Settings."}
        </li>
        <li>
            {lkn_hn_lang text="In the client area, open My Details, click Vincular Telegram and press Start in Telegram."}
        </li>
        <li>
            {lkn_hn_lang text="Configure a Telegram template for the notification you want and test the delivery."}
        </li>
    </ol>

    <p class="help-block">
        {lkn_hn_lang text="Proxy is optional: keep Proxy Type as None unless this server needs an outbound proxy. If your browser autofills the token or username fields, clear them before saving; they are secrets."}
    </p>
</div>
