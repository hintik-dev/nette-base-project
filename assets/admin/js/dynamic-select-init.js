/**
 * Inicializace TomSelect nad [data-dynamic-select] (viz
 * App\Presentation\Control\Form\DynamicSelect\DynamicSelect/DynamicMultiSelect).
 * Bez data-dynamic-select-source jde o čistě lokální vyhledávání v už
 * vykreslených <option>; s atributem se zbytek nabídky dotahuje z
 * DynamicSelectPresenter podle napsaného textu. multiple se pozná nativně
 * z HTML atributu <select multiple> — dostane tak vzhled uzavřeného
 * dropdownu s odebiratelnými štítky, ne nativní listbox.
 */
import TomSelect from 'tom-select';
import naja from 'naja';

const buildRemoteConfig = (select) => {
    const url = select.dataset.dynamicSelectSource;
    const minChars = Number.parseInt(select.dataset.dynamicSelectMinChars ?? '2', 10);

    return {
        shouldLoad: (query) => query.length >= minChars,
        load(query, callback) {
            const params = new URLSearchParams({ q: query });

            fetch(`${url}${url.includes('?') ? '&' : '?'}${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((response) => (response.ok ? response.json() : { items: [] }))
                .then((data) => callback((data.items ?? []).map((item) => ({ value: item.value, text: item.label }))))
                .catch(() => callback());
        },
    };
};

const defaultPlaceholder = (isRemote) => (isRemote ? 'Napište pro vyhledávání…' : 'Vyberte nebo vyhledejte…');

export const initDynamicSelects = (root = document) => {
    root.querySelectorAll('[data-dynamic-select]:not([data-dynamic-select-ready])').forEach((select) => {
        select.setAttribute('data-dynamic-select-ready', '1');

        const isRemote = Boolean(select.dataset.dynamicSelectSource);

        new TomSelect(select, {
            plugins: select.multiple ? ['remove_button'] : [],
            maxOptions: isRemote ? undefined : null,
            placeholder: select.dataset.dynamicSelectPlaceholder ?? defaultPlaceholder(isRemote),
            render: {
                no_results: () => '<div class="no-results">Nic nenalezeno</div>',
            },
            ...(isRemote ? buildRemoteConfig(select) : {}),
        });
    });
};

// Modul se importuje dynamickým import() až v okamžiku, kdy [data-dynamic-select]
// v DOM už reálně existuje (viz main.js) — DOM je tedy hotové bez ohledu na to,
// že DOMContentLoaded už mohl proběhnout dřív, než se tenhle chunk stihl načíst
// a vyhodnotit. Proto se spouští rovnou, ne až na DOMContentLoaded (na rozdíl
// od toho ale ajaxem dotažený obsah teprve přijde, tam naja 'complete' zůstává).
initDynamicSelects();
naja.addEventListener('complete', () => initDynamicSelects());
