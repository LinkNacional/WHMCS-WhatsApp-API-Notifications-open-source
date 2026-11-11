{if $page_params.notification_send_result}
    <script type="text/javascript">
        {assign 'msg' $page_params.notification_send_result['msg']}
        window.alert("{$page_params.notification_send_result['code']} - {lkn_hn_lang text=$msg}")
    </script>
{/if}

<div
    class="container-fluid"
    style="max-width: fit-content; margin: 0 auto 0;"
>
    <div
        class="row"
        style="margin-top: 30px;"
    >
        <div class="col-sm-12">
            <form
                id="lkn-hn-{$page_params.hook->name}"
                class="form-inline"
                target="_self"
                method="POST"
            >
                <input
                    type="hidden"
                    name="token"
                    value="{$csrf_token}"
                >
                {foreach from=$page_params.whmcsHookParams item=$paramValue key=$paramName}
                    <input
                        type="hidden"
                        name="{$paramName}"
                        value="{$paramValue}"
                    >
                {/foreach}

                <div
                    class="form-group text-left"
                    style="vertical-align: top;"
                >
                    <div>
                        <select
                            class="form-control"
                            id="lkn-hn-manual-notif-code"
                            name="lkn-hn-manual-notif-code"
                        >
                            <option value="">{lkn_hn_lang text="Select a notification"}</option>
                            {foreach from=$page_params.notifications item=$notification}
                                <option value="{$notification->code}">{lkn_hn_lang text="{$notification->code}"}</option>
                            {/foreach}
                        </select>
                        <br>
                        <small style="margin-left: 13px;">
                            {lkn_hn_lang text="WhatsApp & Chawoot Module"}
                        </small>
                    </div>
                </div>

                <button
                    id="lkn-hn-manual-notification-trigger"
                    class="btn btn-success btn-sm"
                    type="submit"
                    disabled
                >
                    {lkn_hn_lang text="Send Notification"}
                </button>
            </form>
        </div>

        <div
            class="col-sm-12"
            style="max-height: 200px; overflow: auto; margin-top: 30px;"
        >
            <button
                class="btn btn-link btn-sm"
                type="button"
                data-toggle="collapse"
                data-target="#collapseExample"
                aria-expanded="false"
                aria-controls="collapseExample"
            >
                {lkn_hn_lang text="Show notification reports"}
            </button>
        </div>

        <div class="col-sm-12">
            <div
                class="collapse"
                id="collapseExample"
            >
                <div class="panel panel-default">
                    {if count($page_params.notification_reports) > 0}
                        <div
                            class="table-responsive"
                            style="max-height: 200px; overflow: auto;"
                        >
                            <table class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>{lkn_hn_lang text="Status"}</th>
                                        <th>{lkn_hn_lang text="Date"}</th>
                                        <th style="min-width: 200px;">{lkn_hn_lang text="Notification"}</th>
                                        <th style="min-width: 150px;">{lkn_hn_lang text="Platform"}</th>
                                        <th style="min-width: 300px;">{lkn_hn_lang text="Message"}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$page_params.notification_reports item=$report}
                                        <tr>
                                            {if is_null($report->status)}
                                                <td>-</td>
                                            {else}
                                                <td>
                                                    <span
                                                        class="label label-{if $report->status->value === 'error'}danger{elseif $report->status->value === 'not_sent'}warning{else}success{/if}"
                                                    >
                                                        {$report->status->label()}
                                                    </span>
                                                </td>
                                            {/if}

                                            <td>
                                                {$report->createdAt->format("d/m/y")}
                                            </td>
                                            <td class="text-left">
                                                {lkn_hn_lang text="{$report->notificationCode}"}
                                            </td>

                                            {if is_null($report->platform)}
                                                <td>-</td>
                                            {else}
                                                <td class="text-left">{$report->platform->label()}</td>
                                            {/if}

                                            <td>{$report->msg}</td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <p
                            class="text-muted"
                            style="margin-bottom: 0px; margin: 10px 0px;"
                        >
                            {lkn_hn_lang text="There is no reports for this item."}
                        </p>
                    {/if}
                    <div class="panel-footer">
                        <a
                            class="btn btn-link btn-sm"
                            href="addonmodules.php?module=lknhooknotification&page=notification-reports"
                            target="_blank"
                        >
                            {lkn_hn_lang text="See all reports"}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const manualNotificationSelect = document.getElementById('lkn-hn-manual-notif-code')
    const manualNotificationTriggerBtn = document.getElementById('lkn-hn-manual-notification-trigger')

    manualNotificationSelect.addEventListener('change', (e) => {
        const selectedNotificationCode = e.currentTarget.value

        if (selectedNotificationCode !== '') {
            manualNotificationTriggerBtn.disabled = false
        }
    })
</script>
