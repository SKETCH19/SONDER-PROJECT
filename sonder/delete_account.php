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
$confirm_password = $_POST['confirm_password'] ?? null;

// Validar confirmación
if (!$confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña es requerida']);
    exit;
}

try {
    // Obtener usuario actual
    $stmt = $pdo->prepare("SELECT password, profile_pic FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    // Verificar contraseña
    if (!password_verify($confirm_password, $user['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'La contraseña es incorrecta']);
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Eliminar archivo de avatar si existe
    $upload_dir = __DIR__ . '/uploads/';
    if ($user['profile_pic'] && $user['profile_pic'] !== 'default.png') {
        $avatar_path = $upload_dir . $user['profile_pic'];
        if (realpath($avatar_path) && strpos(realpath($avatar_path), realpath($upload_dir)) === 0) {
            if (file_exists($avatar_path)) {
                unlink($avatar_path);
            }
        }
    }
    
    // Marcar usuario como inactivo en lugar de eliminarlo completamente (opción más segura)
    $stmt = $pdo->prepare("UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    if (!$stmt->execute([$user_id])) {
        throw new Exception('Error al marcar usuario como inactivo');
    }
    
    // Eliminación opcional de mensajes relacionados (comentar si quieres mantenerlos)
    // $stmt = $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
    // $stmt->execute([$user_id, $user_id]);
    
    // Eliminación opcional de amistades
    // $stmt = $pdo->prepare("DELETE FROM friends WHERE user_id = ? OR friend_id = ?");
    // $stmt->execute([$user_id, $user_id]);
    
    $pdo->commit();
    
    // Registrar la acción
    logAction('DELETE_ACCOUNT', 'Cuenta eliminada');
    
    // Log a la base de datos antes de destruir la sesión no funcionaría bien,
    // pero ya lo hicimos arriba
    
    // Destruir sesión
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Cuenta eliminada correctamente',
        'redirect' => 'index.php'
    ]);
} catch(PDOException $e) {
    try {
        $pdo->rollBack();
    } catch(Exception $e) {}
    
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar la cuenta']);
    logAction('DELETE_ACCOUNT_ERROR', 'Error: ' . $e->getMessage());
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar la cuenta']);
}
?>
