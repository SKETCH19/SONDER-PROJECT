document.addEventListener('DOMContentLoaded', () => {
    const countriesList = window.SONDER_COUNTRIES || [];
    const countrySelect = document.getElementById('country-select');
    const form = document.getElementById('register-form');
    const birthDateInput = document.getElementById('birth_date');

    if (!countrySelect || !form || !birthDateInput) {
        return;
    }

    countrySelect.innerHTML = '<option value="">Selecciona tu pais</option>';

    countriesList.sort().forEach((country) => {
        const option = document.createElement('option');
        option.value = country;
        option.textContent = country;
        countrySelect.appendChild(option);
    });

    birthDateInput.addEventListener('change', () => {
        const birthDate = new Date(birthDateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 18) {
            birthDateInput.classList.add('field-error');
            birthDateInput.setCustomValidity('Debes ser mayor de 18 anos');
        } else {
            birthDateInput.classList.remove('field-error');
            birthDateInput.setCustomValidity('');
        }
    });

    form.addEventListener('submit', (event) => {
        const birthDate = new Date(birthDateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 18) {
            event.preventDefault();
            birthDateInput.classList.add('field-error');
            alert('Debes ser mayor de 18 anos para registrarte en Sonder.');
        }
    });
});
