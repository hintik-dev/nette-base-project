import './main.scss';

import netteForms from 'nette-forms';
import './js/nette-bootstrap.js';
netteForms.initOnLoad();

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import 'admin-lte';

import './js/naja-init.js';

import './js/toastr-shim.js';
import './js/confirm.js';
import './js/theme-preview.js';

// Datagrid (ublaboo) je nejtěžší závislost tohoto bundlu (Sortable, TomSelect,
// VanillaDatepicker, ...), ale ne každá admin stránka grid má — načte se
// přes dynamický import() jen tam, kde v DOM reálně existuje.
if (document.querySelector('[data-datagrid-name]')) {
    import('./js/datagrid-init.js');
}
