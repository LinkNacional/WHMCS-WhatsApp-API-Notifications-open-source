(() => {
    function init() {
        const scriptTag = document.querySelector('script[src$="login_otp.js"]');
        if (!scriptTag) {
            return;
        }

        let translations = {};
        try {
            translations = JSON.parse(scriptTag.dataset.translations || '{}');
        } catch (e) {
            translations = {};
        }
        const injectedToken = scriptTag.dataset.csrfToken || '';

        const t = (key) => (typeof translations[key] === 'string' ? translations[key] : key);

        const COOKIE_NAME = 'lkn_notification_login_pref';
        const COOKIE_DAYS = 30;
        const API_BASE = '/modules/addons/lknhooknotification/src/Core/api.php';

        const loginForm = document.querySelector('form[action*="dologin"]');
        if (!loginForm) {
            return;
        }

        // Token CSRF: prefere o do formulário (atual da página); fallback ao injetado.
        const tokenInput = loginForm.querySelector('input[name="token"], input[name="csrftoken"]');
        const csrfToken = (tokenInput && tokenInput.value) || injectedToken;

        // Botão do Google (OAuth2) — best-effort, tema-agnóstico.
        const googleEl = Array.from(document.querySelectorAll('a, button')).find((el) =>
            /google/i.test(el.getAttribute('href') || '') || /google/i.test(el.textContent || '')
        ) || null;

        function setCookie(name, value, days) {
            const d = new Date();
            d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/';
        }

        function getCookie(name) {
            const m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
            return m ? decodeURIComponent(m[1]) : null;
        }

        function injectStyles() {
            if (document.getElementById('lkn-login-otp-style')) {
                return;
            }
            const style = document.createElement('style');
            style.id = 'lkn-login-otp-style';
            style.textContent = `
                #lkn-login-chooser, #lkn-login-whatsapp { margin: 0 auto 20px; max-width: 400px; text-align: center; }
                #lkn-login-chooser .lkn-login-chooser-title { margin-bottom: 15px; font-weight: 600; }
                #lkn-login-chooser .lkn-login-method { display: block; width: 100%; margin-bottom: 10px; }
                #lkn-login-whatsapp .lkn-login-msg { margin: 10px 0; font-size: 0.9em; color: #666; }
                #lkn-login-whatsapp input { margin-bottom: 10px; }
                #lkn-login-accounts .lkn-login-account { display: block; width: 100%; margin-bottom: 8px; }
                #lkn-login-accounts .lkn-login-accounts-title { margin: 10px 0; font-weight: 600; }
            `;
            document.head.appendChild(style);
        }

        function renderChooser() {
            const chooser = document.createElement('div');
            chooser.id = 'lkn-login-chooser';
            chooser.innerHTML = '<div class="lkn-login-chooser-title">' + t('login_otp_choose_method') + '</div>';

            [
                ['email', t('login_otp_email'), 'btn-primary'],
                ['whatsapp', t('login_otp_whatsapp'), 'btn-success'],
                ['google', t('login_otp_google'), 'btn-default'],
            ].forEach(([method, label, cls]) => {
                if (method === 'google' && !googleEl) {
                    return;
                }
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'btn ' + cls + ' lkn-login-method';
                b.textContent = label;
                b.addEventListener('click', () => {
                    setCookie(COOKIE_NAME, method, COOKIE_DAYS);
                    showMethod(method);
                });
                chooser.appendChild(b);
            });

            loginForm.parentNode.insertBefore(chooser, loginForm);
        }

        function hideEmailForm() {
            loginForm.style.display = 'none';
        }
        function showEmailForm() {
            loginForm.style.display = '';
        }
        function hideGoogle() {
            if (googleEl) googleEl.style.display = 'none';
        }
        function showGoogle() {
            if (googleEl) googleEl.style.display = '';
        }

        function removeWhatsApp() {
            const el = document.getElementById('lkn-login-whatsapp');
            if (el) el.remove();
        }

        function showWhatsApp() {
            removeWhatsApp();
            const box = document.createElement('div');
            box.id = 'lkn-login-whatsapp';
            box.innerHTML = `
                <label for="lkn-login-phone">${t('login_otp_phone_label')}</label>
                <input type="tel" id="lkn-login-phone" class="form-control" autocomplete="tel" placeholder="+5511999999999">
                <button type="button" id="lkn-login-send" class="btn btn-primary btn-block">${t('login_otp_send_code')}</button>
                <div id="lkn-login-msg" class="lkn-login-msg"></div>
                <div id="lkn-login-otp-wrap" style="display:none">
                    <label for="lkn-login-otp">${t('login_otp_code_label')}</label>
                    <input type="text" inputmode="numeric" maxlength="6" id="lkn-login-otp" class="form-control" autocomplete="one-time-code">
                    <button type="button" id="lkn-login-verify" class="btn btn-success btn-block">${t('login_otp_verify')}</button>
                </div>
                <div id="lkn-login-accounts"></div>
            `;
            loginForm.parentNode.insertBefore(box, loginForm.nextSibling);

            const phoneInput = box.querySelector('#lkn-login-phone');
            const sendBtn = box.querySelector('#lkn-login-send');
            const otpWrap = box.querySelector('#lkn-login-otp-wrap');
            const otpInput = box.querySelector('#lkn-login-otp');
            const verifyBtn = box.querySelector('#lkn-login-verify');
            const msg = box.querySelector('#lkn-login-msg');
            const accounts = box.querySelector('#lkn-login-accounts');

            async function post(endpoint, query) {
                const res = await fetch(API_BASE + '?endpoint=' + endpoint + query, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ token: csrfToken }),
                });
                return res.json();
            }

            function errorText(code) {
                return translations['login_otp_error_' + code] || t('login_otp_generic_error');
            }

            sendBtn.addEventListener('click', async () => {
                const phone = (phoneInput.value || '').trim();
                if (!phone) {
                    msg.textContent = t('login_otp_phone_required');
                    return;
                }
                sendBtn.disabled = true;
                try {
                    await post('login/otp/send', '?phone=' + encodeURIComponent(phone));
                    // Resposta sempre neutra (anti-enumeração).
                    msg.textContent = t('login_otp_sent_hint');
                    otpWrap.style.display = '';
                    otpInput.focus();
                } catch (e) {
                    msg.textContent = t('login_otp_generic_error');
                } finally {
                    sendBtn.disabled = false;
                }
            });

            async function verify(phone, otp, clientId) {
                const q = '?phone=' + encodeURIComponent(phone) + '&otp=' + encodeURIComponent(otp) + (clientId ? '&client_id=' + encodeURIComponent(clientId) : '');
                return post('login/otp/verify', q);
            }

            function handleVerifyResult(res) {
                if (res && res.logged_in && res.redirect_url) {
                    window.location.href = res.redirect_url;
                    return;
                }
                if (res && res.need_account_selection && Array.isArray(res.accounts)) {
                    renderAccountSelection(res.accounts);
                    return;
                }
                msg.textContent = errorText((res && res.error) || '');
            }

            verifyBtn.addEventListener('click', async () => {
                const phone = (phoneInput.value || '').trim();
                const otp = (otpInput.value || '').trim();
                if (!phone || !otp) {
                    msg.textContent = t('login_otp_code_required');
                    return;
                }
                verifyBtn.disabled = true;
                try {
                    handleVerifyResult(await verify(phone, otp));
                } catch (e) {
                    msg.textContent = t('login_otp_generic_error');
                } finally {
                    verifyBtn.disabled = false;
                }
            });

            function renderAccountSelection(list) {
                accounts.innerHTML = '<div class="lkn-login-accounts-title">' + t('login_otp_select_account') + '</div>';
                list.forEach((acc) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'btn btn-default lkn-login-account';
                    b.textContent = acc.email;
                    b.addEventListener('click', async () => {
                        try {
                            handleVerifyResult(await verify(
                                (phoneInput.value || '').trim(),
                                (otpInput.value || '').trim(),
                                acc.id
                            ));
                        } catch (e) {
                            msg.textContent = t('login_otp_generic_error');
                        }
                    });
                    accounts.appendChild(b);
                });
            }
        }

        function showMethod(method) {
            const chooser = document.getElementById('lkn-login-chooser');
            if (chooser) chooser.remove();
            hideEmailForm();
            hideGoogle();
            removeWhatsApp();

            if (method === 'email') {
                showEmailForm();
            } else if (method === 'whatsapp') {
                showWhatsApp();
            } else if (method === 'google') {
                showGoogle();
            }
        }

        injectStyles();

        const pref = getCookie(COOKIE_NAME);
        if (pref === 'email' || pref === 'whatsapp' || pref === 'google') {
            showMethod(pref);
        } else {
            hideEmailForm();
            hideGoogle();
            renderChooser();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
