window.FocusTrap = function FocusTrap(container, options) {
    options = options || {};
    var focusableSelector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
    var previousActiveElement = null;
    var isActive = false;

    function getFocusableElements() {
        return Array.from(container.querySelectorAll(focusableSelector)).filter(function (el) {
            return el.offsetParent !== null && !el.disabled && !el.hidden && el.tabIndex >= 0;
        });
    }

    function handleKeyDown(e) {
        if (!isActive) {
            return;
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            deactivate();
            if (typeof options.onEscape === 'function') {
                options.onEscape();
            }
            return;
        }
        if (e.key !== 'Tab') {
            return;
        }
        var focusables = getFocusableElements();
        if (focusables.length === 0) {
            e.preventDefault();
            return;
        }
        var first = focusables[0];
        var last = focusables[focusables.length - 1];
        var active = document.activeElement;
        if (e.shiftKey) {
            if (active === first || !container.contains(active)) {
                e.preventDefault();
                last.focus();
            }
        } else {
            if (active === last || !container.contains(active)) {
                e.preventDefault();
                first.focus();
            }
        }
    }

    function activate() {
        if (isActive) {
            return;
        }
        previousActiveElement = document.activeElement;
        isActive = true;
        document.addEventListener('keydown', handleKeyDown, true);
        var focusables = getFocusableElements();
        if (focusables.length && options.initialFocus !== false) {
            var initial = focusables[0];
            if (options.initialFocus) {
                for (var i = 0; i < focusables.length; i += 1) {
                    if (focusables[i] === options.initialFocus) {
                        initial = focusables[i];
                        break;
                    }
                }
            }
            window.setTimeout(function () {
                initial.focus();
            }, 0);
        }
    }

    function deactivate() {
        if (!isActive) {
            return;
        }
        isActive = false;
        document.removeEventListener('keydown', handleKeyDown, true);
        if (options.restoreFocus !== false && previousActiveElement && typeof previousActiveElement.focus === 'function') {
            window.setTimeout(function () {
                previousActiveElement.focus();
            }, 0);
        }
    }

    return {
        activate: activate,
        deactivate: deactivate,
        getFocusableElements: getFocusableElements
    };
};
