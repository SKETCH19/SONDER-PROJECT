<?php
include 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isProfileComplete()) {
    header('Location: complete_profile.php');
    exit;
}

$userInfo = getUserInfo($_SESSION['user_id']);



// Obtener amigos del usuario
function getUserFriends($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.profile_pic, f.status 
        FROM friends f 
        JOIN users u ON u.id = f.friend_id 
        WHERE f.user_id = ? AND f.status = 'accepted'
        UNION
        SELECT u.id, u.username, u.full_name, u.profile_pic, f.status 
        FROM friends f 
        JOIN users u ON u.id = f.user_id 
        WHERE f.friend_id = ? AND f.status = 'accepted'
    ");
    $stmt->execute([$user_id, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener solicitudes pendientes
function getPendingRequests($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.profile_pic, f.created_at 
        FROM friends f 
        JOIN users u ON u.id = f.user_id 
        WHERE f.friend_id = ? AND f.status = 'pending'
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener usuarios bloqueados
function getBlockedUsers($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.profile_pic 
        FROM friends f 
        JOIN users u ON u.id = f.friend_id 
        WHERE f.user_id = ? AND f.status = 'blocked'
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener contactos destacados
function getFeaturedContacts($user_id) {
    global $pdo;
    //
    //
    return getUserFriends($user_id);
}

$friends = getUserFriends($_SESSION['user_id']);
$pending_requests = getPendingRequests($_SESSION['user_id']);
$blocked_users = getBlockedUsers($_SESSION['user_id']);
$featured_contacts = getFeaturedContacts($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sonder - Mensajería</title>
    <link rel="icon" href="logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-current-user-id="<?php echo $_SESSION['user_id']; ?>">
    <button class="mobile-menu-toggle" type="button" aria-label="Abrir menu">☰</button>
    <div class="dashboard">
        <!-- Menú lateral -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="logo.svg" alt="Sonder" class="sonder-logo">
                    <span class="sonder-text">Sonder</span>
                </div>
                <button class="menu-toggle">☰</button>
            </div>
            
            <div class="sidebar-user">
                <div class="user-info">
                    <img src="uploads/<?php echo $userInfo['profile_pic']; ?>" 
                        alt="Avatar" 
                        class="user-avatar" 
                        onerror="this.src='https://placehold.co/40'">
                    <span class="username"><?php echo htmlspecialchars($userInfo['username']); ?></span>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <div class="menu-item active" data-section="messages">
                    <span class="menu-icon">▬</span>
                    <span>Mensajes</span>
                </div>
                <div class="menu-item" data-section="friends">
                    <span class="menu-icon">◯</span>
                    <span>Amigos</span>
                    <?php if (count($pending_requests) > 0): ?>
                        <span class="notification-badge"><?php echo count($pending_requests); ?></span>
                    <?php endif; ?>
                </div>
                <div class="menu-item" data-section="search">
                    <span class="menu-icon">◍</span>
                    <span>Buscar amigos</span>
                </div>
                <div class="menu-item" data-section="featured">
                    <span class="menu-icon">★</span>
                    <span>Destacados</span>
                </div>
                <div class="menu-item" data-section="profile">
                    <span class="menu-icon">◆</span>
                    <span src = "include/profile.php">Perfil</span>
                </div>
            </div>
        </div>
        
        <!-- Área de contenido principal - Mensajes -->
        <div class="chat-area" id="messages-section">
            <div class="chat-header">
                <div class="chat-user">
                    <img src="logo.svg" alt="Sonder" class="chat-user-avatar">
                    <div class="chat-user-info">
                        <h3>Bienvenido a Sonder</h3>
                        <p>Selecciona una conversación</p>
                    </div>
                </div>
                <div class="chat-actions">
                </div>
            </div>
            
            <div class="messages-container">
                <div class="welcome-message">
                    <div class="welcome-logo"><img src="logo.svg" alt="Sonder"></div>
                    <h3>Bienvenido a Sonder</h3>
                    <p>Tu espacio para conexiones significativas</p>
                    <p>Selecciona un chat de la lista o inicia una nueva conversación</p>
                </div>
            </div>
            
            <div class="message-input-container" style="display: none;">
                <input type="text" class="message-input" placeholder="Escribe un mensaje...">
                <button class="send-button">Enviar</button>
            </div>
        </div>
        
        <!-- Sección de Amigos -->
        <div class="content-area" id="friends-section" style="display: none;">
            <div class="section-header">
                <h2>Mis Amigos</h2>
                <div class="section-tabs">
                    <button class="tab-btn active" data-tab="friends-list">Amigos</button>
                    <button class="tab-btn" data-tab="pending-requests">Solicitudes 
                        <?php if (count($pending_requests) > 0): ?>
                            <span class="tab-badge"><?php echo count($pending_requests); ?></span>
                        <?php endif; ?>
                    </button>
                    <button class="tab-btn" data-tab="blocked-users">Bloqueados</button>
                </div>
            </div>
            
            <div class="tab-content active" id="friends-list">
                <?php if (count($friends) > 0): ?>
                    <div class="friends-list">
                        <?php foreach ($friends as $friend): ?>
                            <div class="friend-item" data-user-id="<?php echo $friend['id']; ?>">
                                <img src="uploads/<?php echo $friend['profile_pic']; ?>" alt="<?php echo htmlspecialchars($friend['username']); ?>" class="friend-avatar" onerror="this.src='https://placehold.co/50'">
                                <div class="friend-info">
                                    <div class="friend-name"><?php echo htmlspecialchars($friend['full_name']); ?></div>
                                    <div class="friend-username">@<?php echo htmlspecialchars($friend['username']); ?></div>
                                </div>
                                <div class="friend-actions">
                                    <button class="friend-action chat-with-friend" title="Chatear">Chat</button>
                                    <button class="friend-action block-friend" title="Bloquear">Bloquear</button>
                                    <button class="friend-action remove-friend" title="Eliminar">Eliminar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">≈</div>
                        <h3>Aún no tienes amigos</h3>
                        <p>Busca usuarios y envíales solicitudes para comenzar a chatear</p>
                        <button class="btn btn-primary" onclick="showSection('search')">Buscar amigos</button>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content" id="pending-requests">
                <?php if (count($pending_requests) > 0): ?>
                    <div class="requests-list">
                        <?php foreach ($pending_requests as $request): ?>
                            <div class="request-item" data-user-id="<?php echo $request['id']; ?>">
                                <img src="uploads/<?php echo $request['profile_pic']; ?>" alt="<?php echo htmlspecialchars($request['username']); ?>" class="request-avatar" onerror="this.src='https://placehold.co/50'">
                                <div class="request-info">
                                    <div class="request-name"><?php echo htmlspecialchars($request['full_name']); ?></div>
                                    <div class="request-username">@<?php echo htmlspecialchars($request['username']); ?></div>
                                    <div class="request-time">Hace <?php echo time_elapsed_string($request['created_at']); ?></div>
                                </div>
                                <div class="request-actions">
                                    <button class="btn btn-primary accept-request">Aceptar</button>
                                    <button class="btn btn-secondary decline-request">Rechazar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">✓</div>
                        <h3>No hay solicitudes pendientes</h3>
                        <p>Las solicitudes de amistad aparecerán aquí</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content" id="blocked-users">
                <?php if (count($blocked_users) > 0): ?>
                    <div class="blocked-list">
                        <?php foreach ($blocked_users as $blocked): ?>
                            <div class="blocked-item" data-user-id="<?php echo $blocked['id']; ?>">
                                <img src="uploads/<?php echo $blocked['profile_pic']; ?>" alt="<?php echo htmlspecialchars($blocked['username']); ?>" class="blocked-avatar" onerror="this.src='https://placehold.co/50'">
                                <div class="blocked-info">
                                    <div class="blocked-name"><?php echo htmlspecialchars($blocked['full_name']); ?></div>
                                    <div class="blocked-username">@<?php echo htmlspecialchars($blocked['username']); ?></div>
                                </div>
                                <div class="blocked-actions">
                                    <button class="btn btn-primary unblock-user">Desbloquear</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">−</div>
                        <h3>No hay usuarios bloqueados</h3>
                        <p>Los usuarios que bloquees aparecerán aquí</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
         <!-- Sección de Búsqueda -->
<div class="content-area search-section" id="search-section" style="display: none;">
    <div class="search-hero">
        <h2>Conecta con Sonder</h2>
        <p>Descubre personas extraordinarias y expande tu red de conexiones</p>
        
        <div class="search-container-improved">
            <input type="text" class="search-input-improved" id="user-search-improved" 
                   placeholder="Buscar por usuario, nombre o intereses...">
            <button class="search-btn-improved" id="search-btn-improved">
                <span>Buscar</span>
            </button>
        </div>
        
        <div class="search-suggestions">
            <div class="suggestion-tag" data-search="developers">Developers</div>
            <div class="suggestion-tag" data-search="designers">Designers</div>
            <div class="suggestion-tag" data-search="gamers">Gamers</div>
            <div class="suggestion-tag" data-search="music">Música</div>
            <div class="suggestion-tag" data-search="travel">Viajes</div>
        </div>
    </div>
    
    <div class="search-results-improved" id="search-results-improved">
        <div class="empty-state">
            <div class="empty-icon">◈</div>
            <h3>Explora la Red Sonder</h3>
            <p>Busca usuarios para encontrar conexiones increíbles</p>
            <div class="search-stats">
                <p>Miles de usuarios esperando conocerte</p>
            </div>
        </div>
    </div>
</div>

        <!-- Sección de Destacados -->
        <div class="content-area" id="featured-section" style="display: none;">
            <div class="section-header">
                <h2>Contactos Destacados</h2>
            </div>
            
            <?php if (count($featured_contacts) > 0): ?>
                <div class="featured-list">
                    <?php foreach ($featured_contacts as $featured): ?>
                        <div class="featured-item" data-user-id="<?php echo $featured['id']; ?>">
                            <div class="featured-badge">★</div>
                            <img src="uploads/<?php echo $featured['profile_pic']; ?>" alt="<?php echo htmlspecialchars($featured['username']); ?>" class="featured-avatar" onerror="this.src='https://placehold.co/60'">
                            <div class="featured-info">
                                <div class="featured-name"><?php echo htmlspecialchars($featured['full_name']); ?></div>
                                <div class="featured-username">@<?php echo htmlspecialchars($featured['username']); ?></div>
                            </div>
                            <div class="featured-actions">
                                <button class="featured-action chat-featured" title="Chatear">Chat</button>
                                <button class="featured-action remove-featured" title="Quitar de destacados">Quitar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">★</div>
                    <h3>No hay contactos destacados</h3>
                    <p>Agrega contactos a destacados para verlos aquí</p>
                    <button class="btn btn-primary" onclick="showSection('friends')">Ver amigos</button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sección de Perfil -->
        <?php include 'includes/profile.php';?>
    
    <script src="js/script.js"></script>
</body>
</html>

<?php
// Función auxiliar para mostrar tiempo transcurrido - VERSIÓN CORREGIDA
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calcular semanas manualmente
    $weeks = floor($diff->d / 7);
    $days = $diff->d - $weeks * 7;

    $string = array(
        'y' => 'año',
        'm' => 'mes',
        'd' => 'día',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    );
    
    // Agregar semanas si existen
    if ($weeks > 0) {
        $string['w'] = 'semana';
    }
    
    $result = array();
    foreach ($string as $k => &$v) {
        if ($k === 'w') {
            $value = $weeks;
        } elseif ($k === 'd') {
            $value = $days;
        } else {
            $value = $diff->$k;
        }
        
        if ($value > 0) {
            $v = $value . ' ' . $v . ($value > 1 ? 's' : '');
            $result[] = $v;
        }
    }

    if (!$full) $result = array_slice($result, 0, 1);
    return $result ? implode(', ', $result) . '' : 'justo ahora';
}
?>