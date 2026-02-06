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

// Verificar si se subió un archivo
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibió archivo o hubo un error']);
    exit;
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['avatar'];

// Validar tamaño (máximo 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo es demasiado grande (máximo 5MB)']);
    exit;
}

// Validar tipo de archivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime_type, $allowed_mimes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de archivo no permitido. Solo se aceptan JPEG, PNG, GIF y WebP']);
    exit;
}

try {
    // Crear directorio si no existe
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generar nombre único para el archivo
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = $user_id . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;
    
    // Mover archivo temporal a la carpeta uploads
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el archivo']);
        exit;
    }
    
    // Obtener foto anterior para eliminarla
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $old_pic = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Eliminar foto anterior si existe y no es la defecto
    if ($old_pic && $old_pic['profile_pic'] !== 'default.png') {
        $old_path = $upload_dir . $old_pic['profile_pic'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }
    
    // Actualizar base de datos
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    if ($stmt->execute([$new_filename, $user_id])) {
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
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
