import netteForms from 'nette-forms';

netteForms.showFormErrors = function (form, errors) {
    // Smazání předchozích chyb
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

    errors.forEach(({ element, message }) => {
        element.classList.add('is-invalid');

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;

        // Vložení dovnitř .input-group (kvůli Bootstrap CSS selektorům)
        const inputGroup = element.closest('.input-group');
        if (inputGroup) {
            inputGroup.appendChild(feedback);
        } else {
            element.insertAdjacentElement('afterend', feedback);
        }
    });

    if (errors.length > 0) {
        errors[0].element.focus();
    }
};
