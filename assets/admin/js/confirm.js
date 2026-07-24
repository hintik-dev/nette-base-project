/**
 * Potvrzovací modal pro akce s data-confirm atributem.
 * Sdílí modal s DataGrid ConfirmPlugin (datagridConfirmModal).
 */
document.addEventListener('click', function (e) {
	const el = e.target.closest('[data-confirm]');
	if (!el) return;

	const message = el.getAttribute('data-confirm');
	if (!message) return;

	const modal = document.getElementById('datagridConfirmModal');
	if (!modal) {
		if (!window.confirm(message)) e.preventDefault();
		return;
	}

	e.preventDefault();

	const messageBox = document.getElementById('datagridConfirmMessage');
	const confirmButton = document.getElementById('datagridConfirmOk');
	if (!messageBox || !confirmButton) return;

	messageBox.textContent = message;

	const newButton = confirmButton.cloneNode(true);
	confirmButton.parentNode.replaceChild(newButton, confirmButton);

	newButton.addEventListener('click', () => {
		window.bootstrap.Modal.getInstance(modal)?.hide();

		el.removeAttribute('data-confirm');
		if (el.classList.contains('ajax')) {
			window.naja?.makeRequest('GET', el.href, null, { history: false });
		} else {
			el.click();
		}
		el.setAttribute('data-confirm', message);
	}, { once: true });

	const modalInstance = window.bootstrap.Modal.getInstance(modal) || new window.bootstrap.Modal(modal);
	modalInstance.show();
}, true);
