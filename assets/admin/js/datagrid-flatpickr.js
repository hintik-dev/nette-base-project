import 'flatpickr/dist/flatpickr.min.css';
import flatpickr from 'flatpickr';
import { Czech } from 'flatpickr/dist/l10n/cs.js';
import naja from 'naja';

const locales = {
    cs: Czech,
};

const toBool = (value) => value === 'true' || value === '1';

const parseDateWithFallback = (value, preferredFormat) => {
    if (!value) {
        return null;
    }

    const raw = String(value).replaceAll('\u00A0', ' ').trim();
    if (!raw) {
        return null;
    }

    const formats = [
        preferredFormat,
        'j. n. Y H:i:s',
        'j. n. Y H:i',
        'j.n.Y H:i:s',
        'j.n.Y H:i',
        'j. n. Y',
        'j.n.Y',
        'd.m.Y H:i',
        'd.m.Y H:i:S',
        'd.m.Y',
        'd/m/Y H:i',
        'd/m/Y',
        'm/d/Y H:i',
        'm/d/Y',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
    ];

    for (const format of formats) {
        const parsed = flatpickr.parseDate(raw, format);
        if (parsed) {
            return parsed;
        }
    }

    return null;
};

const destroyLegacyDatepicker = (input) => {
    // Datagrid range filter defaultně používá bootstrap-datepicker přes data-provide.
    // Pro flatpickr to musíme odstranit, jinak se oba pickery vrství a duplikují.
    input.setAttribute('data-provide', 'flatpickr');
    input.classList.remove('datepicker-input', 'active');

    if (window.jQuery && typeof window.jQuery.fn?.datepicker === 'function') {
        try {
            window.jQuery(input).datepicker('destroy');
        } catch {
            // Není inicializovaný nebo plugin není dostupný pro tento input.
        }
    }

    const wrapper = input.closest('.input-group') ?? input.parentElement;
    wrapper?.querySelectorAll('.datepicker').forEach((el) => el.remove());
};

const cleanupLegacyDatepickers = (root = document) => {
    root.querySelectorAll('[data-datagrid-name] .input-group .datepicker').forEach((el) => el.remove());
};

const clearReadonlyAutofill = (input, dateFormat) => {
    const readonlyPicker = toBool(input.dataset.readonlyPicker ?? 'false');
    if (!readonlyPicker) {
        return;
    }

    const currentValue = (input.value ?? '').trim();
    const defaultValue = (input.defaultValue ?? '').trim();

    // Tvrdá ochrana proti browser restore/autofillu ve US slash formátu.
    // V našem filtru očekáváme tečky, nikoli hodnoty typu 4/10/2026.
    if (currentValue !== '' && !dateFormat.includes('/') && currentValue.includes('/')) {
        input.value = '';
        return;
    }

    // Pokud browser autofill doplnil hodnotu, ale server ji neposlal v HTML atributu value,
    // bereme to jako neplatny default a vycistime.
    if (currentValue !== '' && defaultValue === '') {
        input.value = '';
        return;
    }

    // U readonly pickeru respektujeme jen kanonicky format inputu.
    // Browser restore/autofill casto vraci US format (napr. 4/10/2026), ktery nechceme.
    if (currentValue !== '' && !flatpickr.parseDate(currentValue, dateFormat)) {
        input.value = '';
    }
};

const hideDuringReadonlyInit = (input) => {
    const readonlyPicker = toBool(input.dataset.readonlyPicker ?? 'false');
    if (!readonlyPicker || input.dataset.fpInitHidden === 'true') {
        return;
    }

    input.style.visibility = 'hidden';
    input.dataset.fpInitHidden = 'true';
};

const showAfterReadonlyInit = (input) => {
    if (input.dataset.fpInitHidden !== 'true') {
        return;
    }

    input.style.visibility = '';
    delete input.dataset.fpInitHidden;
};

const submitFilterForm = (form) => {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.classList.contains('ajax')) {
        try {
            naja.makeRequest(
                (form.method || 'GET').toUpperCase(),
                form.action,
                new FormData(form),
                { history: false },
            );
            return;
        } catch {
            // Fallback nize.
        }
    }

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
        return;
    }

    form.submit();
};

const initFlatpickr = (root = document) => {
    root.querySelectorAll('.flatpickr-input:not([data-flatpickr-skip])').forEach((input) => {
        destroyLegacyDatepicker(input);
        hideDuringReadonlyInit(input);

        if (input._flatpickr) {
            input._flatpickr.destroy();
        }

        const language = input.dataset.dateLanguage ?? 'cs';
        const locale = locales[language] ?? language;
        const dateFormat = input.dataset.dateFormat ?? 'Y-m-d';
        const modelInput = input.dataset.modelInputId
            ? document.getElementById(input.dataset.modelInputId)
            : input;
        clearReadonlyAutofill(input, dateFormat);
        if (modelInput && modelInput !== input) {
            clearReadonlyAutofill(modelInput, dateFormat);
        }
        const minuteIncrement = Number.parseInt(input.dataset.minuteIncrement ?? '5', 10);
        const mode = input.dataset.mode ?? 'single';
        const submitOnClose = toBool(input.dataset.submitOnClose ?? 'false');
        const readonlyPicker = toBool(input.dataset.readonlyPicker ?? 'false');
        const rangeToInput = input.dataset.rangeToId ? document.getElementById(input.dataset.rangeToId) : null;
        if (rangeToInput) {
            hideDuringReadonlyInit(rangeToInput);
        }
        const baseValue = (modelInput?.value ?? input.value);
        const parsedFrom = parseDateWithFallback(baseValue, dateFormat);
        const parsedTo = parseDateWithFallback(rangeToInput?.value ?? '', dateFormat);
        const defaultDate = parsedFrom
            ? (mode === 'range' ? [parsedFrom, parsedTo ?? parsedFrom] : parsedFrom)
            : undefined;
        const initialFrom = parsedFrom ? flatpickr.formatDate(parsedFrom, dateFormat) : '';
        const initialTo = parsedTo ? flatpickr.formatDate(parsedTo, dateFormat) : '';
        let lastChangedFrom = initialFrom;
        let lastChangedTo = initialTo;
        let valueSelectedDuringOpen = false;
        let valueOnOpen = initialFrom;
        let modelValueOnOpen = (modelInput?.value ?? '').trim();

        const applyCanonicalRangeValues = () => {
            if (mode !== 'range') {
                return;
            }

            input.value = lastChangedFrom;
            if (rangeToInput) {
                rangeToInput.value = lastChangedTo;
            }
        };

        if (mode === 'range') {
            // Datagrid autosubmit plugin reaguje na change; u range chceme submit az po zavreni pickeru.
            input.removeAttribute('data-autosubmit-change');
            rangeToInput?.removeAttribute('data-autosubmit-change');

            if (input.dataset.rangeStopChangeAttached !== 'true') {
                const stopAutosubmit = (event) => {
                    event.stopImmediatePropagation();
                };

                input.addEventListener('change', stopAutosubmit, true);
                rangeToInput?.addEventListener('change', stopAutosubmit, true);
                input.dataset.rangeStopChangeAttached = 'true';
            }
        }

        const defaultHourRaw = Number.parseInt(input.dataset.defaultHour ?? '12', 10);
        const defaultMinuteRaw = Number.parseInt(input.dataset.defaultMinute ?? '0', 10);
        const defaultHour = Number.isNaN(defaultHourRaw) ? 12 : defaultHourRaw;
        const defaultMinute = Number.isNaN(defaultMinuteRaw) ? 0 : defaultMinuteRaw;

        flatpickr(input, {
            allowInput: !readonlyPicker,
            inline: false,
            locale,
            dateFormat,
            mode,
            defaultDate,
            enableTime: toBool(input.dataset.enableTime ?? 'false'),
            enableSeconds: toBool(input.dataset.enableSeconds ?? 'false'),
            time_24hr: toBool(input.dataset.time_24hr ?? 'false'),
            autoFillDefaultTime: false,
            minuteIncrement: Number.isNaN(minuteIncrement) ? 5 : minuteIncrement,
            defaultHour,
            defaultMinute,
            onOpen: () => {
                valueSelectedDuringOpen = false;
                valueOnOpen = (input.value ?? '').trim();
                modelValueOnOpen = (modelInput?.value ?? '').trim();
            },
            onChange: (selectedDates, _dateStr, instance) => {
                valueSelectedDuringOpen = true;

                if (mode !== 'range' && modelInput && modelInput !== input) {
                    const first = selectedDates[0] ?? null;
                    const value = first ? instance.formatDate(first, dateFormat) : '';
                    input.value = value;
                    modelInput.value = value;
                    modelInput.dispatchEvent(new Event('change', { bubbles: true }));
                    updateClearButtonVisibility(input);
                    return;
                }

                if (mode !== 'range' || !rangeToInput) {
                    return;
                }

                const first = selectedDates[0] ?? null;
                const second = selectedDates[1] ?? null;

                lastChangedFrom = first ? instance.formatDate(first, dateFormat) : '';
                lastChangedTo = second ? instance.formatDate(second, dateFormat) : '';

                rangeToInput.value = lastChangedTo;
                input.value = lastChangedFrom;
                updateClearButtonVisibility(input);
                updateClearButtonVisibility(rangeToInput);
            },
            onClose: () => {
                // Pokud uzivatel nic nevybral, obnov puvodni hodnotu.
                // Flatpickr muze dopsat vlastni hodnotu i po onClose, proto fix i v dalsim ticku.
                if (readonlyPicker && !valueSelectedDuringOpen) {
                    const restore = () => {
                        input.value = valueOnOpen;
                        if (modelInput && modelInput !== input) {
                            modelInput.value = modelValueOnOpen;
                        }
                    };
                    restore();
                    setTimeout(restore, 0);
                    return;
                }

                if (mode !== 'range' || !submitOnClose) {
                    return;
                }

                // Ber posledni korektni hodnoty z onChange; pri close muze flatpickr fallbacknout na "today".
                applyCanonicalRangeValues();
                // Flatpickr muze po onClose jeste dopsat vlastni hodnotu do inputu, proto fix i v dalsim ticku.
                setTimeout(applyCanonicalRangeValues, 0);

                const currentFrom = (lastChangedFrom ?? '').trim();
                const currentTo = (lastChangedTo ?? '').trim();

                if (currentFrom === initialFrom && currentTo === initialTo) {
                    return;
                }

                const form = input.closest('form');
                submitFilterForm(form);
            },
            onValueUpdate: () => {
                if (mode === 'range') {
                    applyCanonicalRangeValues();
                }
            },
        });

        // Udrzuj hodnoty v jednotnem formatu, aby se po reopen zobrazil stejny rozsah.
        if (mode === 'range') {
            if (parsedFrom) {
                input.value = flatpickr.formatDate(parsedFrom, dateFormat);
            }

            if (rangeToInput && parsedTo) {
                rangeToInput.value = flatpickr.formatDate(parsedTo, dateFormat);
            }

            lastChangedFrom = (input.value ?? '').trim();
            lastChangedTo = (rangeToInput?.value ?? '').trim();
        } else if (modelInput && modelInput !== input) {
            const normalized = parsedFrom ? flatpickr.formatDate(parsedFrom, dateFormat) : '';
            input.value = normalized;
            modelInput.value = normalized;
        }

        requestAnimationFrame(() => {
            showAfterReadonlyInit(input);
            if (rangeToInput) {
                showAfterReadonlyInit(rangeToInput);
            }
            updateClearButtonVisibility(input);
            if (rangeToInput) {
                updateClearButtonVisibility(rangeToInput);
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    cleanupLegacyDatepickers();
    initFlatpickr();
    initClearButtons();
});

naja.addEventListener('complete', () => {
    cleanupLegacyDatepickers();
    initFlatpickr();
    initClearButtons();
});

const updateClearButtonVisibility = (displayInput) => {
    if (!displayInput?.id) {
        return;
    }

    const btn = document.querySelector(`[data-flatpickr-clear="${displayInput.id}"]`);
    if (!btn) {
        return;
    }

    const hasValue = (displayInput.value ?? '').trim() !== '';
    btn.style.display = hasValue ? '' : 'none';
};

const initClearButtons = (root = document) => {
    root.querySelectorAll('[data-flatpickr-clear]').forEach((btn) => {
        if (btn.dataset.fpClearAttached === 'true') {
            return;
        }

        btn.dataset.fpClearAttached = 'true';
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const displayInputId = btn.dataset.flatpickrClear;
            const displayInput = displayInputId ? document.getElementById(displayInputId) : null;
            if (!displayInput) {
                return;
            }

            const fp = displayInput._flatpickr;
            if (fp) {
                fp.clear();
            } else {
                displayInput.value = '';
            }

            const modelInputId = displayInput.dataset.modelInputId;
            const modelInput = modelInputId ? document.getElementById(modelInputId) : null;
            if (modelInput) {
                modelInput.value = '';
                modelInput.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                displayInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            updateClearButtonVisibility(displayInput);

            const form = displayInput.closest('form');
            submitFilterForm(form);
        });
    });
};

['focusin', 'click'].forEach((eventName) => {
    document.addEventListener(eventName, (event) => {
        const input = event.target instanceof HTMLElement
            ? event.target.closest('.flatpickr-input:not([data-flatpickr-skip])')
            : null;

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        destroyLegacyDatepicker(input);
        cleanupLegacyDatepickers(input.closest('[data-datagrid-name]') ?? document);
    }, true);
});
