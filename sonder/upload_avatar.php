<?php
include 'includes/auth.php';

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

// Verificar si se subió un archivo
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo del servidor',
        UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande',
        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
        UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
        UPLOAD_ERR_NO_TMP_DIR => 'No hay directorio temporal',
        UPLOAD_ERR_CANT_WRITE => 'No se puede escribir en el directorio',
        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP bloqueó el archivo'
    ];
    
    $error = $error_messages[$_FILES['avatar']['error']] ?? 'Error desconocido';
    echo json_encode(['error' => $error]);
    exit;
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['avatar'];

// Validar tamaño (máximo 10MB)
$max_size = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo es demasiado grande (máximo 10MB)']);
    exit;
}

// Validar tamaño mínimo
if ($file['size'] < 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo es demasiado pequeño']);
    exit;
}

// Validar tipo de archivo por MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime_type, $allowed_mimes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de archivo no permitido. Solo se aceptan JPEG, PNG, GIF y WebP']);
    exit;
}

// Validar extensión del archivo
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($file_extension, $allowed_extensions)) {
    http_response_code(400);
    echo json_encode(['error' => 'Extensión de archivo no permitida']);
    exit;
}

// Mapear extensiones correctamente
$extension_map = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
];
$safe_extension = $extension_map[$mime_type];

try {
    // Crear directorio si no existe
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generar nombre único y seguro para el archivo
    $new_filename = 'avatar_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $safe_extension;
    $target_path = $upload_dir . $new_filename;
    
    // Mover archivo temporal a la carpeta uploads
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el archivo']);
        exit;
    }
    
    // Establecer permisos de archivo seguros
    chmod($target_path, 0644);
    
    // Obtener foto anterior para eliminarla
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $old_pic = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Eliminar foto anterior si existe y no es la defecto
    if ($old_pic && $old_pic['profile_pic'] !== 'default.png') {
        $old_path = $upload_dir . $old_pic['profile_pic'];
        // Validar que la ruta esté dentro de uploads/
        if (realpath($old_path) && strpos(realpath($old_path), realpath($upload_dir)) === 0) {
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }
    }
    
    // Actualizar base de datos
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    if ($stmt->execute([$new_filename, $user_id])) {
        logAction('UPLOAD_AVATAR', 'Avatar actualizado');
        
        echo json_encode([
            'success' => true,
            'message' => 'Avatar actualizado correctamente',
            'filename' => $new_filename
        ]);
    } else {
        // Eliminar archivo si no se puede guardar en BD
        unlink($target_path);
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el perfil']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar el archivo']);
    logAction('UPLOAD_AVATAR_ERROR', 'Error: ' . $e->getMessage());
}
?>
