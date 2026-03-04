/**
 * Google reCAPTCHA v3 Interceptor
 * Injected automatically via PHP conditionally on pages with targeted forms.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof tocRecaptchaConfig === 'undefined' || !tocRecaptchaConfig.siteKey) {
        return;
    }

    const forms = document.querySelectorAll(tocRecaptchaConfig.selectors.join(','));

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            grecaptcha.ready(function () {
                grecaptcha.execute(tocRecaptchaConfig.siteKey, {action: 'submit'}).then(function (token) {
                    
                    // Remove any existing token input to avoid duplicates
                    const existingInput = form.querySelector('input[name="g-recaptcha-response"]');
                    if (existingInput) {
                        existingInput.remove();
                    }

                    // Append the dynamic token to the form
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'g-recaptcha-response';
                    tokenInput.value = token;
                    form.appendChild(tokenInput);

                    // Submit the form programmatically, bypassing this 'submit' event listener
                    form.submit();
                });
            });
        });
    });
});
