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

        const loginForm = Array.from(document.querySelectorAll('form')).find((f) => f.querySelector('input[name="username"]'))
            || document.querySelector('form[action*="dologin"]')
            || null;
        if (!loginForm) {
            return;
        }

        const tokenInput = loginForm.querySelector('input[name="token"], input[name="csrftoken"]');
        const csrfToken = (tokenInput && tokenInput.value) || injectedToken;

        const googleEl = document.querySelector('#login-social')
            || document.querySelector('.btn-google, [class*="btn-google"], #btnGoogleSignin1')
            || Array.from(document.querySelectorAll('a, button')).find((el) =>
                /google/i.test(el.getAttribute('href') || '') || /google/i.test(el.textContent || '')
            )
            || null;

        function setCookie(name, value, days) {
            const d = new Date();
            d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/';
        }

        function getCookie(name) {
            const m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
            return m ? decodeURIComponent(m[1]) : null;
        }

        function deleteCookie(name) {
            document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        }

        function injectStyles() {
            if (document.getElementById('lkn-login-otp-style')) {
                return;
            }
            const style = document.createElement('style');
            style.id = 'lkn-login-otp-style';
            style.textContent = `
                #lkn-login-chooser, #lkn-login-whatsapp { margin: 0 auto 20px; max-width: 400px; }
                #lkn-login-chooser { text-align: center; }
                #lkn-login-chooser .lkn-login-chooser-title { margin-bottom: 18px; font-weight: 600; font-size: 1.05rem; }
                #lkn-login-chooser .lkn-login-method { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-bottom: 12px; padding: 10px 14px; }
                .lkn-login-back { display: inline-flex; align-items: center; gap: 6px; margin: 0 0 14px; padding: 0; border: none; background: none; color: #007bff; cursor: pointer; font-size: 0.95rem; }
                .lkn-login-back:hover { text-decoration: underline; color: #0056b3; }
                #lkn-login-whatsapp { text-align: left; }
                #lkn-login-whatsapp label { font-weight: 500; }
                #lkn-login-whatsapp input { margin-bottom: 12px; }
                #lkn-login-whatsapp .lkn-login-msg { margin: 10px 0; font-size: 0.9em; color: #666; }
                #lkn-login-whatsapp .lkn-login-msg.lkn-login-error { color: #d9534f; }
                #lkn-login-accounts .lkn-login-account { display: block; width: 100%; margin-bottom: 8px; text-align: left; }
                #lkn-login-accounts .lkn-login-accounts-title { margin: 10px 0; font-weight: 600; }
            `;
            document.head.appendChild(style);
        }

        async function withLoading(btn, fn) {
            if (!btn) {
                return fn();
            }
            btn.disabled = true;
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm';
            spinner.setAttribute('role', 'status');
            spinner.setAttribute('aria-hidden', 'true');
            btn.insertBefore(spinner, btn.firstChild);
            try {
                return await fn();
            } finally {
                btn.disabled = false;
                if (spinner.parentNode) spinner.remove();
            }
        }

        function setMsg(el, text, isError) {
            el.textContent = text;
            el.classList.toggle('lkn-login-error', !!isError);
        }

        let backBtn = null;

        function removeBackButton() {
            if (backBtn) {
                backBtn.remove();
                backBtn = null;
            }
        }

        function ensureBackButton() {
            if (backBtn) {
                return backBtn;
            }
            backBtn = document.createElement('button');
            backBtn.type = 'button';
            backBtn.className = 'lkn-login-back';
            backBtn.innerHTML = '<i class="fas fa-arrow-left"></i> ' + t('login_otp_back');
            backBtn.addEventListener('click', goBack);
            loginForm.parentNode.insertBefore(backBtn, loginForm);
            return backBtn;
        }

        function goBack() {
            deleteCookie(COOKIE_NAME);
            removeBackButton();
            hideEmailForm();
            hideGoogle();
            removeWhatsApp();
            renderChooser();
        }

        function renderChooser() {
            const chooser = document.createElement('div');
            chooser.id = 'lkn-login-chooser';
            chooser.innerHTML = '<div class="lkn-login-chooser-title">' + t('login_otp_choose_method') + '</div>';

            [
                ['email', '<i class="fas fa-envelope"></i> ' + t('login_otp_email'), 'btn-primary'],
                ['whatsapp', '<i class="fab fa-whatsapp"></i> ' + t('login_otp_whatsapp'), 'btn-success'],
                ['google', '<i class="fab fa-google"></i> ' + t('login_otp_google'), 'btn-default'],
            ].forEach(([method, labelHtml, cls]) => {
                if (method === 'google' && !googleEl) {
                    return;
                }
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'btn ' + cls + ' lkn-login-method';
                b.innerHTML = labelHtml;
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

            sendBtn.addEventListener('click', () => {
                const phone = (phoneInput.value || '').trim();
                if (!phone) {
                    setMsg(msg, t('login_otp_phone_required'), true);
                    return;
                }
                withLoading(sendBtn, async () => {
                    try {
                        await post('login/otp/send', '?phone=' + encodeURIComponent(phone));
                        setMsg(msg, t('login_otp_sent_hint'), false);
                        otpWrap.style.display = '';
                        otpInput.focus();
                    } catch (e) {
                        setMsg(msg, t('login_otp_generic_error'), true);
                    }
                });
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
                setMsg(msg, errorText((res && res.error) || ''), true);
            }

            verifyBtn.addEventListener('click', () => {
                const phone = (phoneInput.value || '').trim();
                const otp = (otpInput.value || '').trim();
                if (!phone || !otp) {
                    setMsg(msg, t('login_otp_code_required'), true);
                    return;
                }
                withLoading(verifyBtn, async () => {
                    try {
                        handleVerifyResult(await verify(phone, otp));
                    } catch (e) {
                        setMsg(msg, t('login_otp_generic_error'), true);
                    }
                });
            });

            function renderAccountSelection(list) {
                accounts.innerHTML = '<div class="lkn-login-accounts-title">' + t('login_otp_select_account') + '</div>';
                list.forEach((acc) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'btn btn-default lkn-login-account';
                    b.textContent = acc.email;
                    b.addEventListener('click', () => {
                        withLoading(b, async () => {
                            try {
                                handleVerifyResult(await verify(
                                    (phoneInput.value || '').trim(),
                                    (otpInput.value || '').trim(),
                                    acc.id
                                ));
                            } catch (e) {
                                setMsg(msg, t('login_otp_generic_error'), true);
                            }
                        });
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
            ensureBackButton();

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
