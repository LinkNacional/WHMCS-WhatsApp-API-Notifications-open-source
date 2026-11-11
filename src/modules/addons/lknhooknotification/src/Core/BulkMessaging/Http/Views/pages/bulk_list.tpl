{extends "{$lkn_hn_layout_path}/layout/layout.tpl"}

{block "page_title"}
    {lkn_hn_lang text="Bulk Messages" params=[$page_params.platform_title]}
{/block}

{block "title_right_side"}
    <a
        class="btn btn-link"
        href="?module=lknhooknotification&amp;page=bulk/new"
    >
        <i class="far fa-plus"></i>
        {lkn_hn_lang text="New bulk message"}
    </a>
{/block}

{block "page_content"}
    <div class="row">
        <div class="col-md-12">
            {if count($page_params.bulks) === 0}
                <div
                    class="alert alert-info"
                    role="alert"
                >
                    {lkn_hn_lang text="No bulk messages."}
                </div>
                <a
                    class="btn btn-link text-center"
                    href="?module=lknhooknotification&amp;page=bulk/new"
                >
                    <i class="far fa-plus"></i>
                    {lkn_hn_lang text="New bulk message"}
                </a>
            {else}
                <div class="panel panel-default">
                    <div class="table-responsive">
                        <table class="table table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{lkn_hn_lang text="Title"}</th>
                                    <th>{lkn_hn_lang text="Status"}</th>
                                    <th>{lkn_hn_lang text="Description"}</th>
                                    <th>{lkn_hn_lang text="Progress"}</th>
                                    <th>{lkn_hn_lang text="Platform"}</th>
                                    <th>{lkn_hn_lang text="Start date"}</th>
                                    <th>{lkn_hn_lang text="Completed at"}</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                {foreach from=$page_params.bulks item=$bulk key=$key}
                                    <tr style="position: relative;">
                                        <th scope="row">
                                            {$bulk->id}
                                        </th>
                                        <td>
                                            {$bulk->title}
                                        </td>
                                        <td>
                                            {if "now"|date_format:"%Y-%m-%d %H:%M:%S" >= $bulk->startAt->format('Y-m-d H:i:s')}

                                                <span class="label {$bulk->status->labelClass()}">
                                                    {$bulk->status->label()}
                                                </span>
                                            {else}
                                                <span class="label label-default">
                                                    {lkn_hn_lang text="Awaiting"}
                                                </span>
                                            {/if}
                                        </td>
                                        <td>
                                            {$bulk->description}
                                        </td>
                                        <td>
                                            <div
                                                class="progress"
                                                style="margin-bottom: 0px;"
                                            >
                                                <div
                                                    class="progress-bar"
                                                    role="progressbar"
                                                    aria-valuenow="{$bulk->progress}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                    style="width: {$bulk->progress}%;"
                                                >
                                                    {$bulk->progress}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {$bulk->platform->label()}
                                        </td>
                                        <td>
                                            {$bulk->startAt->format('M d Y - H:i')}
                                        </td>
                                        <td>
                                            {if $bulk->completedAt}
                                                {$bulk->completedAt->format('M d Y - H:i')}
                                            {else}
                                                -
                                            {/if}
                                        </td>
                                        <td>
                                            <a
                                                class="btn btn-link btn-sm"
                                                href="?module=lknhooknotification&page=bulks/{$bulk->id}"
                                                style="padding: 0px; line-height: 0px;"
                                            >
                                                {lkn_hn_lang text="View bulk"}
                                            </a>
                                            {if "now"|date_format:"%Y-%m-%d %H:%M:%S" < $bulk->startAt->format('Y-m-d H:i:s')}
                                                &#8226;
                                                <form method="POST" style="display: inline;">
                                                    <input
                                                        type="hidden"
                                                        name="lkn_hn_token"
                                                        value="{$csrf_token}"
                                                    >
                                                    <input
                                                        type="hidden"
                                                        name="send-now"
                                                        value="1"
                                                    >
                                                    <input
                                                        type="hidden"
                                                        name="bulk-id"
                                                        value="{$bulk->id}"
                                                    >
                                                    <button
                                                        type="submit"
                                                        class="btn btn-link btn-sm"
                                                        style="padding: 0px; line-height: 0px;"
                                                    >
                                                        {lkn_hn_lang text="Send now"}
                                                    </button>
                                                </form>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/block}
