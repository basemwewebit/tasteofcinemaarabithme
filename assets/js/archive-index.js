/**
 * Filing-cabinet index: regroup archive rows by month, by category, or show all.
 *
 * Rows carry data-month / data-month-label / data-category / data-category-label.
 * Ads injected between rows stay anchored to the row they followed. No motion.
 */
(function () {
    'use strict';

    var container = document.querySelector('[data-archive-index]');
    if (!container) {
        return;
    }

    var buttons = Array.prototype.slice.call(document.querySelectorAll('.archive-groupbar__btn'));
    if (!buttons.length) {
        return;
    }

    var original = Array.prototype.slice.call(container.children);
    var announcer = document.querySelector('[data-archive-announcer]');

    function makeHeader(text) {
        var header = document.createElement('h3');
        header.className = 'archive-group__title';
        header.textContent = text;
        return header;
    }

    function rebuild(mode) {
        var fragment = document.createDocumentFragment();

        if (mode === 'all') {
            original.forEach(function (el) {
                fragment.appendChild(el);
            });
            container.replaceChildren(fragment);
            return;
        }

        var groups = [];
        var groupIndex = {};
        var adsByRow = new Map();
        var leadingAds = [];
        var previousRow = null;

        original.forEach(function (el) {
            if (el.classList.contains('archive-row')) {
                var key = mode === 'month' ? (el.dataset.month || '') : (el.dataset.category || '');
                if (!Object.prototype.hasOwnProperty.call(groupIndex, key)) {
                    groupIndex[key] = groups.length;
                    groups.push({
                        key: key,
                        label: mode === 'month' ? (el.dataset.monthLabel || key) : (el.dataset.categoryLabel || key),
                        rows: []
                    });
                }
                groups[groupIndex[key]].rows.push(el);
                previousRow = el;
            } else if (previousRow) {
                if (!adsByRow.has(previousRow)) {
                    adsByRow.set(previousRow, []);
                }
                adsByRow.get(previousRow).push(el);
            } else {
                leadingAds.push(el);
            }
        });

        leadingAds.forEach(function (el) {
            fragment.appendChild(el);
        });

        groups.forEach(function (group) {
            fragment.appendChild(makeHeader(group.label));
            group.rows.forEach(function (row) {
                fragment.appendChild(row);
                (adsByRow.get(row) || []).forEach(function (ad) {
                    fragment.appendChild(ad);
                });
            });
        });

        container.replaceChildren(fragment);
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            buttons.forEach(function (other) {
                var isActive = other === btn;
                other.classList.toggle('is-active', isActive);
                other.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
            var mode = btn.dataset.group || 'all';
            rebuild(mode);
            if (announcer) {
                announcer.textContent = mode === 'all'
                    ? 'تم عرض كل المقالات في الفهرس'
                    : 'تم تجميع الفهرس ' + (mode === 'month' ? 'حسب الشهر' : 'حسب التصنيف');
            }
        });
    });

    rebuild('month');
})();
