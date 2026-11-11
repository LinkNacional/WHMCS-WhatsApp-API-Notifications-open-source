<div class="telegram-actions" style="margin-top: 20px;">
    <h4>{lkn_hn_lang text="Telegram bot"}</h4>
    <p class="help-block">
        {lkn_hn_lang text="Webhook URL"}:<br>
        <code>{$page_params.webhook_url}</code>
    </p>
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
</div>
