<?php
include 'config.php';

// Registrar usuario
function registerUser($userData) {
    global $pdo;
    
    // Verificar si el nombre de usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$userData['username']]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
    }
    
    // Hash de la contraseña
    $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    // Insertar usuario
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, birth_date, country, phone, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([
            $userData['full_name'],
            $userData['username'],
            $userData['email'],
            $hashedPassword,
            $userData['birth_date'],
            $userData['country'],
            $userData['phone'],
            $userData['profile_pic']
        ]);
        
        return ['success' => true, 'user_id' => $pdo->lastInsertId()];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()];
    }
}

// Iniciar sesión
function loginUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Cerrar sesión
function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Obtener información del usuario
function getUserInfo($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Actualizar foto de perfil
function updateProfilePic($user_id, $profile_pic) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    return $stmt->execute([$profile_pic, $user_id]);
}





?>