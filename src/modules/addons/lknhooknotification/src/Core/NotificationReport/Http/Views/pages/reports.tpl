{extends "{$lkn_hn_layout_path}/layout/layout.tpl"}

{block "page_title"}
    {lkn_hn_lang text="Notification Reports" params=[$page_params.platform_title]}
{/block}

{block "page_content"}
    <style>
        .report-link {
            padding: 0px;
        }

        .kpi-panel .panel-body {
            text-align: center;
            padding: 15px 10px;
        }

        .kpi-panel .kpi-value {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .kpi-panel .kpi-label {
            color: #777;
            margin-bottom: 0px;
        }

        .filter-form .form-group {
            margin-bottom: 10px;
        }

        .filter-form label {
            font-weight: normal;
        }

        .chart-panel .panel-body {
            padding: 15px;
        }

        .bar-row {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }

        .bar-row .bar-label {
            flex: 0 0 96px;
            font-size: 11px;
            color: #777;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 6px;
            text-align: right;
        }

        .bar-row .bar-track {
            flex: 1;
            background: #f1f1f1;
            border-radius: 3px;
            height: 16px;
            overflow: hidden;
        }

        .bar-row .bar-fill {
            height: 100%;
            background: #337ab7;
            border-radius: 3px;
            min-width: 1px;
        }

        .bar-row .bar-value {
            flex: 0 0 42px;
            font-size: 11px;
            color: #555;
            padding-left: 6px;
        }

        .bar-row.bar-error .bar-fill {
            background: #d9534f;
        }

        .chart-empty {
            color: #999;
            font-style: italic;
            text-align: center;
            padding: 20px 0;
        }
    </style>

    <div class="row">
        <div class="col-md-12">
            {* KPI cards *}
            <div class="row">
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="panel panel-default kpi-panel">
                        <div class="panel-body">
                            <p class="kpi-value">{$page_params.kpi.total}</p>
                            <p class="kpi-label">{lkn_hn_lang text="Total"}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="panel panel-default kpi-panel">
                        <div class="panel-body">
                            <p class="kpi-value">{$page_params.kpi.sent}</p>
                            <p class="kpi-label">{lkn_hn_lang text="Sent"}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="panel panel-default kpi-panel">
                        <div class="panel-body">
                            <p class="kpi-value">{$page_params.kpi.not_sent}</p>
                            <p class="kpi-label">{lkn_hn_lang text="Not sent"}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="panel panel-default kpi-panel">
                        <div class="panel-body">
                            <p class="kpi-value">{$page_params.kpi.error}</p>
                            <p class="kpi-label">{lkn_hn_lang text="Error"}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-6">
                    <div class="panel panel-default kpi-panel">
                        <div class="panel-body">
                            <p class="kpi-value">{$page_params.kpi.success_rate}%</p>
                            <p class="kpi-label">{lkn_hn_lang text="Success rate"}</p>
                        </div>
                    </div>
                </div>
            </div>

            {* Charts (server-side, CSS bars) *}
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default chart-panel">
                        <div class="panel-heading">{lkn_hn_lang text="Messages per day"}</div>
                        <div class="panel-body">
                            {if $page_params.charts.by_day}
                                {foreach from=$page_params.charts.by_day item=$bar}
                                    <div class="bar-row">
                                        <span class="bar-label" title="{$bar.title}">{$bar.label}</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {$bar.pct}%"></div>
                                        </div>
                                        <span class="bar-value">{$bar.total}</span>
                                    </div>
                                {/foreach}
                            {else}
                                <div class="chart-empty">{lkn_hn_lang text="No data for this period"}</div>
                            {/if}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="panel panel-default chart-panel">
                        <div class="panel-heading">{lkn_hn_lang text="Messages per hour"}</div>
                        <div class="panel-body">
                            {if $page_params.charts.by_hour}
                                {foreach from=$page_params.charts.by_hour item=$bar}
                                    <div class="bar-row">
                                        <span class="bar-label" title="{$bar.title}">{$bar.label}h</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {$bar.pct}%"></div>
                                        </div>
                                        <span class="bar-value">{$bar.total}</span>
                                    </div>
                                {/foreach}
                            {else}
                                <div class="chart-empty">{lkn_hn_lang text="No data for this period"}</div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default chart-panel">
                        <div class="panel-heading">{lkn_hn_lang text="Top notifications"}</div>
                        <div class="panel-body">
                            {if $page_params.charts.top_notifications}
                                {foreach from=$page_params.charts.top_notifications item=$bar}
                                    <div class="bar-row">
                                        <span class="bar-label" title="{$bar.notification}">{lkn_hn_lang text="{$bar.notification}"}</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {$bar.pct}%"></div>
                                        </div>
                                        <span class="bar-value">{$bar.total}</span>
                                    </div>
                                {/foreach}
                            {else}
                                <div class="chart-empty">{lkn_hn_lang text="No data for this period"}</div>
                            {/if}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="panel panel-default chart-panel">
                        <div class="panel-heading">{lkn_hn_lang text="Top error messages"}</div>
                        <div class="panel-body">
                            {if $page_params.charts.top_errors}
                                {foreach from=$page_params.charts.top_errors item=$bar}
                                    <div class="bar-row bar-error">
                                        <span class="bar-label" title="{$bar.msg|escape}">{$bar.msg|truncate:24|escape}</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {$bar.pct}%"></div>
                                        </div>
                                        <span class="bar-value">{$bar.total}</span>
                                    </div>
                                {/foreach}
                            {else}
                                <div class="chart-empty">{lkn_hn_lang text="No data for this period"}</div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            {* Filters *}
            <div class="panel panel-default">
                <div class="panel-body">
                    <form
                        method="get"
                        action=""
                        class="filter-form"
                    >
                        <input type="hidden" name="module" value="lknhooknotification">
                        <input type="hidden" name="page" value="notification-reports">

                        <div class="row">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-period">{lkn_hn_lang text="Period"}</label>
                                <select
                                    id="filter-period"
                                    name="period"
                                    class="form-control"
                                    onchange="this.form.submit()"
                                >
                                    {foreach from=$page_params.period_options key=$key item=$label}
                                        <option
                                            value="{$key}"
                                            {if $page_params.period === $key}selected{/if}
                                        >{$label}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-status">{lkn_hn_lang text="Status"}</label>
                                <select id="filter-status" name="status" class="form-control">
                                    {foreach from=$page_params.status_options key=$key item=$label}
                                        <option
                                            value="{$key}"
                                            {if $page_params.filters.status === $key}selected{/if}
                                        >{$label}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-platform">{lkn_hn_lang text="Platform"}</label>
                                <select id="filter-platform" name="platform" class="form-control">
                                    {foreach from=$page_params.platform_options key=$key item=$label}
                                        <option
                                            value="{$key}"
                                            {if $page_params.filters.platform === $key}selected{/if}
                                        >{$label}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-category">{lkn_hn_lang text="Category"}</label>
                                <select id="filter-category" name="category" class="form-control">
                                    {foreach from=$page_params.category_options key=$key item=$label}
                                        <option
                                            value="{$key}"
                                            {if $page_params.filters.category === $key}selected{/if}
                                        >{$label}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-notification">{lkn_hn_lang text="Notification"}</label>
                                <select id="filter-notification" name="notification" class="form-control">
                                    <option value="">{lkn_hn_lang text="All notifications"}</option>
                                    {foreach from=$page_params.notification_options item=$notification_code}
                                        <option
                                            value="{$notification_code}"
                                            {if $page_params.filters.notification === $notification_code}selected{/if}
                                        >{lkn_hn_lang text="{$notification_code}"}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-client-id">{lkn_hn_lang text="Client ID"}</label>
                                <input
                                    id="filter-client-id"
                                    type="number"
                                    name="client_id"
                                    class="form-control"
                                    value="{$page_params.filters.client_id}"
                                >
                            </div>

                            <div class="col-md-2 col-sm-6 form-group">
                                <label for="filter-date-from">{lkn_hn_lang text="Start date"}</label>
                                <input
                                    id="filter-date-from"
                                    type="date"
                                    name="date_from"
                                    class="form-control"
                                    value="{$page_params.filters.date_from}"
                                >
                            </div>

                            <div class="col-md-2 col-sm-6 form-group">
                                <label for="filter-date-to">{lkn_hn_lang text="End date"}</label>
                                <input
                                    id="filter-date-to"
                                    type="date"
                                    name="date_to"
                                    class="form-control"
                                    value="{$page_params.filters.date_to}"
                                >
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-q">{lkn_hn_lang text="Search message or target"}</label>
                                <input
                                    id="filter-q"
                                    type="text"
                                    name="q"
                                    class="form-control"
                                    value="{$page_params.filters.q}"
                                >
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        {lkn_hn_lang text="Apply filters"}
                                    </button>
                                    <a
                                        href="?module=lknhooknotification&page=notification-reports"
                                        class="btn btn-default btn-sm"
                                    >
                                        {lkn_hn_lang text="Clear filters"}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="table-responsive">
                    <table class="table table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{lkn_hn_lang text="Status"}</th>
                                <th>{lkn_hn_lang text="Message"}</th>
                                <th>{lkn_hn_lang text="Date"}</th>
                                <th>{lkn_hn_lang text="Platform"}</th>
                                <th>{lkn_hn_lang text="Notification"}</th>
                                <th>{lkn_hn_lang text="Client"}</th>
                                <th>{lkn_hn_lang text="Category"}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$page_params.reports item=$report}
                                <tr>
                                    <th scope="row">{$report->id}</th>

                                    <td>
                                        <span
                                            class="label label-{if $report->status->value === 'error'}danger{elseif $report->status->value === 'not_sent'}warning{else}success{/if}"
                                        >
                                            {$report->status->label()}
                                        </span>
                                    </td>
                                    <td style="max-width: 200px;">
                                        {if !empty($report->msg)}
                                            <p
                                                {if strlen($report->msg) > 30}
                                                    data-toggle="popover"
                                                    data-animation="false"
                                                    data-placement="right"
                                                    data-html="true"
                                                    {if $report->platform->value === 'wp' && $report->status->value === 'error'}
                                                        data-content="
                                                        {htmlspecialchars($report->msg)}
                                                        <br>
                                                        <a href='https://developers.facebook.com/docs/whatsapp/cloud-api/support/error-codes/#error-codes' target='_blank'>WhatsApp Cloud API Error Codes <i class='fas fa-external-link-alt'></i></a>"
                                                    {else}
                                                        data-content="{htmlspecialchars($report->msg)}"
                                                    {/if}
                                                    data-trigger="click hover"
                                                {/if}
                                                class="text-muted"
                                                style="margin-bottom: 0px !important; width: fit-content; cursor: pointer;"
                                            >
                                                {if strlen($report->msg) > 30}
                                                    <i class="fas fa-question-circle"></i>
                                                    {substr($report->msg, 0, 30)}...
                                                {else}
                                                    {lkn_hn_lang text="{$report->msg}"}
                                                {/if}
                                            </p>
                                        {/if}
                                    </td>
                                    <td>{$report->createdAt->format('Y-m-d H:i:s')}</td>
                                    <td>
                                        {if $report->platform}
                                            <a
                                                class="btn btn-link report-link"
                                                href="?module=lknhooknotification&page=platforms/{$report->platform->value}/settings"
                                            >
                                                {$report->platform->label()}
                                            </a>
                                        {/if}
                                    </td>
                                    <td>
                                        {if !$report->platform || $report->platform->value === 'cw'}
                                            {lkn_hn_lang text="{$report->notificationCode}"}
                                        {else}
                                            <a
                                                class="btn btn-link report-link"
                                                href="?module=lknhooknotification&page=notifications/{$report->notificationCode}/templates/first"
                                            >
                                                {lkn_hn_lang text="{$report->notificationCode}"}
                                            </a>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $report->clientId}
                                            <a
                                                target="_blank"
                                                href="clientssummary.php?userid={$report->clientId}"
                                            >
                                                #{$report->clientId}
                                                {if $report->target}
                                                    at +{$report->target}
                                                {/if}
                                            </a>
                                        {/if}
                                    </td>
                                    <td>
                                        {if !empty($report->category) && !empty($report->categoryId)}
                                            {if $report->category->value === 'invoice'}
                                                {assign "category_link" "invoices.php?action=edit&id={$report->categoryId}"}
                                            {elseif $report->category->value === 'order'}
                                                {assign "category_link" "orders.php?action=view&id={$report->categoryId}"}
                                            {elseif $report->category->value === 'ticket'}
                                                {assign "category_link" "supporttickets.php?action=view&id={$report->categoryId}"}
                                            {elseif $report->category->value === 'domain'}
                                                {assign "category_link" "clientsdomains.php?userid={$report->clientId}&id={$report->categoryId}"}
                                            {/if}

                                            <a
                                                target="_blank"
                                                href="{$category_link}"
                                            >
                                                {$report->category->label()} #{$report->categoryId}
                                            </a>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {assign "total_pages" value=ceil($page_params.total_reports / $page_params.reports_per_page)}
                {assign "page_link_tpl" value="?module=lknhooknotification&page=notification-reports{$page_params.filters_query_string}&pageN"}

                {if $total_pages > 1}
                    <nav
                        aria-label="Page navigation"
                        style="text-align: center;"
                    >
                        <ul class="pagination">
                            {if $page_params.current_page > 1}
                                <li>
                                    <a href="{$page_link_tpl}=1">
                                        {lkn_hn_lang text="First Page"}
                                    </a>
                                </li>
                            {/if}
                            <li
                                {if $page_params.current_page == 1}
                                    class="disabled"
                                {/if}
                            >
                                <a
                                    {if $page_params.current_page > 1}
                                        href="{$page_link_tpl}={$page_params.current_page - 1}"
                                    {/if}
                                    aria-label="Previous"
                                >
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>

                            {if $total_pages >= 15}


                                {for $page=$page_params.current_page - 8 to $page_params.current_page}
                                    {if $page > 0}
                                        <li
                                            {if $page == $page_params.current_page}
                                                class="active"
                                            {/if}
                                        >
                                            <a href="{$page_link_tpl}={$page}">{$page}</a>
                                        </li>
                                    {/if}
                                {/for}

                                {for $page=$page_params.current_page + 1 to $page_params.current_page + 8}
                                    {if $page < $total_pages}
                                        <li
                                            {if $page == $page_params.current_page}
                                                class="active"
                                            {/if}
                                        >
                                            <a href="{$page_link_tpl}={$page}">{$page}</a>
                                        </li>
                                    {/if}
                                {/for}


                            {else}
                                {for $page=1 to $total_pages}
                                    <li
                                        {if $page == $page_params.current_page}
                                            class="active"
                                        {/if}
                                    ><a href="{$page_link_tpl}={$page}">{$page}</a></li>
                                {/for}
                            {/if}

                            <li
                                {if $page_params.current_page >= $total_pages}
                                    class="disabled"
                                {/if}
                            >
                                <a
                                    {if $page_params.current_page < $total_pages}
                                        href="{$page_link_tpl}={$page_params.current_page + 1}"
                                    {/if}
                                    aria-label="Next"
                                >
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>

                            {if $page_params.current_page <= $total_pages - 1}
                                <li>
                                    <a href="{$page_link_tpl}={$total_pages}">
                                        {lkn_hn_lang text="Last Page"}
                                    </a>
                                </li>
                            {/if}
                        </ul>
                    </nav>
                {/if}
            </div>
        </div>
    </div>
{/block}
