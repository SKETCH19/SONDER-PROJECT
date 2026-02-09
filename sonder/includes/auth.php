<?php
require_once __DIR__ . '/config.php';

// Registrar usuario
function registerUser($userData) {
    global $pdo;
    
    // Validaciones
    if (!isset($userData['email'], $userData['password'], 
              $userData['full_name'], $userData['birth_date'], $userData['country'], 
              $userData['phone'])) {
        return ['success' => false, 'message' => 'Datos incompletos'];
    }
    
    $username = trim($userData['username'] ?? '');
    $email = trim($userData['email'] ?? '');
    if ($username !== '' && !validateUsername($username)) {
        return ['success' => false, 'message' => 'El nombre de usuario debe tener 3-20 caracteres (letras, números, guión bajo)'];
    }
    
    // Validar email
    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'El correo electrónico no es válido'];
    }
    
    // Validar contraseña
    if (!validatePassword($userData['password'])) {
        return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres'];
    }
    
    // Validar edad
    if (!validateAge($userData['birth_date'])) {
        return ['success' => false, 'message' => 'Debes tener al menos 18 años para registrarte'];
    }
    
    // Validar que el teléfono tenga solo números y guiones
    if (!preg_match('/^[0-9\-\+\(\)\s]{7,20}$/', $userData['phone'])) {
        return ['success' => false, 'message' => 'Número de teléfono inválido'];
    }
    
    // Verificar si el nombre de usuario ya existe
    if ($username !== '') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() !== false) {
            return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
        }
    }
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() !== false) {
        return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
    }
    
    // Hash de la contraseña
    //$hashedPassword = password_hash($userData['password'], PASSWORD_ARGON2ID);
      $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
    
    if ($username === '') {
        $username = generateUniqueUsername('user');
    }

    // Insertar usuario
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, username, email, password, birth_date, country, phone, profile_pic, profile_completed)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    try {
        $stmt->execute([
            trim($userData['full_name']),
            $username,
            $email,
            $hashedPassword,
            $userData['birth_date'],
            $userData['country'],
            trim($userData['phone']),
            'default.png',
            0
        ]);
        
        $user_id = $pdo->lastInsertId();
        logAction('REGISTER', 'Nuevo usuario registrado: ' . $userData['username']);
        
        return ['success' => true, 'user_id' => $user_id, 'username' => $username];
    } catch(PDOException $e) {
        $errorMessage = $e->getMessage();
        error_log('REGISTER_ERROR: ' . $errorMessage);

        if (stripos($errorMessage, 'UNIQUE') !== false && stripos($errorMessage, 'email') !== false) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
        }

        if (stripos($errorMessage, 'UNIQUE') !== false && stripos($errorMessage, 'username') !== false) {
            return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
        }

        if (stripos($errorMessage, 'database is locked') !== false) {
            return ['success' => false, 'message' => 'La base de datos está ocupada. Intenta de nuevo en unos segundos.'];
        }

        return ['success' => false, 'message' => 'Error al registrar usuario. Por favor, intenta de nuevo.'];
    }
}

// Iniciar sesión
function loginUser($username, $password) {
    global $pdo;
    
    // Validar inputs
    if (empty($username) || empty($password)) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, username, password, is_active, profile_completed 
        FROM users 
        WHERE (username = ? OR email = ?)
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_completed'] = (int)$user['profile_completed'];
        $_SESSION['login_time'] = time();
        
        logAction('LOGIN', 'Inicio de sesión exitoso');
        return true;
    }
    
    logAction('LOGIN_FAILED', 'Intento de inicio de sesión fallido con usuario: ' . $username);
    return false;
}

// Verificar si el usuario está logueado
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Validar que el usuario siga existiendo y activo
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user && $user['is_active'];
}

function isProfileComplete($user_id = null) {
    if (isset($_SESSION['profile_completed'])) {
        return (int)$_SESSION['profile_completed'] === 1;
    }

    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }

    if (!$user_id) {
        return false;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT profile_completed FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return false;
    }

    $_SESSION['profile_completed'] = (int)$row['profile_completed'];
    return (int)$row['profile_completed'] === 1;
}

function generateUniqueUsername($base) {
    global $pdo;
    $base = preg_replace('/[^a-zA-Z0-9_]/', '', $base);
    if ($base === '') {
        $base = 'user';
    }

    do {
        $candidate = $base . '_' . bin2hex(random_bytes(3));
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$candidate]);
        $exists = $stmt->fetchColumn();
    } while ($exists !== false);

    return $candidate;
}

// Cerrar sesión
function logout() {
    logAction('LOGOUT', 'Cierre de sesión');
    session_destroy();
    header('Location: index.php');
    exit;
}

// Obtener información del usuario
function getUserInfo($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Actualizar foto de perfil
function updateProfilePic($user_id, $profile_pic) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$profile_pic, $user_id]);
}

// Generar token CSRF
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validar token CSRF
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

?>
