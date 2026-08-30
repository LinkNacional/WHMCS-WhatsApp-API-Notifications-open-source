<div
    class="alert alert-success alert-dismissible fade in"
    role="alert"
>
    <button
        id="lknhooknotification-dismiss-icon"
        type="button"
        class="close"
        data-dismiss="alert"
        aria-label="Close"
    >
        <span aria-hidden="true">×</span>
    </button>
    <h4 style="margin-bottom: 20px;">{lkn_hn_lang text="A new version is available for WhatsApp and Chatwoot"}</h4>

    {if $page_params.new_version_body}
        <div style="max-width: 750px; max-height: 260px; overflow-y: auto; margin-bottom: 20px; padding: 12px; border: 1px solid #e2e2e2; background: #fafafa;">
            {$page_params.new_version_body|escape:'html'|nl2br}
        </div>
    {/if}

    <div style="margin-top: 20px; display: flex; align-items: center; justify-content: space-between;">
        <p style="max-width: 750px; margin: 0;">
            <a
                class="btn btn-success"
                target="_blank"
                href="https://github.com/LinkNacional/WHMCS-WhatsApp-API-Notifications-open-source/releases/latest"
                role="button"
            ><i class="fas fa-cloud-download"></i> {lkn_hn_lang text="Download new version"}
                v{$page_params.new_version}</a>
        </p>

        <p style="margin: 0;">
            <a
                class="btn btn-link btn-sm"
                target="_blank"
                href="https://github.com/LinkNacional/WHMCS-WhatsApp-API-Notifications-open-source/releases/latest"
                role="button"
            >
                {lkn_hn_lang text="View changelog"}
            </a>
        </p>
    </div>
</div>

<script type="text/javascript">
    const dismissIcon = document.getElementById('lknhooknotification-dismiss-icon');

    dismissIcon.addEventListener('click', () => {
        const url = new URL(window.location.href);

        url.searchParams.set('new-version-dismiss-on-admin-home', '1');

        window.location.href = url.toString();
    });
</script>
