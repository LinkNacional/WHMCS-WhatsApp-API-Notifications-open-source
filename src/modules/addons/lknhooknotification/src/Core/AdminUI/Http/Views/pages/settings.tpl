{* https://getbootstrap.com/docs/3.4/css/#forms-example *}
{extends "layout/layout.tpl"}

{block "page_title"}
    {lkn_hn_lang text="[1] Settings" params=[$page_params.platform_title]}
{/block}

{block "page_content"}
    <style>
        #lkn-hn-settings-form .control-label {
            text-align: left !important;
        }

        #lkn-hn-settings-form select,
        #lkn-hn-settings-form input {
            max-width: 400px;
        }
    </style>
    <form
        id="lkn-hn-settings-form"
        class="form-horizontal"
        method="post"
        target="_self"
        autocomplete="off"
    >
        <input
            type="hidden"
            name="lkn_hn_token"
            value="{$csrf_token}"
        >
        <input
            type="hidden"
            name="placeholder"
            value="placeholder"
        >
        <div class="row">
            <div
                {if $platform_settings_controller_output}
                    class="col-md-6"
                {else}
                    class="col-md-12"
                {/if}
            >
                {foreach from=$page_params.settings_df item=$setting}
                    {if isset($setting['hide'])}

                    {elseif isset($setting['separator'])}
                        <h2>
                            <strong>{$setting['title']}</strong>
                        </h2>
                        <span class="help-block">
                            {$setting['description']}
                        </span>
                    {elseif in_array($setting['type'], ['text', 'password', 'url', 'number'])}
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label
                                    for="{$setting['id']}"
                                    class="control-label"
                                >
                                    {$setting['label']}
                                    {if isset($setting['popover-config'])}
                                        <span
                                            tabindex="0"
                                            role="button"
                                            data-toggle="popover"
                                            data-trigger="hover click"
                                            title="{$setting['popover-config']['popover-title']}"
                                            data-content="
                                                    {foreach $setting['popover-config']['popover-images'] item=$images}
                                                        <img ' src='{$lkn_hn.system_url}modules/addons/lknhooknotification/src/Core/assets/{$images['popover-img']}' width='{$images['popover-width']} style='text-aling:center; margin-bottom:10px;' alt='Imagem'>
                                                    {/foreach}

                                                {if isset($setting['popover-config']['popover-description'])}
                                                    <p> {$setting['popover-config']['popover-description']}</p>
                                                {/if}"
                                            data-html="true"
                                        ><i class="fas fa-question-circle"></i></span>
                                    {/if}
                                </label>

                                <span class="help-block">
                                    {$setting['description']}
                                </span>
                            </div>
                            <div class="col-sm-6">
                                <input
                                    type="{$setting['type']}"
                                    class="form-control"
                                    id="{$setting['id']}"
                                    name="{$setting['id']}"
                                    value="{$setting['current']}"
                                    autocomplete="{if $setting['type'] === 'password'}new-password{else}off{/if}"
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                    spellcheck="false"
                                >
                                {if $setting['below_field']}
                                    <span class="help-block">{$setting['below_field']['title']}</span>
                                    <pre style='max-width: 400px;'>{$setting['below_field']['code']}</pre>
                                {/if}
                            </div>
                        </div>
                    {elseif $setting['type'] === 'textarea'}
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label
                                    for="{$setting['id']}"
                                    class="control-label"
                                >
                                    {$setting['label']}
                                    {if isset($setting['popover-config'])}
                                        <span
                                            tabindex="0"
                                            role="button"
                                            data-toggle="popover"
                                            data-trigger="hover click"
                                            title="{$setting['popover-config']['popover-title']}"
                                            data-content="
                                                    {foreach $setting['popover-config']['popover-images'] item=$images}
                                                        <img ' src='{$lkn_hn.system_url}modules/addons/lknhooknotification/src/Core/assets/{$images['popover-img']}' width='{$images['popover-width']} style='text-aling:center; margin-bottom:10px;' alt='Imagem'>
                                                    {/foreach}

                                                {if isset($setting['popover-config']['popover-description'])}
                                                    <p> {$setting['popover-config']['popover-description']}</p>
                                                {/if}"
                                            data-html="true"
                                        ><i class="fas fa-question-circle"></i></span>
                                    {/if}
                                </label>

                                <span class="help-block">
                                    {$setting['description']}
                                </span>
                            </div>
                            <div class="col-sm-6">
                                <textarea
                                    class="form-control"
                                    id="{$setting['id']}"
                                    name="{$setting['id']}"
                                    rows="3"
                                    style="font-family: monospace; resize: none; min-height: 350px; max-height: 350px; min-width: 100%; max-width: 450px;"
                                >{$setting['current']}</textarea>
                            </div>
                        </div>
                    {elseif in_array($setting['type'], ['select', 'multiple'])}
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label
                                    for="{$setting['id']}"
                                    class="control-label"
                                >
                                    {$setting['label']}
                                    {if isset($setting['popover-config'])}
                                        <span
                                            tabindex="0"
                                            role="button"
                                            data-toggle="popover"
                                            data-trigger="hover click"
                                            title="{$setting['popover-config']['popover-title']}"
                                            data-content="
                                                    {foreach $setting['popover-config']['popover-images'] item=$images}
                                                        <img ' src='{$lkn_hn.system_url}modules/addons/lknhooknotification/src/Core/assets/{$images['popover-img']}' width='{$images['popover-width']} style='text-aling:center; margin-bottom:10px;' alt='Imagem'>
                                                    {/foreach}

                                                {if isset($setting['popover-config']['popover-description'])}
                                                    <p> {$setting['popover-config']['popover-description']}</p>
                                                {/if}"
                                            data-html="true"
                                        ><i class="fas fa-question-circle"></i></span>
                                    {/if}
                                </label>

                                <span class="help-block">
                                    {$setting['description']}

                                    {if isset($setting['description_link'])}
                                        <a href="{$setting['description_link']['link']}">
                                            {$setting['description_link']['label']}
                                        </a>
                                    {/if}
                                </span>
                            </div>
                            <div class="col-sm-6">
                                <select
                                    id="{$setting['id']}"
                                    class="form-control"
                                    {if $setting['type'] === 'multiple'}
                                        multiple
                                        name="{$setting['id']}[]"
                                    {else}
                                        name="{$setting['id']}"
                                    {/if}
                                >
                                    {if isset($setting['default']) && is_array($setting['default'])}
                                        <option value="{$setting['default']['value']}">
                                            {$setting['default']['label']}
                                        </option>
                                    {/if}

                                    {if $setting['options'] === 'lkn_hn_locales'}
                                        {foreach from=$lkn_hn_locales item=$locale}
                                            <option
                                                value="{$locale['value']}"
                                                {if $setting['current'] == $locale['value']}
                                                    selected
                                                {/if}
                                            >
                                                {$locale['label']}
                                            </option>
                                        {/foreach}
                                    {elseif $setting['options'] === 'lkn_hn_custom_fields'}
                                        {foreach from=$lkn_hn_custom_fields item=$settingOption}
                                            <option
                                                value="{$settingOption['value']}"
                                                {if $setting['type'] === 'multiple'}
                                                    {if $setting['current'] && in_array($settingOption['value'], $setting['current'])}
                                                        selected
                                                    {/if}
                                                {else}
                                                    {if $setting['current'] == $settingOption['value']}
                                                        selected
                                                    {/if}
                                                {/if}
                                            >
                                                {$settingOption['value']} - {$settingOption['label']}
                                            </option>
                                        {/foreach}
                                    {else}
                                        {foreach from=$setting['options'] item=$settingOption}
                                            <option
                                                value="{$settingOption['value']}"
                                                {if $setting['type'] === 'multiple'}
                                                    {if $setting['current'] && in_array($settingOption['value'], $setting['current'])}
                                                        selected
                                                    {/if}
                                                {else}
                                                    {if $setting['current'] == $settingOption['value']}
                                                        selected
                                                    {/if}
                                                {/if}
                                            >
                                                {$settingOption['label']}
                                            </option>
                                        {/foreach}
                                    {/if}
                                </select>
                                {if isset($setting['description_right_link'])}
                                    <span class="help-block">
                                        <a
                                            href="{$setting['description_right_link']['link']}"
                                            target="_blank"
                                        >
                                            {$setting['description_right_link']['label']}
                                        </a>
                                    </span>
                                {else isset($setting['description_link'])}
                                    <span class="help-block">
                                        <a
                                            href="{$setting['description_link']['link']}"
                                            target="_blank"
                                        >
                                            {$setting['description_link']['label']}
                                        </a>
                                    </span>
                                {/if}
                            </div>
                        </div>
                    {else}
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label
                                    for="{$setting['id']}"
                                    class="control-label"
                                >
                                    {$setting['label']}
                                </label>

                                <span class="help-block">
                                    {$setting['description']}
                                </span>
                            </div>
                            <div class="col-sm-6">
                                <div class="checkbox">
                                    <label>
                                        <input
                                            id="{$setting['id']}"
                                            name="{$setting['id']}"
                                            type="checkbox"
                                            {if $setting['current']}
                                                checked
                                            {/if}
                                        >
                                        {$setting['label']}
                                    </label>
                                </div>

                                {if !empty($setting['warning_on_unchecked']) && !$setting['current']}
                                    <br>
                                    <div class="alert alert-warning">
                                        {$setting['warning_on_unchecked']}
                                    </div>
                                {/if}
                            </div>
                        </div>
                    {/if}

                    {if !isset($setting['hide'])}
                        <hr>
                    {/if}
                {/foreach}

                <div
                    class="form-group"
                    style="margin-top: 60px;"
                >
                    <div class="col-sm-12">
                        <button
                            type="submit"
                            class="btn btn-primary btn-block"
                            style="max-width: 160px; margin: 0 auto 0;"
                            onclick="return confirmSubmit('{lkn_hn_lang text="Are you sure? The settings will take effect immediately." params=[$page_params.platform_title]}')"
                        >
                            {lkn_hn_lang text="Save Settings"}
                        </button>
                    </div>
                </div>
            </div>

            {if $platform_settings_controller_output}
                <div class="col-md-6">
                    {$platform_settings_controller_output}
                </div>
            {/if}
        </div>
    </form>
{/block}
