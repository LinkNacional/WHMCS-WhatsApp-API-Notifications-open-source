{extends "layout/layout.tpl"}

{assign var="is_homepage" value=true}

{block "page_content"}
    {$page_params.new_version_alert}
    <div class="row">
        {if !$page_params.dismiss_v400_alert}
            {include "{$lkn_hn_layout_path}/components/v400_alert.tpl"}
        {/if}

        <div class="col-md-12">
            <div class="panel panel-default">
                <div
                    class="panel-body"
                    style="display: flex; justify-content: space-between;"
                >
                    <div style="width: 100%; display: inline-flex; gap: 10px; align-content: center; align-items: center;">
                        <p class="lead">
                            <strong>{lkn_hn_lang text="Plan"}</strong>
                        </p>

                        <span
                            class="label {($page_params.license_status === 'yes') ? 'label-success' : 'label-default'}"
                            style="padding: 6px 10px;"
                        >
                            {if $page_params.license_status === 'no-license-found'}
                                {lkn_hn_lang text="Free"}
                            {elseif $page_params.license_status === 'no'}
                                {lkn_hn_lang text="Free"}
                            {elseif $page_params.license_status === 'yes'}
                                {lkn_hn_lang text="Pro"}
                            {else}
                                {lkn_hn_lang text="Error"}
                            {/if}
                        </span>

                        {if $page_params.license_status === 'unable-to-check-license'}
                            <p>{lkn_hn_lang text="There was an error checking your license."}</p>

                        {elseif $page_params.license_status === 'no'}
                            <p>
                                {lkn_hn_lang text="You are limited to 3 notifications per platform."}
                            </p>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        <div
            class="col-md-3"
            style="min-width: 320px; width: 320px;"
        >
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {lkn_hn_lang text="Last hour statistics"}
                    </h3>
                </div>
                <div class="panel-body">
                    <ul
                        class="list-group"
                        style="margin-bottom: 0px;"
                    >
                        <li class="list-group-item">
                            <span class="badge">{$page_params.last_our['notifications_sent']}</span>
                            {lkn_hn_lang text="Messages sent"}
                        </li>
                        <li class="list-group-item">
                            <span class="badge">{$page_params.last_our['failed_sendings']}</span>
                            {lkn_hn_lang text="Failed messages"}
                        </li>
                        {if !empty($page_params.last_our['top_notifications'])}
                            <li class="list-group-item">
                                <h3 style="margin-bottom: 0px;">
                                    <strong>{lkn_hn_lang text="Top notifications"}</strong>
                                </h3>
                            </li>

                            <li class="list-group-item">
                                <ol style="padding: 0px; padding-left: 15px;">
                                    {foreach from=$page_params.last_our['top_notifications'] item=$notification}
                                        <li>
                                            {lkn_hn_lang text="{$notification->notification}"}
                                            <span class="badge">{$notification->total}</span>
                                        </li>
                                    {/foreach}
                                </ol>
                            </li>
                        {/if}
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-sm-6">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h2 style="margin-left: 15px; display: flex; justify-content: space-between;">
                                {lkn_hn_lang text="Contribute and request for new features!"}
                                <i class="far fa-external-link"></i>
                            </h2>
                            <hr>
                            <ul class="nav nav-pills nav-stacked">
                                <li role="presentation">
                                    <a
                                        href="https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/issues/new?assignees=&labels=bug%2C+help+wanted&projects=&template=bug_report.md&title=%5BBUG%5D"
                                        target="_blank"
                                    >
                                        <i class="fas fa-exclamation-triangle"></i> {lkn_hn_lang text="Report error"}
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a
                                        href="https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/issues/new?assignees=&labels=enhancement&projects=&template=feature_request.md&title=%5BFEATURE%5D"
                                        target="_blank"
                                    >
                                        <i class="fas fa-plus-circle"></i> {lkn_hn_lang text="Request new feature"}
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a
                                        href="https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/wiki/How-to-create-a-notification-by-yourself"
                                        target="_blank"
                                    >
                                        <i class="far fa-code"></i>
                                        {lkn_hn_lang text="How to create your own notification?"}
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a
                                        href="https://linknacional.github.io/whmcs-whatsapp-api-notifications-open-source/indices/files.html"
                                        target="_blank"
                                    >
                                        <i class="fas fa-file-alt"></i> {lkn_hn_lang text="Technical Documentation"}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h2 style="margin-left: 15px; display: flex; justify-content: space-between;">
                                {lkn_hn_lang text="Documentation"}
                                <i class="far fa-external-link"></i>
                            </h2>
                            <hr>
                            <ul class="nav nav-pills nav-stacked">
                                <li role="presentation">
                                    <a
                                        href="https://github.com/LinkNacional/WHMCS-WhatsApp-API-Notifications-open-source/wiki"
                                        target="_blank"
                                    >
                                        <i class="fas fa-cog"></i> {lkn_hn_lang text="How to setup the module?"}
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a
                                        href="https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/wiki/How-to-install-more-notifications"
                                        target="_blank"
                                    >
                                        <i class="fas fa-cloud-download"></i>
                                        {lkn_hn_lang text="How to install new notifications?"}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}