document.addEventListener('DOMContentLoaded', () => {
    const avatarInput = document.getElementById('avatar-input');
    const profileAvatar = document.getElementById('profile-avatar');
    const changeBtn = document.getElementById('change-avatar-btn');
    const avatarForm = document.getElementById('avatar-form');

    // Abrir el explorador de archivos
    changeBtn.addEventListener('click', () => {
        avatarInput.click();
    });

    // Previsualizar y subir imagen
    avatarInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('Por favor selecciona un archivo de imagen.');
            return;
        }

        // Previsualizar instantáneamente
        const reader = new FileReader();
        reader.onload = (e) => {
            profileAvatar.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Subir automáticamente
        const formData = new FormData(avatarForm);
        fetch('upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            console.log('Respuesta del servidor:', data);
        })
        .catch(err => console.error('Error al subir:', err));
    });
});