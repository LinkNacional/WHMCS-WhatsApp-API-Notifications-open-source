{extends "layout/layout.tpl"}

{block "page_title"}
    {lkn_hn_lang text="Logs"}
{/block}

{block "title_right_side_float"}
    <form
        target="_blank"
        method="POST"
        action="addonmodules.php?module=lknhooknotification&page=logs"
    >
        <input
            type="hidden"
            name="lkn_hn_token"
            value="{$csrf_token}"
        >
        <input
            type="hidden"
            name="download-last-100-logs"
        >
        <button
            type="submit"
            class="btn btn-primary"
            style="float: right;"
        >
            Download last 100 logs
        </button>
    </form>
{/block}

{block "page_content"}
    <style>
        #table-logs textarea {
            width: 100%;
            height: 100px;
            border: 1px solid lightgray;
            border-radius: 5px;
            resize: none;
        }

        #table-logs form .form-group input {
            width: 100%;
        }

        #table-logs .popover-content {
            max-width: 300px;
            width: 300px;
            overflow: auto;
        }

        #table-logs .popover-content pre {
            width: 100%;
        }

        #table-logs textarea {
            outline: none;
            cursor: pointer;
        }

        .textarea-cont {
            width: 360px;
            position: relative;
        }

        .textarea-cont button {
            position: absolute;
            top: 0px;
            right: 15px;
        }
    </style>

    <div
        class="row"
        id="table-logs"
    >
        <div class="col-md-12">
            <form class="form-inline">
                <div class="form-group">
                    <label>Content filter</label>
                    <input
                        type="text"
                        class="form-control"
                        id="filter-content"
                        required
                        {if $page_params.filter}
                            value="{$page_params.filter}"
                        {/if}
                    >
                </div>
                <div class="form-group">
                    <label>
                        &nbsp;
                    </label>
                    <button
                        id="btn-submit-filter-content"
                        class="btn btn-primary btn-sm btn-block"
                        type="submit"
                    >
                        {lkn_hn_lang text="Filter"}
                    </button>
                </div>

                {if $page_params.filter}
                    <div class="form-group">
                        <label>
                            &nbsp;
                        </label>

                        <button
                            id="btn-clear-filter-content"
                            type="button"
                            class="btn btn-primary btn-sm btn-block btn-link"
                            data-toggle="tooltip"
                            data-placement="right"
                            title="{lkn_hn_lang text="Click to clear filter"}"
                        >
                            <i class="far fa-times"></i>
                        </button>
                    </div>
                {/if}
            </form>
            <script type="text/javascript">
                document.getElementById('btn-submit-filter-content').addEventListener('click', function(event) {
                    event.preventDefault()

                    const input = document.getElementById('filter-content');
                    const filterValue = encodeURIComponent(input.value.trim());

                    if (!filterValue) {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('filter')
                        window.location.href = url.toString();

                        return
                    };

                    const url = new URL(window.location.href);
                    url.searchParams.set('filter', filterValue);

                    window.location.href = url.toString();
                });

                const btnClearFilterContent = document.getElementById('btn-clear-filter-content')

                if (btnClearFilterContent) {
                    btnClearFilterContent.addEventListener('click', function(event) {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('filter')
                        window.location.href = url.toString();
                    });
                }

                function copyTextareaContent(event) {
                    const textArea = event.target.parentElement.children[0]

                    navigator.clipboard.writeText(textArea.value)
                    .then(() => window.alert('{lkn_hn_lang text="Content copied!"}'))
                    .catch(err => window.alert('{lkn_hn_lang text="Content copied!"}' + err));
                }
            </script>
        </div>

        <div class="col-md-12">
            <hr>
        </div>

        <div class="col-md-12">
            {* {include "pages/logs_pagination.tpl"} *}

            <div class="panel panel-default">
                <div class="table-responsive">
                    <table class="table table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>{lkn_hn_lang text="Action"}</th>
                                <th>{lkn_hn_lang text="Input"}</th>
                                <th>{lkn_hn_lang text="Output"}</th>
                                <th>{lkn_hn_lang text="Date"}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$page_params.logs item=$logs}
                                <tr>
                                    <td>{$logs->action}</td>
                                    <td>
                                        {if $logs->request === 'null'}
                                            <div style="width: 350px;">
                                                <hr>
                                            </div>
                                        {else}
                                            <div class="textarea-cont">
                                                <textarea
                                                    readonly
                                                    data-toggle="popover"
                                                    title="{lkn_hn_lang text="Input"}"
                                                    data-content="<pre>{$logs->request}</pre>"
                                                    data-html="true"
                                                    data-placement="bottom"
                                                    data-trigger="hover click"
                                                >{$logs->request}</textarea>
                                                <button
                                                    type="button"
                                                    class="btn btn-default btn-sm"
                                                    onclick="copyTextareaContent(event)"
                                                >
                                                    {lkn_hn_lang text="Copy"}</button>
                                            </div>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $logs->response === 'null'}
                                            <div style="width: 350px;">
                                                <hr>
                                            </div>
                                        {else}
                                            <div class="textarea-cont">
                                                <textarea
                                                    readonly
                                                    data-toggle="popover"
                                                    title="{lkn_hn_lang text="Output"}"
                                                    data-content="<pre>{$logs->response}</pre>"
                                                    data-html="true"
                                                    data-placement="bottom"
                                                    data-trigger="hover click"
                                                >{$logs->response}</textarea>
                                                <button
                                                    type="button"
                                                    class="btn btn-default btn-sm"
                                                    onclick="copyTextareaContent(event)"
                                                >
                                                    {lkn_hn_lang text="Copy"}</button>
                                            </div>
                                        {/if}
                                    </td>
                                    <td>
                                        {$logs->date}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>

            {include "pages/logs_pagination.tpl"}
        </div>
    </div>
{/block}
