<?php
include 'includes/auth.php';
include 'includes/functions.php';

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

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = $_POST['message'] ?? null;

// Validaciones básicas
if (!$receiver_id || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Validar que receiver_id sea numérico
if (!is_numeric($receiver_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de receptor inválido']);
    exit;
}

$receiver_id = (int)$receiver_id;

// Validar que no sea a sí mismo
if ($sender_id === $receiver_id) {
    http_response_code(400);
    echo json_encode(['error' => 'No puedes enviarte mensajes a ti mismo']);
    exit;
}

// Validar que el mensaje no esté vacío
$message = trim($message);
if (strlen($message) === 0 || strlen($message) > 5000) {
    http_response_code(400);
    echo json_encode(['error' => 'El mensaje debe tener entre 1 y 5000 caracteres']);
    exit;
}

// Verificar que el receptor existe y está activo
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$receiver_id]);
if ($stmt->fetchColumn() === false) {
    http_response_code(404);
    echo json_encode(['error' => 'El usuario receptor no existe']);
    exit;
}

// Verificar si el receptor tiene bloqueado al sender
if (isUserBlocked($sender_id, $receiver_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'No puedes enviar mensajes a este usuario']);
    exit;
}

// Verificar que sean amigos (opcional, comentar si quieres permitir mensajes de desconocidos)
// if (!areFriends($sender_id, $receiver_id)) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Solo puedes enviar mensajes a tus amigos']);
//     exit;
// }

try {
    // Preparar el mensaje (escapar HTML para seguridad)
    $safe_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, message, message_type, created_at) 
        VALUES (?, ?, ?, 'text', CURRENT_TIMESTAMP)
    ");
    
    if ($stmt->execute([$sender_id, $receiver_id, $safe_message])) {
        $message_id = $pdo->lastInsertId();
        
        logAction('SEND_MESSAGE', "Mensaje enviado a usuario ID $receiver_id");
        
        echo json_encode([
            'success' => true,
            'message_id' => $message_id,
            'message' => 'Mensaje enviado correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar mensaje']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos']);
    logAction('SEND_MESSAGE_ERROR', 'Error al enviar mensaje: ' . $e->getMessage());
}
?>
