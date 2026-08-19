/**
 * Inicializace Naji (Nette AJAX) — musí běžet na každé admin stránce, ne
 * jen na těch s datagridem. Naja.initialize() lze zavolat jen jednou
 * (podruhé vyhazuje výjimku), proto je vytažená sem a datagrid-init.js
 * (lazy-loaded, jen když je na stránce grid) už jen znovu použije tuhle
 * instanci přes `naja` import, bez dalšího initialize().
 */
import naja from 'naja';
import netteForms from 'nette-forms';

naja.defaultOptions.history = false;
naja.formsHandler.netteForms = netteForms;
naja.initialize();

window.naja = naja;

export default naja;
