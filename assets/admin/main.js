import './main.scss';

import netteForms from 'nette-forms';
import './js/nette-bootstrap.js';
netteForms.initOnLoad();

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import 'admin-lte';

import '../../vendor/ublaboo/datagrid/assets/datagrid-full.ts';
import './js/datagrid-spinner.js';
import './js/datagrid-flatpickr.js';

import './js/toastr-shim.js';
import './js/confirm.js';
