<?php
include 'includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$user_id = $_SESSION['user_id'];
$confirm_password = $_POST['confirm_password'] ?? null;

if (!$confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña es requerida']);
    exit;
}

try {
    // Obtener usuario actual
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
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
    
    // Eliminar mensajes del usuario
    $stmt = $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
    $stmt->execute([$user_id, $user_id]);
    
    // Eliminar amistades del usuario
    $stmt = $pdo->prepare("DELETE FROM friends WHERE user_id = ? OR friend_id = ?");
    $stmt->execute([$user_id, $user_id]);
    
    // Eliminar usuario
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        $pdo->commit();
        
        // Destruir sesión
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cuenta eliminada correctamente',
            'redirect' => 'index.php'
        ]);
    } else {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar la cuenta']);
    }
} catch(PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos']);
}
?>
