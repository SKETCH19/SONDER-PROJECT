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

// No permitir obtener info de sí mismo desde esta ruta
if ($user_id === $friend_id) {
    http_response_code(400);
    echo json_encode(['error' => 'No puedes obtener info de ti mismo']);
    exit;
}

try {
    // Obtener información del usuario amigo
    $stmt = $pdo->prepare("
        SELECT id, username, full_name, profile_pic, country, created_at
        FROM users 
        WHERE id = ? AND is_active = 1
    ");
    
    $stmt->execute([$friend_id]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$friend) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    // Obtener estado de amistad
    $friendship_status = getFriendshipStatus($user_id, $friend_id);
    
    // Escapar datos para seguridad XSS
    $safe_friend = [
        'id' => $friend['id'],
        'username' => htmlspecialchars($friend['username'], ENT_QUOTES, 'UTF-8'),
        'full_name' => htmlspecialchars($friend['full_name'], ENT_QUOTES, 'UTF-8'),
        'profile_pic' => htmlspecialchars($friend['profile_pic'], ENT_QUOTES, 'UTF-8'),
        'country' => htmlspecialchars($friend['country'], ENT_QUOTES, 'UTF-8'),
        'created_at' => $friend['created_at'],
        'friendship_status' => $friendship_status
    ];
    
    logAction('VIEW_FRIEND_INFO', "Vio información del usuario ID $friend_id");
    
    echo json_encode([
        'success' => true,
        'friend' => $safe_friend
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener información del usuario']);
    logAction('GET_FRIEND_INFO_ERROR', 'Error: ' . $e->getMessage());
}
?>
