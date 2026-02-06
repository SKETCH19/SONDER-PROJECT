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

$receiver_id = $_POST['receiver_id'] ?? null;
$message = $_POST['message'] ?? null;

if (!$receiver_id || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, message, created_at) 
        VALUES (?, ?, ?, CURRENT_TIMESTAMP)
    ");
    
    if ($stmt->execute([$_SESSION['user_id'], $receiver_id, $message])) {
        echo json_encode([
            'success' => true,
            'message_id' => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar mensaje']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la BD: ' . $e->getMessage()]);
}
?>
