import './main.scss';

// Initialize Nette Forms on page load
import netteForms from 'nette-forms';

netteForms.initOnLoad();

// Bootstrap JS (navbar toggler, dropdowny, ...)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
