/**
 * Minimal toastr shim using Bootstrap 5 Toasts.
 * Implements toastr.success/error/info/warning interface.
 */

const TYPES = {
    success: { bg: 'text-bg-success', icon: 'bi-check-circle-fill' },
    error:   { bg: 'text-bg-danger',  icon: 'bi-x-circle-fill' },
    warning: { bg: 'text-bg-warning', icon: 'bi-exclamation-triangle-fill' },
    info:    { bg: 'text-bg-info',    icon: 'bi-info-circle-fill' },
};

function getContainer() {
    let el = document.getElementById('toastr-container');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toastr-container';
        el.className = 'toast-container position-fixed top-0 end-0 p-3';
        el.style.zIndex = '1090';
        document.body.appendChild(el);
    }
    return el;
}

function show(type, message) {
    const { bg, icon } = TYPES[type] ?? TYPES.info;
    const container = getContainer();

    const el = document.createElement('div');
    el.className = `toast align-items-center border-0 ${bg}`;
    el.setAttribute('role', 'alert');
    el.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi ${icon}"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;

    container.appendChild(el);

    const toast = new window.bootstrap.Toast(el, { delay: 5000 });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}

window.toastr = {
    success: (msg) => show('success', msg),
    error:   (msg) => show('error', msg),
    warning: (msg) => show('warning', msg),
    info:    (msg) => show('info', msg),
};
