<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


include __DIR__ . '/includes/config.php'; // Carga tu conexión PDO

// Verifica que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    die("Usuario no autenticado");
}

$user_id = $_SESSION['user_id'];

// Verifica si se subió un archivo
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $targetDir = __DIR__ . "/uploads/";
    $fileName = basename($_FILES["avatar"]["name"]);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validar formato
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($fileType, $validExtensions)) {
        die("Formato no válido");
    }

    // Mover el archivo al servidor
    if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
        // Guardar en la base de datos usando PDO
        $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->execute([$fileName, $user_id]);

        echo "Imagen actualizada correctamente";
    } else {
        echo "Error al subir la imagen";
    }
} else {
    echo "No se recibió ninguna imagen";
}
?>
