/**
 * Google reCAPTCHA v3 Interceptor
 * Injected automatically via PHP conditionally on pages with targeted forms.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof tocRecaptchaConfig === 'undefined' || !tocRecaptchaConfig.siteKey) {
        return;
    }

    // A selector may match a field inside the form (a Reader submission's marker) or the form itself.
    const forms = Array.from(document.querySelectorAll(tocRecaptchaConfig.selectors.join(',')), function (el) {
        return el.form || el;
    });

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            // Google's script blocked: submit without a token so the server's rejection shows, not a dead button.
            if (typeof grecaptcha === 'undefined' || !grecaptcha.enterprise) {
                return;
            }
            e.preventDefault();

            grecaptcha.enterprise.ready(function () {
                grecaptcha.enterprise.execute(tocRecaptchaConfig.siteKey, {action: 'submit'}).then(function (token) {
                    
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

                    // Add hidden input to simulate the submit button being pressed
                    // This is required because form.submit() bypasses the submit button's value
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && submitBtn.name) {
                        const hiddenSubmit = document.createElement('input');
                        hiddenSubmit.type = 'hidden';
                        hiddenSubmit.name = submitBtn.name;
                        hiddenSubmit.value = submitBtn.value || '1';
                        form.appendChild(hiddenSubmit);
                    }

                    // Submit the form programmatically, bypassing this 'submit' event listener
                    form.submit();
                }).catch(function(err) {});
            });
        });
    });
});
