<?php
require_once __DIR__ . '/includes/auth.php';

$target = null;
if (isset($_GET['user_id'])) {
    $id = (int)$_GET['user_id'];
    $target = getUserInfo($id);
} elseif (isset($_GET['username'])) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$_GET['username']]);
    $id = $stmt->fetchColumn();
    if ($id) $target = getUserInfo((int)$id);
}

if (!$target) {
    http_response_code(404);
    echo "<h2>Usuario no encontrado</h2>";
    exit;
}

// Determinar si el visitante está logueado
$viewer_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Perfil de <?php echo htmlspecialchars($target['full_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="welcome-page">
    <div class="stars"></div>
    <div class="profile-page">
        <div class="profile-card">
            <div class="profile-header">
                <img src="uploads/<?php echo htmlspecialchars($target['profile_pic'] ?: 'default.png'); ?>" alt="Avatar" class="profile-large-avatar" onerror="this.src='https://placehold.co/150'">
                <div class="profile-header-text">
                    <h1><?php echo htmlspecialchars($target['full_name']); ?></h1>
                    <p class="muted">@<?php echo htmlspecialchars($target['username']); ?></p>
                    <p class="profile-join">Miembro desde <?php echo date('F Y', strtotime($target['created_at'])); ?></p>
                </div>
            </div>

            <?php if ($viewer_id && $viewer_id != $target['id']): ?>
                <div class="profile-actions">
                    <button id="add-friend-btn" class="btn btn-primary">Enviar solicitud</button>
                </div>
            <?php endif; ?>

            <div class="profile-meta">
                <div>
                    <span class="profile-meta-label">País</span>
                    <span class="profile-meta-value"><?php echo htmlspecialchars($target['country']); ?></span>
                </div>
                <div>
                    <span class="profile-meta-label">Teléfono</span>
                    <span class="profile-meta-value"><?php echo htmlspecialchars($target['phone']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('add-friend-btn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                btn.disabled = true;
                btn.textContent = 'Enviando...';
                const form = new FormData();
                form.append('friend_id', '<?php echo $target['id']; ?>');
                fetch('send_friend_request.php', { method: 'POST', body: form, credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(res => {
                        const message = res.message || (res.success ? 'Solicitud enviada' : 'Error');
                        if (res.success) {
                            btn.textContent = 'Enviado';
                        } else {
                            btn.disabled = false;
                            btn.textContent = 'Enviar solicitud';
                        }
                        showProfileToast(message, res.success);
                    }).catch(() => {
                        showProfileToast('Error al enviar solicitud', false);
                        btn.disabled = false;
                        btn.textContent = 'Enviar solicitud';
                    });
            });
        });

        function showProfileToast(message, success) {
            const toast = document.createElement('div');
            toast.className = 'profile-toast' + (success ? ' success' : '');
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
