<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Necesitas iniciar sesión']);
    exit;
}

$friend_id = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
if ($friend_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

$result = sendFriendRequest($_SESSION['user_id'], $friend_id);
echo json_encode($result);
?>
