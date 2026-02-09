<?php
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (loginUser($username, $password)) {
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
        
        <div style="text-align: center; margin: 1rem 0;">
            <p style="opacity: 0.7;">O inicia sesión con</p>
            <button class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem;">
                <span>Gmail</span>
            </button>
        </div>
        
        <p style="text-align: center;">
            ¿No tienes cuenta? <a href="register.php" style="color: var(--electric-blue);">Regístrate</a>
        </p>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>