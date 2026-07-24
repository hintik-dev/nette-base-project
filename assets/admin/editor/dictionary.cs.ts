/**
 * Český překlad vestavěných textů Puck editoru (tlačítka, tooltipy, panely...).
 * `dictionary` prop existuje až od 0.23.0-canary (viz package.json — vydaná
 * 0.22.2 tuto možnost nemá vůbec). Klíče ověřeny přímo v nainstalovaném
 * balíčku (node_modules/@puckeditor/core/dist/no-external.js, `defaultDictionary`),
 * ne jen podle veřejné dokumentace, která popisuje odlišný (novější) stav.
 * Názvy bloků a polí se překládají zvlášť přes `label` v config.ts / blocks/*.tsx.
 */
export const csDictionary: Record<string, string> = {
    'header-publish': 'Publikovat',
    'header-undo': 'Zpět',
    'header-redo': 'Znovu',
    'header-toggle-leftsidebar': 'Přepnout levý panel',
    'header-toggle-rightsidebar': 'Přepnout pravý panel',
    'header-toggle-menubar': 'Přepnout panel nabídky',

    'action-selectparent': 'Vybrat nadřazený prvek',
    'action-duplicate': 'Duplikovat',
    'action-delete': 'Smazat',

    'label-page': 'Stránka',
    'label-component': 'Komponenta',

    'outline-empty': 'Žádné položky',
    'outline-collapse': 'Sbalit',
    'outline-expand': 'Rozbalit',

    'drawer-category-collapse': 'Sbalit {title}',
    'drawer-category-expand': 'Rozbalit {title}',
    'drawer-category-other': 'Ostatní',

    'canvas-noconfig': 'Chybí konfigurace pro {type}',

    'field-readonly': 'Pouze pro čtení',
    'field-arrayitem-summary': 'Položka č. {index}',
    'field-arrayitem-duplicate': 'Duplikovat',
    'field-arrayitem-delete': 'Smazat',
    'field-external-selectdata': 'Vybrat data',
    'field-external-search': 'Hledat',
    'field-external-togglefilters': 'Přepnout filtry',
    'field-external-item': 'Externí položka',
    'field-external-result-singular': '{count} výsledek',
    'field-external-result-plural': '{count} výsledků',

    'field-richtext-bold': 'Tučné',
    'field-richtext-italic': 'Kurzíva',
    'field-richtext-underline': 'Podtržené',
    'field-richtext-strikethrough': 'Přeškrtnuté',
    'field-richtext-blockquote': 'Citace',
    'field-richtext-code-inline': 'Vložený kód',
    'field-richtext-code-block': 'Blok kódu',
    'field-richtext-list-bullet': 'Odrážkový seznam',
    'field-richtext-list-ordered': 'Číslovaný seznam',
    'field-richtext-horizontalrule': 'Vodorovná čára',
    'field-richtext-align-left': 'Zarovnat vlevo',
    'field-richtext-align-center': 'Zarovnat na střed',
    'field-richtext-align-right': 'Zarovnat vpravo',
    'field-richtext-align-justify': 'Zarovnat do bloku',
    'field-richtext-select': 'Vybrat',
    'field-richtext-headingselect-1': 'Nadpis 1',
    'field-richtext-headingselect-2': 'Nadpis 2',
    'field-richtext-headingselect-3': 'Nadpis 3',
    'field-richtext-headingselect-4': 'Nadpis 4',
    'field-richtext-headingselect-5': 'Nadpis 5',
    'field-richtext-headingselect-6': 'Nadpis 6',
    'field-richtext-alignselect-left': 'Vlevo',
    'field-richtext-alignselect-center': 'Na střed',
    'field-richtext-alignselect-right': 'Vpravo',
    'field-richtext-alignselect-justify': 'Do bloku',
    'field-richtext-listselect-bullet': 'Odrážkový seznam',
    'field-richtext-listselect-ordered': 'Číslovaný seznam',

    'viewport-zoom-in': 'Přiblížit náhled',
    'viewport-zoom-out': 'Oddálit náhled',
    'viewport-zoom-auto': '{zoom}% (automaticky)',
    'viewport-toggle-menu': 'Přepnout nabídku náhledu',
    'viewport-switch': 'Přepnout na náhled {label}',
    'viewport-switch-default': 'Přepnout náhled',

    'plugin-blocks': 'Bloky',
    'plugin-outline': 'Osnova',
    'plugin-fields': 'Pole',
    'plugin-components': 'Komponenty',

    'layout-maximize': 'maximalizovat',
    'layout-minimize': 'minimalizovat',

    'loader-loading': 'Načítání',
};
