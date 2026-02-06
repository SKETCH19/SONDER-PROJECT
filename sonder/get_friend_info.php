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
        SELECT id, username, full_name, profile_pic 
        FROM users 
        WHERE id = ?
    ");
    
    $stmt->execute([$friend_id]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($friend) {
        echo json_encode([
            'success' => true,
            'friend' => $friend
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la BD: ' . $e->getMessage()]);
}
?>
