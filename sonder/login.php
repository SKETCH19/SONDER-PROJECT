<?php
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (loginUser($username, $password)) {
        if (!isProfileComplete()) {
            header('Location: complete_profile.php');
            exit;
        }
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (GOOGLE_AUTH_ENABLED): ?>
    <meta name="google-signin-client_id" content="<?php echo htmlspecialchars(GOOGLE_CLIENT_ID, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <title>Iniciar Sesión - Sonder</title>
    <link rel="icon" href="logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="welcome-page">
    <div class="stars"></div>
    <div class="form-container">
        <div class="form-logo-container">
            <img src="logo.svg" alt="Sonder" class="form-logo">
            <h1 class="form-title">Sonder</h1>
        </div>
        
        <h2 style="text-align: center; color: var(--stardust); margin-bottom: 1.5rem; font-size: 1.2rem; opacity: 0.8;">Iniciar Sesión</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message" style="color: #ff6b6b; margin-bottom: 1rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Usuario o correo electrónico</label>
                <input type="text" name="username" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Iniciar Sesión</button>
        </form>
        
        <?php if (GOOGLE_AUTH_ENABLED): ?>
        <div style="text-align: center; margin: 1.5rem 0;">
            <p style="opacity: 0.7; margin-bottom: 0.8rem;">O inicia sesión con</p>
            <div id="g_id_onload"
                 data-client_id="<?php echo htmlspecialchars(GOOGLE_CLIENT_ID, ENT_QUOTES, 'UTF-8'); ?>"
                 data-callback="handleCredentialResponse">
            </div>
            <div class="g_id_signin" data-type="standard" data-size="large" data-theme="dark" data-text="signin" data-shape="rectangular" data-logo_alignment="left" style="display: flex; justify-content: center;"></div>
        </div>
        <?php endif; ?>
        
        <p style="text-align: center;">
            ¿No tienes cuenta? <a href="register.php" style="color: var(--electric-blue);">Regístrate</a>
        </p>
    </div>
    
    <?php if (GOOGLE_AUTH_ENABLED): ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <?php endif; ?>
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
        };
    </script>
    <script src="js/script.js"></script>