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
$full_name = $_POST['full_name'] ?? null;
$username = $_POST['username'] ?? null;
$email = $_POST['email'] ?? null;
$country = $_POST['country'] ?? null;
$phone = $_POST['phone'] ?? null;

// Validaciones
if (!$full_name || !$username || !$email || !$country) {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos requeridos deben completarse']);
    exit;
}

try {
    // Verificar si el username ya existe (excepto el del usuario actual)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre de usuario ya está en uso']);
        exit;
    }
    
    // Verificar si el email ya existe (excepto el del usuario actual)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'El email ya está en uso']);
        exit;
    }
    
    // Actualizar perfil
    $stmt = $pdo->prepare("
        UPDATE users 
        SET full_name = ?, username = ?, email = ?, country = ?, phone = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$full_name, $username, $email, $country, $phone, $user_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el perfil']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
