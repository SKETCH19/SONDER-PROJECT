<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : (isset($_POST['q']) ? trim($_POST['q']) : '');
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

if ($q === '' || strlen($q) < 2) {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

try {
    $results = searchUsers($q, $current_user_id);
    // Añadir URL completa de la imagen de perfil
    foreach ($results as &$r) {
        $r['profile_pic_url'] = 'uploads/' . ($r['profile_pic'] ?? 'default.png');
    }

    echo json_encode(['success' => true, 'results' => $results]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la búsqueda']);
}

?>
