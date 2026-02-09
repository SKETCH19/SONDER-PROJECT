<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$friend_id = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
if ($friend_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

$result = acceptFriendRequest($_SESSION['user_id'], $friend_id);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Solicitud aceptada']);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se pudo aceptar la solicitud']);
}
?>
