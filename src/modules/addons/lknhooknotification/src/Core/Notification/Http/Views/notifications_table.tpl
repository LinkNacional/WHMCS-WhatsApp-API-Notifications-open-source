{extends "{$lkn_hn_layout_path}/layout/layout.tpl"}

{block "page_title"}
    {lkn_hn_lang text="Notifications"}
{/block}

{block "title_right_side"}
    <a
        class="btn btn-link"
        href="https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/wiki/How-to-create-a-notification-by-yourself"
        target="_blank"
    >
        <i class="far fa-question-circle"></i>
        {lkn_hn_lang text="How to create your own notification?"}
    </a>
{/block}

{block "page_content"}
    {if $page_params.must_block_add_other_notifications}
        <div
            class="alert alert-warning"
            role="alert"
            style="width: 100%; display: inline-flex; justify-content: space-between; align-items: center;"
        >
            <p>
                {lkn_hn_lang text="You are on free plan and limited to 3 notifications."}
                {if $page_params.must_block_edit_notification}
                    <br>
                    {lkn_hn_lang text="You have to keep only three notifications configured to be able to use the module."}
                {/if}
            </p>
        </div>
    {/if}

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="table-responsive">
                    <table class="table table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{lkn_hn_lang text="Status"}</th>
                                <th>{lkn_hn_lang text="Notification"}</th>
                                {* <th>{lkn_hn_lang text="Description"}</th> *}
                                <th>{lkn_hn_lang text="Templates"}</th>
                                {* <th>{lkn_hn_lang text="Actions"}</th> *}
                            </tr>
                        </thead>

                        <tbody>
                            {foreach from=$page_params.notifications item=$notification key=$key}
                                <tr style="position: relative;">
                                    <th scope="row">
                                        <p style="line-height: 30px;">
                                            {$key + 1}
                                        </p>
                                    </th>
                                    <td>
                                        <p style="line-height: 30px;">
                                            {if isset($notification->templates) && count($notification->templates) > 0}
                                                <span class="label label-success">{lkn_hn_lang text="Enabled"}</span>
                                            {else}
                                                <span class="label label-default">{lkn_hn_lang text="Disabled"}</span>
                                            {/if}
                                        </p>
                                    </td>
                                    <td>
                                        <div
                                            style="display: flex; flex-direction: column; align-content: center; flex-wrap: wrap; max-width: fit-content;"
                                            {if $notification->description}
                                                data-toggle="popover"
                                                title="{lkn_hn_lang text=$notification->code}"
                                                data-content="{lkn_hn_lang text=$notification->description}"
                                                data-trigger="hover"
                                            {/if}
                                        >
                                            <p>
                                                {lkn_hn_lang text=$notification->code}
                                                {if $notification->description}<i class="far fa-question-circle"></i>{/if}
                                            </p>

                                            {if $notification->hook->value}
                                                <a
                                                    href="https://developers.whmcs.com/hooks/hook-index/#:~:text={$notification->hook->value}"
                                                    target="_blank"
                                                    style="font-size: 0.9rem; color: gray;"
                                                >{$notification->hook->value}</a>
                                            {/if}
                                        </div>
                                    </td>
                                    {* <td></td> *}
                                    <td>
                                        {if !$page_params.must_block_add_other_notifications}
                                            <p
                                                class="text-muted"
                                                style="margin-bottom: 0px;"
                                            >

                                                <a
                                                    type="button"
                                                    class="btn btn-link btn-sm"
                                                    href="{$lkn_hn_base_endpoint}&page=notifications/{$notification->code}/templates/new"
                                                >
                                                    <i class="fas fa-plus"></i>
                                                    {lkn_hn_lang text="Setup template"}
                                                </a>
                                            </p>
                                        {/if}
                                        {if isset($notification->templates) && (count($notification->templates) > 0)}
                                            <div
                                                class="panel panel-default"
                                                style="margin-bottom: 0px !important;"
                                            >
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-condensed">
                                                        <tbody>
                                                            {foreach from=$notification->templates item=$template}
                                                                <tr>
                                                                    <td style="width: 80px;">
                                                                        <div style="display: flex; align-items: center; gap: 4px;">
                                                                            {if !in_array($template->platform, $page_params.platform_list)}
                                                                                <div
                                                                                    data-toggle="tooltip"
                                                                                    data-placement="left"
                                                                                    title="{lkn_hn_lang text="This template will not be sent because its is disabled."}"
                                                                                    style="background-color: red; width: 14px; height: 14px; border-radius: 100%;"
                                                                                >
                                                                                </div>
                                                                            {/if}

                                                                            <p
                                                                                class="text-muted"
                                                                                style="margin-bottom: 0px !important;"
                                                                            >
                                                                                {$template->lang}
                                                                            </p>
                                                                        </div>
                                                                    </td>
                                                                    <td style="width: 160px;">
                                                                        <p
                                                                            class="text-muted"
                                                                            style="margin-bottom: 0px !important;"
                                                                        >
                                                                            {$template->platform->label()}
                                                                        </p>
                                                                    </td>
                                                                    <td>
                                                                        <p
                                                                            {if $template->platform->value !== 'wp' && strlen($template->template) > 60}
                                                                                data-toggle="tooltip"
                                                                                data-placement="left"
                                                                                title="{$template->template}"
                                                                            {/if}
                                                                            class="text-muted"
                                                                            style="margin-bottom: 0px !important;"
                                                                        >
                                                                            {if strlen($template->template) > 60}
                                                                                {substr($template->template, 0, 60)}...
                                                                            {else}
                                                                                {$template->template}
                                                                            {/if}
                                                                        </p>
                                                                    </td>
                                                                    <td
                                                                        class="text-right"
                                                                        style="width: 140px;"
                                                                    >
                                                                        {* platforms/{platform}/notifications/{notif_code}/templates/{tpl_lang} *}

                                                                        {if !$page_params.must_block_edit_notification}
                                                                            <a
                                                                                class="btn btn-primary btn-xs"
                                                                                href="{$lkn_hn_base_endpoint}&page=notifications/{$notification->code}/templates/{$template->lang}"
                                                                            >
                                                                                {lkn_hn_lang text="Edit"}
                                                                            </a>
                                                                        {/if}

                                                                        <form
                                                                            id="delete-notif-form-{$notification->code}-{$template->lang}"
                                                                            style="display: none;"
                                                                            target="_self"
                                                                            method="POST"
                                                                        >
                                                                            <input
                                                                                type="hidden"
                                                                                name="delete-template"
                                                                            >

                                                                            <input
                                                                                type="hidden"
                                                                                name="notification-code"
                                                                                value="{$notification->code}"
                                                                            >

                                                                            <input
                                                                                type="hidden"
                                                                                name="template-locale"
                                                                                value="{$template->lang}"
                                                                            >
                                                                        </form>

                                                                        <button
                                                                            type="submit"
                                                                            form="delete-notif-form-{$notification->code}-{$template->lang}"
                                                                            class="btn btn-danger btn-xs"
                                                                            href="{$lkn_hn_base_endpoint}&page=notifications/{$notification->code}/templates/{$template->lang}"
                                                                            onclick="return window.confirm('{lkn_hn_lang text='Are you sure you want to delete this template?'}')"
                                                                        >
                                                                            {lkn_hn_lang text="Delete"}
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            {/foreach}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        {/if}


                                    </td>
                                    {* <td></td> *}
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
{/block}
