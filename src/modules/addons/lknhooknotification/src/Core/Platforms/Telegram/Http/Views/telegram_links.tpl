{extends "layout/layout.tpl"}

{block "page_title"}
    {lkn_hn_lang text="Telegram links"}
{/block}

{block "page_content"}
    <div class="panel panel-default" style="margin-top: 20px;">
        <div class="panel-heading">
            <h3 class="panel-title">{lkn_hn_lang text="Clients linked to Telegram"}</h3>
        </div>
        <div class="panel-body">
            {if $page_params.links|@count == 0}
                <p>{lkn_hn_lang text="No Telegram links found."}</p>
            {else}
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{lkn_hn_lang text="Client"}</th>
                                <th>{lkn_hn_lang text="Email"}</th>
                                <th>{lkn_hn_lang text="Chat ID"}</th>
                                <th>{lkn_hn_lang text="Username"}</th>
                                <th>{lkn_hn_lang text="Linked at"}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$page_params.links item=$link}
                                <tr>
                                    <td>{$link.client_name} (#{$link.client_id})</td>
                                    <td>{$link.email}</td>
                                    <td>{$link.chat_id}</td>
                                    <td>{$link.username}</td>
                                    <td>{$link.linked_at}</td>
                                    <td class="text-right">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="lkn_hn_token" value="{$csrf_token}">
                                            <input type="hidden" name="unlink-telegram" value="1">
                                            <input type="hidden" name="client-id" value="{$link.client_id}">
                                            <button type="submit" class="btn btn-danger btn-xs">
                                                {lkn_hn_lang text="Unlink"}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {/if}
        </div>
    </div>
{/block}
