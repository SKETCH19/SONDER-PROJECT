<?php
session_start();
include 'includes/auth.php'; //  Asegúrate de tener tu conexión a la BD aquí

// Asegúrate de que el usuario haya iniciado sesión
if (!isset($_SESSION['user_id'])) {
    echo "No estás autenticado.";
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['avatar'];
    $targetDir = __DIR__ . '/uploads/';

    // Crear carpeta si no existe
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Generar nombre único
    $fileName = uniqid('avatar_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $targetPath = $targetDir . $fileName;

    // Mover archivo al servidor
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {

        //  Guardar el nuevo nombre en la base de datos
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $fileName, $userId);

        if ($stmt->execute()) {
            echo "Imagen actualizada correctamente";
        } else {
            echo "Error al actualizar la base de datos.";
        }

        $stmt->close();

    } else {
        echo "Error al mover el archivo.";
    }

} else {
    echo "No se recibió ninguna imagen o hubo un error.";
}
?>
