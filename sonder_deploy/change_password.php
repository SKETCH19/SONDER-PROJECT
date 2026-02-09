<?php
include 'includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Validar CSRF token si existe
if (isset($_POST['csrf_token'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF inválido']);
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? null;
$new_password = $_POST['new_password'] ?? null;
$confirm_password = $_POST['confirm_password'] ?? null;

// Validaciones
if (!$current_password || !$new_password || !$confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos son requeridos']);
    exit;
}

if ($new_password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'Las contraseñas nuevas no coinciden']);
    exit;
}

if (!validatePassword($new_password)) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña debe tener al menos 8 caracteres']);
    exit;
}

// Evitar usar la misma contraseña
if ($current_password === $new_password) {
    http_response_code(400);
    echo json_encode(['error' => 'La nueva contraseña no puede ser igual a la actual']);
    exit;
}

try {
    // Obtener usuario actual
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    // Verificar contraseña actual
    if (!password_verify($current_password, $user['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'La contraseña actual es incorrecta']);
        exit;
    }
    
    // Hash la nueva contraseña
    $hashed_password = password_hash($new_password, PASSWORD_ARGON2ID);
    
    // Actualizar contraseña
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    if ($stmt->execute([$hashed_password, $user_id])) {
        logAction('CHANGE_PASSWORD', 'Contraseña actualizada');
        
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar la contraseña']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos']);
    logAction('CHANGE_PASSWORD_ERROR', 'Error: ' . $e->getMessage());
}
?>
