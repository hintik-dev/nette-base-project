import naja from 'naja';

naja.uiHandler.addEventListener('interaction', (event) => {
    const el = event.detail.element;
    const grid = el?.closest('[data-datagrid-name]');
    if (!grid) return;

    const overlay = document.createElement('div');
    overlay.className = 'datagrid-spinner-overlay';
    overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Načítám…</span></div>';
    grid.appendChild(overlay);
});

naja.addEventListener('complete', () => {
    document.querySelectorAll('.datagrid-spinner-overlay').forEach(el => el.remove());
});

