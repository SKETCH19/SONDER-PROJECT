<?php
include 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (isProfileComplete()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
$username_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $username_value = $username;
    $avatar = $_FILES['avatar'] ?? null;
    $username_normalized = strtolower($username);

    if (!validateUsername($username)) {
        $error = 'El nombre de usuario debe tener 3-20 caracteres (letras, números, guión bajo)';
    } else if (!$avatar || $avatar['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo del servidor',
            UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'Debes subir una foto de perfil válida',
            UPLOAD_ERR_NO_TMP_DIR => 'No hay directorio temporal',
            UPLOAD_ERR_CANT_WRITE => 'No se puede escribir en el directorio',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP bloqueó el archivo'
        ];
        $error = $uploadErrors[$avatar['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'Debes subir una foto de perfil válida';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = ? AND id != ?");
        $stmt->execute([$username_normalized, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $error = 'El nombre de usuario ya está en uso';
        }
    }

    if ($error === null) {
        $file = $avatar;
        $max_size = 10 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            $error = 'El archivo es demasiado grande (máximo 5MB)';
        } else if ($file['size'] < 1024) {
                $error = 'El archivo es demasiado pequeño';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mime_type, $allowed_mimes, true)) {
                $error = 'Tipo de archivo no permitido. Solo se aceptan JPEG, PNG, GIF y WebP';
            }
        }
    }

    if ($error === null) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_extension, $allowed_extensions, true)) {
            $error = 'Extensión de archivo no permitida';
        }
    }

    if ($error === null) {
        $extension_map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        $safe_extension = $extension_map[$mime_type];

        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $new_filename = 'avatar_' . $_SESSION['user_id'] . '_' . bin2hex(random_bytes(8)) . '.' . $safe_extension;
        $target_path = $upload_dir . $new_filename;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            $error = 'Error al guardar el archivo';
        } else {
            chmod($target_path, 0644);

            $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $old_pic = $stmt->fetch(PDO::FETCH_ASSOC);

            try {
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET username = ?, profile_pic = ?, profile_completed = 1, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");

                if ($stmt->execute([$username, $new_filename, $_SESSION['user_id']])) {
                    if ($old_pic && $old_pic['profile_pic'] !== 'default.png') {
                        $old_path = $upload_dir . $old_pic['profile_pic'];
                        if (realpath($old_path) && strpos(realpath($old_path), realpath($upload_dir)) === 0) {
                            if (file_exists($old_path)) {
                                unlink($old_path);
                            }
                        }
                    }

                    $_SESSION['username'] = $username;
                    $_SESSION['profile_completed'] = 1;

                    header('Location: dashboard.php');
                    exit;
                }

                unlink($target_path);
                $error = 'Error al actualizar el perfil';
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                    $error = 'El nombre de usuario ya está en uso';
                } else {
                    $error = 'Error al actualizar el perfil';
                }
                unlink($target_path);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil - Sonder</title>
    <link rel="icon" href="logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="welcome-page">
    <div class="stars"></div>
    <div class="form-container">
        <div class="form-logo-container">
            <img src="logo.svg" alt="Sonder" class="form-logo">
            <h1 class="form-title">Sonder</h1>
        </div>

        <h2 style="text-align: center; color: var(--stardust); margin-bottom: 1.5rem; font-size: 1.2rem; opacity: 0.8;">Completa tu perfil</h2>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Nombre de usuario</label>
                <input type="text" name="username" class="form-input" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+" value="<?php echo htmlspecialchars($username_value, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="form-help">3-20 caracteres (letras, números, guión bajo)</span>
            </div>

            <div class="form-group">
                <label class="form-label">Foto de perfil</label>
                <input type="file" name="avatar" class="form-input" accept="image/*" required>
                <span class="form-help">Formatos permitidos: JPG, PNG, GIF, WebP (máx. 10MB). Si es muy grande, se comprime automaticamente.</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar y continuar</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            const fileInput = document.querySelector('input[name="avatar"]');

            if (!form || !fileInput) {
                return;
            }

            const MAX_CLIENT_SIZE = 1500000; // 1.5MB
            const MAX_DIMENSION = 1024;

            function resizeImage(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => {
                        const img = new Image();
                        img.onload = () => {
                            const scale = Math.min(1, MAX_DIMENSION / Math.max(img.width, img.height));
                            const width = Math.round(img.width * scale);
                            const height = Math.round(img.height * scale);

                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;

                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);

                            let quality = 0.85;
                            function attempt() {
                                canvas.toBlob(blob => {
                                    if (!blob) {
                                        reject(new Error('No se pudo comprimir la imagen'));
                                        return;
                                    }

                                    if (blob.size <= MAX_CLIENT_SIZE || quality <= 0.5) {
                                        resolve(blob);
                                    } else {
                                        quality -= 0.1;
                                        attempt();
                                    }
                                }, 'image/jpeg', quality);
                            }

                            attempt();
                        };
                        img.onerror = () => reject(new Error('Archivo de imagen inválido'));
                        img.src = reader.result;
                    };
                    reader.onerror = () => reject(new Error('No se pudo leer la imagen'));
                    reader.readAsDataURL(file);
                });
            }

            form.addEventListener('submit', async (event) => {
                const file = fileInput.files && fileInput.files[0];

                if (!file) {
                    return;
                }

                if (file.size <= MAX_CLIENT_SIZE) {
                    return;
                }

                event.preventDefault();

                try {
                    const compressed = await resizeImage(file);
                    const compressedFile = new File([compressed], 'avatar.jpg', { type: 'image/jpeg' });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    fileInput.files = dataTransfer.files;

                    form.submit();
                } catch (err) {
                    alert('No se pudo comprimir la imagen. Intenta con una foto mas pequeña.');
                }
            });
        });
    </script>
</body>
</html>
