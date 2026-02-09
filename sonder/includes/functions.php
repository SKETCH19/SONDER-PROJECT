<?php
require_once __DIR__ . '/config.php';

// Buscar usuarios por username
function searchUsers($query, $current_user_id) {
    global $pdo;
    
    // Validar input
    if (empty($query) || strlen($query) < 2) {
        return [];
    }
    
    // Sanitizar búsqueda
    $query = trim($query);
    
    $stmt = $pdo->prepare("
        SELECT id, username, full_name, profile_pic 
        FROM users 
        WHERE (username LIKE ? OR full_name LIKE ?) 
        AND id != ?
        AND is_active = 1
        ORDER BY full_name ASC
        LIMIT 10
    ");
    $stmt->execute(["%$query%", "%$query%", $current_user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Enviar solicitud de amistad
function sendFriendRequest($user_id, $friend_id) {
    global $pdo;
    
    // Validar parámetros
    if ($user_id === $friend_id) {
        return ['success' => false, 'message' => 'No puedes enviarte solicitudes de amistad a ti mismo'];
    }
    
    // Verificar que el usuario amigo exista y esté activo
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$friend_id]);
    if ($stmt->fetchColumn() === false) {
        return ['success' => false, 'message' => 'El usuario no existe'];
    }
    
    // Verificar si ya existe relación
    $stmt = $pdo->prepare("
        SELECT status FROM friends 
        WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
    ");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['status'] === 'pending') {
            return ['success' => false, 'message' => 'Ya existe una solicitud de amistad pendiente'];
        } else if ($existing['status'] === 'accepted') {
            return ['success' => false, 'message' => 'Ya son amigos'];
        } else if ($existing['status'] === 'blocked') {
            return ['success' => false, 'message' => 'Este usuario está bloqueado'];
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
        if ($stmt->execute([$user_id, $friend_id])) {
            logAction('FRIEND_REQUEST', "Solicitud de amistad enviada a usuario ID $friend_id");
            return ['success' => true, 'message' => 'Solicitud de amistad enviada'];
        }
        return ['success' => false, 'message' => 'Error al enviar solicitud'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error en la base de datos'];
    }
}

// Aceptar solicitud de amistad
function acceptFriendRequest($user_id, $friend_id) {
    global $pdo;
    
    // Validar que la solicitud existe
    $stmt = $pdo->prepare("
        SELECT id FROM friends 
        WHERE user_id = ? AND friend_id = ? AND status = 'pending'
    ");
    $stmt->execute([$friend_id, $user_id]);
    if ($stmt->fetchColumn() === false) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_id = ?");
        $result = $stmt->execute([$friend_id, $user_id]);
        
        if ($result) {
            logAction('FRIEND_ACCEPT', "Aceptó solicitud de amistad de usuario ID $friend_id");
        }
        
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Rechazar solicitud de amistad
function rejectFriendRequest($user_id, $friend_id) {
    global $pdo;
    
    // Validar que la solicitud existe
    $stmt = $pdo->prepare("
        SELECT id FROM friends 
        WHERE user_id = ? AND friend_id = ? AND status = 'pending'
    ");
    $stmt->execute([$friend_id, $user_id]);
    if ($stmt->fetchColumn() === false) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM friends WHERE user_id = ? AND friend_id = ?");
        $result = $stmt->execute([$friend_id, $user_id]);
        
        if ($result) {
            logAction('FRIEND_REJECT', "Rechazó solicitud de amistad de usuario ID $friend_id");
        }
        
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Bloquear usuario
function blockUser($user_id, $blocked_id) {
    global $pdo;
    
    // Validar parámetros
    if ($user_id === $blocked_id) {
        return false;
    }
    
    // Verificar que el usuario exista
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$blocked_id]);
        if ($stmt->fetchColumn() === false) {
        return false;
    }
    
    try {
        // Primero verificar si ya existe una relación
        $stmt = $pdo->prepare("SELECT id FROM friends WHERE user_id = ? AND friend_id = ?");
        $stmt->execute([$user_id, $blocked_id]);
        if ($stmt->fetchColumn() !== false) {
            // Actualizar existente
            $stmt = $pdo->prepare("UPDATE friends SET status = 'blocked' WHERE user_id = ? AND friend_id = ?");
        } else {
            // Crear nueva
            $stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'blocked')");
        }
        
        $result = $stmt->execute([$user_id, $blocked_id]);
        
        if ($result) {
            logAction('BLOCK_USER', "Bloqueó usuario ID $blocked_id");
        }
        
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Desbloquear usuario
function unblockUser($user_id, $blocked_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM friends WHERE user_id = ? AND friend_id = ? AND status = 'blocked'");
        $result = $stmt->execute([$user_id, $blocked_id]);
        
        if ($result) {
            logAction('UNBLOCK_USER', "Desbloqueó usuario ID $blocked_id");
        }
        
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Verificar si dos usuarios son amigos
function areFriends($user_id, $friend_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id FROM friends 
        WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))
        AND status = 'accepted'
    ");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    return $stmt->fetchColumn() !== false;
}

// Verificar si usuario está bloqueado
function isUserBlocked($user_id, $blocked_by_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id FROM friends 
        WHERE user_id = ? AND friend_id = ? AND status = 'blocked'
    ");
    $stmt->execute([$blocked_by_id, $user_id]);
    return $stmt->fetchColumn() !== false;
}

// Obtener estado de amistad
function getFriendshipStatus($user_id, $friend_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT status FROM friends 
        WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
    ");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['status'] : null;
}
?>
