<?php
include 'includes/auth.php';
include 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$friend_id = $_GET['friend_id'] ?? null;

if (!$friend_id || !is_numeric($friend_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de amigo requerido']);
    exit;
}

$friend_id = (int)$friend_id;
$user_id = $_SESSION['user_id'];

// No permitir obtener mensajes consigo mismo
if ($user_id === $friend_id) {
    http_response_code(400);
    echo json_encode(['error' => 'No puedes obtener mensajes contigo mismo']);
    exit;
}

// Verificar que el usuario amigo existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$friend_id]);
if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'El usuario no existe']);
    exit;
}

// Verificar si el usuario está bloqueado por el friend_id
if (isUserBlocked($user_id, $friend_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'No puedes ver los mensajes de este usuario']);
    exit;
}

try {
    // Obtener mensajes entre los dos usuarios
    $stmt = $pdo->prepare("
        SELECT 
            m.id, 
            m.sender_id, 
            m.message, 
            m.created_at, 
            u.username, 
            u.profile_pic
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE (
            (m.sender_id = ? AND m.receiver_id = ?) OR
            (m.sender_id = ? AND m.receiver_id = ?)
        )
        ORDER BY m.created_at ASC
    ");
    
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Escapar mensajes para XSS prevention
    $safe_messages = array_map(function($msg) {
        return [
            'id' => $msg['id'],
            'sender_id' => $msg['sender_id'],
            'message' => htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8'),
            'created_at' => $msg['created_at'],
            'username' => htmlspecialchars($msg['username'], ENT_QUOTES, 'UTF-8'),
            'profile_pic' => htmlspecialchars($msg['profile_pic'], ENT_QUOTES, 'UTF-8')
        ];
    }, $messages);
    
    // Marcar mensajes como leídos
    $update_stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE receiver_id = ? AND sender_id = ? AND is_read = 0
    ");
    $update_stmt->execute([$user_id, $friend_id]);
    
    logAction('VIEW_MESSAGES', "Vio mensajes del usuario ID $friend_id");
    
    echo json_encode([
        'success' => true,
        'messages' => $safe_messages
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener mensajes']);
    logAction('GET_MESSAGES_ERROR', 'Error: ' . $e->getMessage());
}
?>
