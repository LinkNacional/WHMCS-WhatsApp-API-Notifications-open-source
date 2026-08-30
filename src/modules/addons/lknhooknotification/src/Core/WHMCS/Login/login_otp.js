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
                .lkn-login-back { display: inline-flex; align-items: center; gap: 6px; margin: 0 0 12px; padding: 0; border: none; background: none; color: #007bff; cursor: pointer; font-size: 0.95rem; }
                .lkn-login-back:hover { text-decoration: underline; color: #0056b3; }
                .lkn-login-chooser-hint { margin-bottom: 14px; }
                .lkn-login-method { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 12px; }
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

        // Constrói um card com o mesmo visual do form de e-mail: clona o header
        // (título + subtítulo) e o footer (cadastro + esqueceu a senha) do form original.
        function buildCard() {
            const srcCard = loginForm.querySelector('.card');
            const srcBody = loginForm.querySelector('.card-body');
            const srcHeader = loginForm.querySelector('.card-body .mb-4');
            const srcFooter = loginForm.querySelector('.card-footer');
            const srcReset = loginForm.querySelector('a[href*="password/reset"], a[href*="/password/reset"]');

            const card = document.createElement('div');
            card.className = srcCard ? srcCard.className : 'card mw-540 mb-md-4 mt-md-4';

            const body = document.createElement('div');
            body.className = srcBody ? srcBody.className : 'card-body px-sm-5 py-5';

            if (srcHeader) {
                body.appendChild(srcHeader.cloneNode(true));
            }

            card.appendChild(body);

            if (srcFooter) {
                const footer = srcFooter.cloneNode(true);
                if (srcReset) {
                    const sep = document.createElement('span');
                    sep.textContent = ' · ';
                    footer.appendChild(sep);
                    footer.appendChild(srcReset.cloneNode(true));
                }
                card.appendChild(footer);
            }

            return { card, body };
        }

        let activeCard = null;
        let backBtn = null;

        function clearActiveCard() {
            if (activeCard) {
                activeCard.remove();
                activeCard = null;
            }
        }

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
            clearActiveCard();
            hideEmailForm();
            hideGoogle();
            renderChooser();
        }

        function renderChooser() {
            clearActiveCard();
            const { card, body } = buildCard();

            const hint = document.createElement('p');
            hint.className = 'text-muted lkn-login-chooser-hint';
            hint.textContent = t('login_otp_choose_method');
            body.appendChild(hint);

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
                b.className = 'btn ' + cls + ' btn-block lkn-login-method';
                b.innerHTML = labelHtml;
                b.addEventListener('click', () => {
                    setCookie(COOKIE_NAME, method, COOKIE_DAYS);
                    showMethod(method);
                });
                body.appendChild(b);
            });

            activeCard = card;
            loginForm.parentNode.insertBefore(card, loginForm);
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

        async function post(endpoint, params) {
            const qs = new URLSearchParams(params || {}).toString();
            const url = API_BASE + '?endpoint=' + endpoint + (qs ? '&' + qs : '');
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ token: csrfToken }),
            });
            return res.json();
        }

        function showWhatsApp() {
            clearActiveCard();
            const { card, body } = buildCard();

            const fields = document.createElement('div');
            fields.id = 'lkn-login-whatsapp';
            fields.innerHTML = `
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
            body.appendChild(fields);

            activeCard = card;
            loginForm.parentNode.insertBefore(card, loginForm);

            const phoneInput = fields.querySelector('#lkn-login-phone');
            const sendBtn = fields.querySelector('#lkn-login-send');
            const otpWrap = fields.querySelector('#lkn-login-otp-wrap');
            const otpInput = fields.querySelector('#lkn-login-otp');
            const verifyBtn = fields.querySelector('#lkn-login-verify');
            const msg = fields.querySelector('#lkn-login-msg');
            const accounts = fields.querySelector('#lkn-login-accounts');

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
                        await post('login/otp/send', { phone: phone });
                        setMsg(msg, t('login_otp_sent_hint'), false);
                        otpWrap.style.display = '';
                        otpInput.focus();
                    } catch (e) {
                        setMsg(msg, t('login_otp_generic_error'), true);
                    }
                });
            });

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

            function verify(phone, otp, clientId) {
                const params = { phone: phone, otp: otp };
                if (clientId) {
                    params.client_id = String(clientId);
                }
                return post('login/otp/verify', params);
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
            clearActiveCard();
            hideEmailForm();
            hideGoogle();
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
