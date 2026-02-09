<?php
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar registro
    $userData = [
        'full_name' => $_POST['full_name'] ?? '',
        'username' => $_POST['username'] ?? '',
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
        $_SESSION['username'] = $userData['username'];
        logAction('REGISTER_SUCCESS', 'Nuevo usuario registrado');
        header('Location: dashboard.php');
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
                <label class="form-label">Nombre de usuario</label>
                <input type="text" name="username" class="form-input" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+">
                <span class="form-help">3-20 caracteres (letras, números, guión bajo)</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-input" required minlength="8">
                <span class="form-help">Mínimo 8 caracteres con números y caracteres especiales</span>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Registrarse</button>
        </form>
        
        <p style="text-align: center; margin-top: 1rem;">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--electric-blue);">Inicia sesión</a>
        </p>
    </div>
    
    <script src="js/script.js"></script>
    <script>
        // Lista completa de países del mundo
        const countriesList = [
            'Afganistán', 'Albania', 'Alemania', 'Andorra', 'Angola', 'Anguila', 'Antártida', 'Antigua y Barbuda',
            'Arabia Saudí', 'Argelia', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaiyán',
            'Bahamas', 'Bahrein', 'Bangladesh', 'Barbados', 'Bélgica', 'Belice', 'Benin', 'Bermudas', 'Bielorrusia',
            'Birmania', 'Bolivia', 'Bosnia y Herzegovina', 'Botsuana', 'Brasil', 'Brunei', 'Bulgaria', 'Burkina Faso',
            'Burundi', 'Bután', 'Cabo Verde', 'Camboya', 'Camerún', 'Canadá', 'Catar', 'Chad', 'Chile', 'China',
            'Chipre', 'Ciudad del Vaticano', 'Colombia', 'Comoras', 'Congo', 'Corea del Norte', 'Corea del Sur',
            'Costa de Marfil', 'Costa Rica', 'Croacia', 'Cuba', 'Curazao', 'Dinamarca', 'Dominica', 'Djibutí',
            'Ecuador', 'Egipto', 'El Salvador', 'Emiratos Árabes Unidos', 'España', 'Estados Unidos', 'Estonia',
            'Etiopía', 'Filipinas', 'Finlandia', 'Fiyi', 'Francia', 'Gabón', 'Gambia', 'Gana', 'Georgia',
            'Gibraltar', 'Grecia', 'Groenlandia', 'Guadalupe', 'Guam', 'Guatemala', 'Guayana Francesa', 'Guyana',
            'Haití', 'Holanda', 'Honduras', 'Hong Kong', 'Hungría', 'India', 'Indonesia', 'Irak', 'Irán',
            'Irlanda', 'Irlanda del Norte', 'Isla de Man', 'Islandia', 'Islas Åland', 'Islas Ascensión',
            'Islas Caimán', 'Islas Canarias', 'Islas Cocos', 'Islas Cook', 'Islas Feroe', 'Islas Hébridas',
            'Islas Malvinas', 'Islas Marianas', 'Islas Norfolk', 'Islas Palau', 'Islas Pitcairn', 'Islas Salomón',
            'Islas Seychelles', 'Islas Turks y Caicos', 'Islas Vírgenes Británicas', 'Islas Vírgenes de EE. UU.',
            'Italia', 'Jamaica', 'Japón', 'Jersey', 'Jordania', 'Kazajistán', 'Kenia', 'Kirguistán', 'Kiribati',
            'Kuwait', 'Laos', 'Lesoto', 'Letonia', 'Líbano', 'Liberia', 'Libia', 'Liechtenstein', 'Lituania',
            'Luxemburgo', 'Macao', 'Macedonia del Norte', 'Madagascar', 'Malasia', 'Malawi', 'Maldivas', 'Mali',
            'Malta', 'Marruecos', 'Martinica', 'Mauricio', 'Mauritania', 'Mayotte', 'México', 'Micronesia',
            'Moldavia', 'Mónaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Mozambique', 'Namibia', 'Nauru',
            'Nepal', 'Nicaragua', 'Níger', 'Nigeria', 'Niue', 'Noruega', 'Nueva Caledonia', 'Nueva Zelanda',
            'Omán', 'Países Bajos', 'Panamá', 'Papúa Nueva Guinea', 'Paquistán', 'Paraguay', 'Perú',
            'Polinesia Francesa', 'Polonia', 'Puerto Rico', 'Qatar', 'República Centroafricana', 'República Checa',
            'República Democrática del Congo', 'República Dominicana', 'Reunión', 'Ruanda', 'Rumania', 'Rusia',
            'Saba', 'Sahara Occidental', 'Samoa', 'Samoa Americana', 'San Bartolomé', 'San Cristóbal y Nieves',
            'San Eustaquio', 'San Marino', 'San Martín', 'San Pedro y Miquelón', 'San Vicente y las Granadinas',
            'Santa Elena', 'Santa Lucía', 'Santo Tomé y Príncipe', 'Senegal', 'Serbia', 'Seychelles',
            'Sierra Leona', 'Singapur', 'Sint Maarten', 'Siria', 'Somalia', 'Sri Lanka', 'Suazilandia',
            'Sudáfrica', 'Sudán', 'Sudán del Sur', 'Suecia', 'Suiza', 'Surinam', 'Svalbard y Jan Mayen',
            'Tailandia', 'Taiwán', 'Tanzania', 'Tayikistán', 'Territorio Británico del Océano Índico',
            'Terranova y Labrador', 'Territorios Franceses del Sur', 'Timor Oriental', 'Togo', 'Tokelau',
            'Tonga', 'Trinidad y Tobago', 'Túnez', 'Turkmenistán', 'Turquía', 'Turks y Caicos', 'Tuvalu',
            'Ucrania', 'Uganda', 'Uruguay', 'Uzbekistán', 'Vanuatu', 'Venezuela', 'Vietnam', 'Wallis y Futuna',
            'Yemen', 'Zambia', 'Zimbabue'
        ];

        // Cargar países cuando se cargue la página
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country-select');
            const form = document.getElementById('register-form');
            const birthDateInput = document.getElementById('birth_date');
            
            // Limpiar la opción de carga
            countrySelect.innerHTML = '<option value="">Selecciona tu país</option>';
            
            // Agregar todos los países ordenados alfabéticamente
            countriesList.sort().forEach(country => {
                const option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                countrySelect.appendChild(option);
            });
            
            // Validar edad mínima
            birthDateInput.addEventListener('change', function() {
                const birthDate = new Date(this.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();
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
            
            // Validar edad al enviar formulario
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
        });
    </script>
</body>
</html>

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
            <div class="error-message" style="color: #ff6b6b; margin-bottom: 1rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="register-form">
            <div class="form-group">
                <label class="form-label">Nombre completo</label>
                <input type="text" name="full_name" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date" name="birth_date" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">País</label>
                <select id="country-select" name="country" class="form-select" required>
                    <option value="">Cargando países...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Número de teléfono</label>
                <input type="tel" name="phone" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nombre de usuario</label>
                <input type="text" name="username" class="form-input" required>
                <small style="opacity: 0.7;">Este será tu identificador único en Sonder</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-input" required>
                <small style="opacity: 0.7;">Mínimo 8 caracteres con números y símbolos</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Registrarse</button>
        </form>
        
        <p style="text-align: center; margin-top: 1rem;">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--electric-blue);">Inicia sesión</a>
        </p>
    </div>
    
    <script src="js/script.js"></script>
    <script>
        // Lista completa de países del mundo
        const countriesList = [
            'Afganistán', 'Albania', 'Alemania', 'Andorra', 'Angola', 'Anguila', 'Antártida', 'Antigua y Barbuda',
            'Arabia Saudí', 'Argelia', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaiyán',
            'Bahamas', 'Bahrein', 'Bangladesh', 'Barbados', 'Bélgica', 'Belice', 'Benin', 'Bermudas', 'Bielorrusia',
            'Birmania', 'Bolivia', 'Bosnia y Herzegovina', 'Botsuana', 'Brasil', 'Brunei', 'Bulgaria', 'Burkina Faso',
            'Burundi', 'Bután', 'Cabo Verde', 'Camboya', 'Camerún', 'Canadá', 'Catar', 'Chad', 'Chile', 'China',
            'Chipre', 'Ciudad del Vaticano', 'Colombia', 'Comoras', 'Congo', 'Corea del Norte', 'Corea del Sur',
            'Costa de Marfil', 'Costa Rica', 'Croacia', 'Cuba', 'Curazao', 'Dinamarca', 'Dominica', 'Djibutí',
            'Ecuador', 'Egipto', 'El Salvador', 'Emiratos Árabes Unidos', 'España', 'Estados Unidos', 'Estonia',
            'Etiopía', 'Filipinas', 'Finlandia', 'Fiyi', 'Francia', 'Gabón', 'Gambia', 'Gana', 'Georgia',
            'Gibraltar', 'Grecia', 'Groenlandia', 'Guadalupe', 'Guam', 'Guatemala', 'Guayana Francesa', 'Guyana',
            'Haití', 'Holanda', 'Honduras', 'Hong Kong', 'Hungría', 'India', 'Indonesia', 'Irak', 'Irán',
            'Irlanda', 'Irlanda del Norte', 'Isla de Man', 'Islandia', 'Islas Åland', 'Islas Ascensión',
            'Islas Caimán', 'Islas Canarias', 'Islas Cocos', 'Islas Cook', 'Islas Feroe', 'Islas Hébridas',
            'Islas Malvinas', 'Islas Marianas', 'Islas Norfolk', 'Islas Palau', 'Islas Pitcairn', 'Islas Salomón',
            'Islas Seychelles', 'Islas Turks y Caicos', 'Islas Vírgenes Británicas', 'Islas Vírgenes de EE. UU.',
            'Italia', 'Jamaica', 'Japón', 'Jersey', 'Jordania', 'Kazajistán', 'Kenia', 'Kirguistán', 'Kiribati',
            'Kuwait', 'Laos', 'Lesoto', 'Letonia', 'Líbano', 'Liberia', 'Libia', 'Liechtenstein', 'Lituania',
            'Luxemburgo', 'Macao', 'Macedonia del Norte', 'Madagascar', 'Malasia', 'Malawi', 'Maldivas', 'Mali',
            'Malta', 'Marruecos', 'Martinica', 'Mauricio', 'Mauritania', 'Mayotte', 'México', 'Micronesia',
            'Moldavia', 'Mónaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Mozambique', 'Namibia', 'Nauru',
            'Nepal', 'Nicaragua', 'Níger', 'Nigeria', 'Niue', 'Noruega', 'Nueva Caledonia', 'Nueva Zelanda',
            'Omán', 'Países Bajos', 'Panamá', 'Papúa Nueva Guinea', 'Paquistán', 'Paraguay', 'Perú',
            'Polinesia Francesa', 'Polonia', 'Puerto Rico', 'Qatar', 'República Centroafricana', 'República Checa',
            'República Democrática del Congo', 'República Dominicana', 'Reunión', 'Ruanda', 'Rumania', 'Rusia',
            'Saba', 'Sahara Occidental', 'Samoa', 'Samoa Americana', 'San Bartolomé', 'San Cristóbal y Nieves',
            'San Eustaquio', 'San Marino', 'San Martín', 'San Pedro y Miquelón', 'San Vicente y las Granadinas',
            'Santa Elena', 'Santa Lucía', 'Santo Tomé y Príncipe', 'Senegal', 'Serbia', 'Seychelles',
            'Sierra Leona', 'Singapur', 'Sint Maarten', 'Siria', 'Somalia', 'Sri Lanka', 'Suazilandia',
            'Sudáfrica', 'Sudán', 'Sudán del Sur', 'Suecia', 'Suiza', 'Surinam', 'Svalbard y Jan Mayen',
            'Tailandia', 'Taiwán', 'Tanzania', 'Tayikistán', 'Territorio Británico del Océano Índico',
            'Terranova y Labrador', 'Territorios Franceses del Sur', 'Timor Oriental', 'Togo', 'Tokelau',
            'Tonga', 'Trinidad y Tobago', 'Túnez', 'Turkmenistán', 'Turquía', 'Turks y Caicos', 'Tuvalu',
            'Ucrania', 'Uganda', 'Uruguay', 'Uzbekistán', 'Vanuatu', 'Venezuela', 'Vietnam', 'Wallis y Futuna',
            'Yemen', 'Zambia', 'Zimbabue'
        ];

        // Cargar países cuando se cargue la página
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country-select');
            
            // Limpiar la opción de carga
            countrySelect.innerHTML = '<option value="">Selecciona tu país</option>';
            
            // Agregar todos los países ordenados alfabéticamente
            countriesList.sort().forEach(country => {
                const option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                countrySelect.appendChild(option);
            });
        });
    </script>
</body>
</html>
