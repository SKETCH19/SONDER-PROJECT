<?php
include 'includes/auth.php';
include 'includes/countries.php';

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

$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'] ?? null;
$username = $_POST['username'] ?? null;
$email = $_POST['email'] ?? null;
$country = $_POST['country'] ?? null;
$phone = $_POST['phone'] ?? null;

// Validaciones básicas
if (!$full_name || !$username || !$email || !$country) {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos requeridos deben completarse']);
    exit;
}

// Validar y sanitizar datos
$full_name = trim($full_name);
$username = trim($username);
$email = trim($email);
$country = trim($country);
$phone = trim($phone ?? '');

// Validaciones de largo
if (strlen($full_name) < 3 || strlen($full_name) > 100) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre debe tener entre 3 y 100 caracteres']);
    exit;
}

if (!validateUsername($username)) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre de usuario debe tener 3-20 caracteres (letras, números, guión bajo)']);
    exit;
}

if (!validateEmail($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'El correo electrónico no es válido']);
    exit;
}

if (!empty($phone) && !preg_match('/^[0-9\-\+\(\)\s]{7,20}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Número de teléfono inválido']);
    exit;
}

// Validar que el país sea válido (lista completa)
$valid_countries = getValidCountries();

if (!in_array($country, $valid_countries, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'País no válido']);
    exit;
}

try {
    // Verificar si el username ya existe (excepto el del usuario actual)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetchColumn() !== false) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre de usuario ya está en uso']);
        exit;
    }
    
    // Verificar si el email ya existe (excepto el del usuario actual)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetchColumn() !== false) {
        http_response_code(400);
        echo json_encode(['error' => 'El email ya está en uso']);
        exit;
    }
    
    // Escapar datos para seguridad
    $safe_full_name = htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8');
    $safe_phone = !empty($phone) ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : '';
    
    // Actualizar perfil
    $stmt = $pdo->prepare("
        UPDATE users 
        SET full_name = ?, username = ?, email = ?, country = ?, phone = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    if ($stmt->execute([$safe_full_name, $username, $email, $country, $safe_phone, $user_id])) {
        logAction('UPDATE_PROFILE', 'Perfil actualizado');
        
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
    echo json_encode(['error' => 'Error en la base de datos']);
    logAction('UPDATE_PROFILE_ERROR', 'Error al actualizar perfil: ' . $e->getMessage());
}
?>
