<?php
include 'includes/auth.php';

header('Content-Type: application/json');

if (!GOOGLE_AUTH_ENABLED) {
    echo json_encode(['success' => false, 'message' => 'Google Sign-In deshabilitado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['token'])) {
    echo json_encode(['success' => false, 'message' => 'Token no proporcionado']);
    exit;
}

$token = $_POST['token'];

try {
    // Verificar el token JWT de Google
    // Nota: En producción, deberías usar la librería official de Google
    // Para desarrollo, usamos una verificación básica
    
    $tokenParts = explode('.', $token);
    if (count($tokenParts) !== 3) {
        throw new Exception('Token inválido');
    }

    // Decodificar el payload (segunda parte del JWT)
    $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);
    
    if (!$payload) {
        throw new Exception('No se pudo decodificar el token');
    }

    // Verificar que el token sea de Google
    if (!isset($payload['iss']) || strpos($payload['iss'], 'accounts.google.com') === false) {
        throw new Exception('Token no es de Google');
    }

    // Verificar que el client_id sea el correcto
    if (!isset($payload['aud']) || $payload['aud'] !== GOOGLE_CLIENT_ID) {
        throw new Exception('Client ID no válido');
    }

    // Verificar que el token no esté expirado
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        throw new Exception('Token expirado');
    }

    // Obtener información del usuario desde el token
    $googleId = $payload['sub'] ?? null;
    $email = $payload['email'] ?? null;
    $name = $payload['name'] ?? null;
    $picture = $payload['picture'] ?? null;

    if (!$googleId || !$email) {
        throw new Exception('Información del usuario incompleta');
    }

    // Buscar o crear el usuario en la base de datos
    global $pdo;

    // Verificar si el usuario ya existe por email
    $stmt = $pdo->prepare("SELECT id, username, is_active, profile_completed FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Usuario existe, iniciar sesión
        if (!$user['is_active']) {
            echo json_encode(['success' => false, 'message' => 'Usuario desactivado']);
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['google_auth'] = true;
        $_SESSION['profile_completed'] = (int)$user['profile_completed'];
        
        logAction('LOGIN_GOOGLE', 'Inicio de sesión con Google');
        $redirect = ($user['profile_completed'] == 1) ? 'dashboard.php' : 'complete_profile.php';
        echo json_encode(['success' => true, 'message' => 'Autenticación exitosa', 'redirect' => $redirect]);
    } else {
        // Crear nuevo usuario desde Google
        // Generar un username único basado en el email
        $username = generateUniqueUsername('user');

        // Generar una contraseña aleatoria para Google Sign-In
        $randomPassword = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);

        // Usar la fecha actual como fecha de nacimiento (el usuario puede actualizar)
        $birthDate = date('Y-m-d', strtotime('-25 years'));

        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, username, email, password, birth_date, country, phone, profile_pic, is_active, profile_completed)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 0)
            ");

            $result = $stmt->execute([
                $name,
                $username,
                $email,
                $hashedPassword,
                $birthDate,
                'No especificado',
                '0000000000',
                'default.png'
            ]);

            if ($result) {
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['google_auth'] = true;
                $_SESSION['profile_completed'] = 0;

                logAction('REGISTER_GOOGLE', 'Registro automático con Google');
                echo json_encode(['success' => true, 'message' => 'Usuario creado y autenticado', 'redirect' => 'complete_profile.php']);
            } else {
                throw new Exception('No se pudo crear el usuario');
            }
        } catch(PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el usuario']);
            }
            error_log("Google Auth Error: " . $e->getMessage());
            exit;
        }
    }

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    error_log("Google Auth Error: " . $e->getMessage());
    exit;
}
?>
