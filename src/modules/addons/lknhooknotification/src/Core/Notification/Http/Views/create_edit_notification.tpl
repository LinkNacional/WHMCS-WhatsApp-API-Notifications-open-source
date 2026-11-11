{extends "{$lkn_hn_layout_path}/layout/layout.tpl"}

{block "page_title"}
    {lkn_hn_lang text="Editing Notification [1] - [2]" params=[{lkn_hn_lang text=$page_params.editing_notification->code},
    $page_params.editing_template->lang]}
{/block}

{block "page_content"}
    <div style="max-width: 600px; min-width: 600px;">
        <div class="row">
            <div class="col-md-12">
                <form
                    id="notification-form"
                    class="form-horizontal"
                    method="POST"
                    target="_self"
                >
                    <input
                        type="hidden"
                        name="lkn_hn_token"
                        value="{$csrf_token}"
                    >
                    {* LANGUAGE *}

                    <div class="form-group">
                        <div class="col-sm-12">
                            <label
                                for="locale"
                                class="control-label"
                            >
                                <h2>{lkn_hn_lang text='Language'}</h2>
                            </label>

                            <p class="help-block">
                                {lkn_hn_lang text='This template will only be sent to clients with the same language as defined below.'}
                            </p>
                        </div>
                        <div class="col-sm-12">
                            <select
                                id="locale"
                                name="locale"
                                class="form-control"
                                {if $page_params.editing_template}
                                    readonly
                                {else}
                                    onchange="document.getElementById('notification-form').submit()"
                                {/if}
                            >
                                <option value="">{lkn_hn_lang text="Select a language"}</option>

                                {foreach from=$lkn_hn_locales item=$locale}
                                    <option
                                        value="{$locale['value']}"
                                        {if $page_params.editing_locale === $locale['value']}
                                            selected
                                        {/if}
                                    >
                                        {$locale['label']}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        {if $page_params.editing_template}
                            <div class="col-sm-12">
                                <a
                                    type="button"
                                    class="btn btn-link btn-sm"
                                    href="{$lkn_hn_base_endpoint}&page=notifications/{$page_params.editing_notification->code}/templates/new"
                                    target="_blank"
                                >
                                    <i class="fas fa-plus"></i>
                                    {lkn_hn_lang text="Setup another language"}
                                </a>
                            </div>
                        {/if}
                    </div>

                    {if $page_params.request_locale_selection}
                        <div
                            class="alert alert-info"
                            role="alert"
                            style="margin-top: 40px;"
                        >
                            {lkn_hn_lang text="Please, choose a language."}
                        </div>
                    {else}
                        {* PLATFORM *}

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label
                                    for="platform"
                                    class="control-label"
                                >
                                    <h2>{lkn_hn_lang text='Platform'}</h2>
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <select
                                    id="platform"
                                    name="platform"
                                    class="form-control"
                                    onchange="window.location='?module=lknhooknotification&page=notifications/{$page_params.editing_notification->code}/templates/{$page_params.editing_locale}&platform='+this.value"
                                    {if $page_params.editing_template}
                                        readonly
                                    {/if}
                                >
                                    <option value="">{lkn_hn_lang text="Select a platform"}</option>

                                    {foreach from=$page_params.platform_list item=$platform}
                                        {if $platform->value !== 'mod'}
                                            <option
                                                value="{$platform->value}"
                                                {if $page_params.editing_template->platform === $platform}
                                                    selected
                                                {/if}
                                            >
                                                {$platform->label()}
                                            </option>
                                        {/if}
                                    {/foreach}
                                </select>
                            </div>
                            {if $page_params.editing_template}
                                <div class="col-sm-12">
                                    <button
                                        id="btn-enable-platform-change"
                                        type="button"
                                        class="btn btn-link btn-sm"
                                    >
                                        <i class="fas fa-exchange-alt"></i>
                                        {lkn_hn_lang text="Change template platform"}
                                    </button>

                                    <script type="text/javascript">
                                        const btnEnablePlatformChange = document.getElementById('btn-enable-platform-change')

                                        btnEnablePlatformChange.addEventListener('click', () => {
                                            btnEnablePlatformChange.style.display = 'none'

                                            const platformSelect = document.getElementById('platform')

                                            platformSelect.readonly = false
                                            platformSelect.showPicker();
                                        })
                                    </script>
                                </div>
                            {/if}
                        </div>
                    {/if}


                    {* TEMPLATE *}

                    {if $page_params.request_platform_selection}
                        {if empty($page_params.platform_list)}
                            <div
                                class="alert alert-warning"
                                role="alert"
                                style="margin-top: 40px;"
                            >
                                {lkn_hn_lang text='You must configure and enable a platform first. Go to the "Settings" menu.'}
                            </div>
                        {else}

                            <div
                                class="alert alert-info"
                                role="alert"
                                style="margin-top: 40px;"
                            >
                                {lkn_hn_lang text="Please, choose a platform."}
                            </div>
                        {/if}
                    {/if}

                    {if !$page_params.request_locale_selection && !$page_params.request_platform_selection}
                        {$page_params.template_editor_view}

                        <div
                            class="form-group"
                            style="margin-top: 60px;"
                        >
                            <div class="col-sm-12">
                                <button
                                    type="submit"
                                    class="btn btn-primary btn-block"
                                    style="max-width: 160px; margin: 0 auto 0;"
                                    onclick="return confirmSave()"
                                >
                                    {lkn_hn_lang text="Save"}
                                </button>
                            </div>

                            {* <input
                                type="hidden"
                                name="operation"
                                id="operationType"
                                value=""
                            >

                            <div class="col-sm-6">
                                <button
                                    type="submit"
                                    class="btn btn-danger btn-block"
                                    style="max-width: 160px; margin: 0 auto 0;"
                                    onclick="return confirmDelete()"
                                >
                                    {lkn_hn_lang text="Delete Template"}
                                </button>
                            </div> *}
                        </div>

                        <input
                            type="hidden"
                            name="operation"
                            id="operationType"
                            value=""
                        >

                        <script type="text/javascript">
                            function confirmDelete() {
                                if (confirm("{lkn_hn_lang text='Are you sure you want to delete this template?'}")) {
                                document.getElementById('operationType').value = 'delete';

                                return true;
                            }

                            return false;
                            }

                            function confirmSave() {
                                document.getElementById('operationType').value = 'save';

                                return confirm("{lkn_hn_lang text="Are you sure? The changes will take effect immediately."}");
                            }
                        </script>
                    {/if}
                </form>
            </div>
        </div>
    </div>
{/block}
