<?php
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar registro
    $userData = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'country' => $_POST['country'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ];
    
    $result = registerUser($userData);
    
    if ($result['success']) {
        // Iniciar sesión automáticamente
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['username'] = $result['username'] ?? '';
        $_SESSION['profile_completed'] = 0;
        logAction('REGISTER_SUCCESS', 'Nuevo usuario registrado');
        header('Location: complete_profile.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sonder</title>
    <link rel="icon" href="logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
    <style>
        #country-select {
            background-color: white !important;
            color: #1a1a2e !important;
        }
        
        #country-select option {
            background-color: white;
            color: #1a1a2e;
            padding: 5px;
        }
        
        #country-select option:hover {
            background-color: var(--electric-blue);
            color: white;
        }
        
        .form-help {
            font-size: 0.85rem;
            opacity: 0.7;
            margin-top: 0.3rem;
            display: block;
        }
        
        .field-error {
            border-color: #ff6b6b !important;
        }
        
        .error-message {
            color: #ff6b6b;
            margin-bottom: 1rem;
            text-align: center;
            padding: 0.8rem;
            background-color: rgba(255, 107, 107, 0.1);
            border-radius: 4px;
        }
    </style>
</head>
<body class="welcome-page">
    <div class="stars"></div>
    <div class="form-container">
        <div class="form-logo-container">
            <img src="logo.svg" alt="Sonder" class="form-logo">
            <h1 class="form-title">Sonder</h1>
        </div>
        
        <h2 style="text-align: center; color: var(--stardust); margin-bottom: 1.5rem; font-size: 1.2rem; opacity: 0.8;">Crear Cuenta</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="register-form" novalidate>
            <div class="form-group">
                <label class="form-label">Nombre completo</label>
                <input type="text" name="full_name" class="form-input" required minlength="3" maxlength="100">
                <span class="form-help">Tu nombre completo (3-100 caracteres)</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-input" required>
                <span class="form-help">Debe ser un correo válido y único</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date" name="birth_date" id="birth_date" class="form-input" required>
                <span class="form-help">Debes ser mayor de 18 años</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">País</label>
                <select id="country-select" name="country" class="form-select" required>
                    <option value="">Selecciona tu país</option>
                </select>
                <span class="form-help">Selecciona el país donde resides</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Número de teléfono</label>
                <input type="tel" name="phone" class="form-input" required pattern="[0-9\-\+\(\)\s]{7,20}">
                <span class="form-help">Formato válido: +1-234-567-8900</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-input" required minlength="8">
                <span class="form-help">Mínimo 8 caracteres con números y caracteres especiales</span>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Registrarse</button>
        </form>
        
        <?php if (GOOGLE_AUTH_ENABLED): ?>
        <div style="text-align: center; margin: 1.5rem 0;">
            <p style="opacity: 0.7; margin-bottom: 0.8rem;">O regístrate con</p>
            <div id="g_id_onload"
                 data-client_id="<?php echo htmlspecialchars(GOOGLE_CLIENT_ID, ENT_QUOTES, 'UTF-8'); ?>"
                 data-callback="handleCredentialResponse">
            </div>
            <div class="g_id_signin" data-type="standard" data-size="large" data-theme="dark" data-text="signup" data-shape="rectangular" data-logo_alignment="left" style="display: flex; justify-content: center;"></div>
        </div>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 1rem;">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--electric-blue);">Inicia sesión</a>
        </p>
    </div>
    
    <?php if (GOOGLE_AUTH_ENABLED): ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <?php endif; ?>
    <script src="js/countries.js"></script>
    <script src="js/script.js"></script>
    <script>
        function handleCredentialResponse(response) {
            // El token JWT de Google
            const token = response.credential;
            
            // Enviar el token al servidor PHP
            fetch('google_auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'token=' + encodeURIComponent(token)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                } else {
                    // Mostrar error
                    alert(data.message || 'Error en la autenticación con Google');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error en la autenticación con Google');
            });
        }

        // Configurar Google Sign-In
        window.onload = function () {
            if (typeof google !== 'undefined') {
                google.accounts.id.initialize({
                    client_id: '<?php echo htmlspecialchars(GOOGLE_CLIENT_ID, ENT_QUOTES, 'UTF-8'); ?>',
                    callback: handleCredentialResponse
                });
                google.accounts.id.renderButton(
                    document.querySelector('.g_id_signin'),
                    { theme: 'outline', size: 'large' }
                );
            }
        
        const countriesList = window.SONDER_COUNTRIES || [];

        function initRegisterForm() {
            const countrySelect = document.getElementById('country-select');
            const form = document.getElementById('register-form');
            const birthDateInput = document.getElementById('birth_date');

            if (!countrySelect || !form || !birthDateInput) {
                return;
            }

            countrySelect.innerHTML = '<option value="">Selecciona tu país</option>';

            countriesList.sort().forEach(country => {
                const option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                countrySelect.appendChild(option);
            });

            birthDateInput.addEventListener('change', function() {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                if (age < 18) {
                    this.classList.add('field-error');
                    this.setCustomValidity('Debes ser mayor de 18 años');
                } else {
                    this.classList.remove('field-error');
                    this.setCustomValidity('');
                }
            });

            form.addEventListener('submit', function(e) {
                const birthDate = new Date(birthDateInput.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                if (age < 18) {
                    e.preventDefault();
                    birthDateInput.classList.add('field-error');
                    alert('Debes ser mayor de 18 años para registrarte en Sonder.');
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRegisterForm);
        } else {
            initRegisterForm();
        }
    };
    </script>
</body>
</html>
