<?php
include 'includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$friend_id = $_GET['friend_id'] ?? null;

if (!$friend_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de amigo requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_id, m.message, m.created_at, u.username, u.profile_pic
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE (
            (m.sender_id = ? AND m.receiver_id = ?) OR
            (m.sender_id = ? AND m.receiver_id = ?)
        )
        ORDER BY m.created_at ASC
    ");
    
    $stmt->execute([$_SESSION['user_id'], $friend_id, $friend_id, $_SESSION['user_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marcar mensajes como leídos
    $update_stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE receiver_id = ? AND sender_id = ? AND is_read = 0
    ");
    $update_stmt->execute([$_SESSION['user_id'], $friend_id]);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la BD: ' . $e->getMessage()]);
}
?>
