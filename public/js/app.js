(function () {
    'use strict';

    const currencyFormatter = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function applyCurrencyMask(input) {
        input.addEventListener('input', function () {
            const digits = input.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').slice(0, 10);
            input.value = digits === '' ? '' : currencyFormatter.format(parseInt(digits, 10) / 100);
        });
    }

    function applyYearMask(input) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 4);
        });
    }

    document.querySelectorAll('[data-mask="currency"]').forEach(applyCurrencyMask);
    document.querySelectorAll('[data-mask="year"]').forEach(applyYearMask);

    if (window.TomSelect) {
        document.querySelectorAll('select[data-multi-select]').forEach(function (select) {
            new TomSelect(select, {
                plugins: ['remove_button'],
                placeholder: select.dataset.placeholder || '',
                hideSelected: true,
            });
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
})();
