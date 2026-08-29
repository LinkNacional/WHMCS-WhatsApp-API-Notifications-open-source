window.document.addEventListener('DOMContentLoaded', () => {
    const scriptTag = document.querySelector('script[src$="safe_password_reset.js"]');
    const translations = JSON.parse(scriptTag.dataset.translations);
    const injectedToken = scriptTag.dataset.csrfToken || '';

    function translate(key) {
        return translations[key] || key
    }

    const passwordResetForm = document.querySelector('form[action*="/password/reset"]');
    const submitPasswordResetBtn = passwordResetForm.querySelector('button[type="submit"]')
    const passwordResetEmailInput = passwordResetForm.querySelector('input[type="email"]')

    passwordResetEmailInput.required = true

    const sentToPhoneDiv = document.createElement('p')
    sentToPhoneDiv.id = 'sentToPhoneInfo'
    sentToPhoneDiv.className = 'text-center'
    passwordResetForm.appendChild(sentToPhoneDiv)

    async function request_reset_password_notification() {
        submitPasswordResetBtn.innerHTML += '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'
        submitPasswordResetBtn.disabled = true

        const email = passwordResetEmailInput.value

        // Fase 1 (hardening): use o token CSRF que o WHMCS renderizou no formulário
        // (o valor injetado via data-csrf-token pode estar obsoleto, pois o WHMCS
        // regenera o token ao renderizar a página).
        const tokenInput = passwordResetForm.querySelector('input[name="token"], input[name="csrftoken"]')
        const csrfToken = (tokenInput && tokenInput.value) || injectedToken

        const res = await fetch(`/modules/addons/lknhooknotification/src/Core/api.php?endpoint=password/reset?email=${encodeURIComponent(email)}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ token: csrfToken })
        }).then(res => res.json())

        if (res.exceeded_try) {
            sentToPhoneDiv.innerHTML = translate('You have reached the maximum limit of requests. Check your email inbox or SPAM folder or the WhatsApp registered in your profile.')

            submitPasswordResetBtn.parentElement.remove()
            passwordResetEmailInput.parentElement.parentElement.remove()

            return
        }

        const sentToPhone = res?.sent_to_phone
        const sentToEmail = res?.sent_to_email

        if (sentToPhone && sentToEmail) {
            sentToPhoneDiv.innerHTML = `${translate('Sent to WhatsApp and email:')}<br>${sentToPhone}<br>${sentToEmail}`
        } else if (sentToPhone) {
            sentToPhoneDiv.innerHTML = `${translate('Sent to WhatsApp:')}<br>${sentToPhone}`
        } else if (sentToEmail) {
            sentToPhoneDiv.innerHTML = `${translate('Sent to email:')}<br>${sentToEmail}`
        } else {
            // Fase 1 (hardening): neutral message, avoids account enumeration.
            sentToPhoneDiv.innerHTML = translate('If an account with this email exists, we have sent the reset instructions.')
        }

        submitPasswordResetBtn.parentElement.remove()
        passwordResetEmailInput.parentElement.parentElement.remove()
    }

    submitPasswordResetBtn.addEventListener('click', async (e) => {
        e.preventDefault()

        if (!passwordResetForm.checkValidity()) {
            passwordResetForm.reportValidity()
            return
        }

        await request_reset_password_notification()
    })
})
