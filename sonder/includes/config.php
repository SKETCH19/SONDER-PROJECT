<?php
// Configuración de seguridad
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
define('DB_PATH', __DIR__ . '/../sonder.db');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 900); // 15 minutos
define('MIN_AGE', 18); 

// Iniciar sesión con configuración segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'samesite' => 'Strict'
    ]);
    session_start();
}
//conexion a la base de datos MySQL
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sonder_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name TEXT NOT NULL,
            username VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            birth_date DATE NOT NULL,
            country TEXT NOT NULL,
            phone TEXT NOT NULL,
            profile_pic TEXT DEFAULT 'default.png',
            is_active INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message LONGTEXT NOT NULL,
            message_type TEXT DEFAULT 'text',
            file_path TEXT,
            is_read INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS friends (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            friend_id INT NOT NULL,
            status TEXT DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(user_id, friend_id)
        )",
        "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action TEXT NOT NULL,
            details TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )"
    ];
    
    // Crear índices para mejorar performance
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_messages_sender ON messages(sender_id)",
        "CREATE INDEX IF NOT EXISTS idx_messages_receiver ON messages(receiver_id)",
        "CREATE INDEX IF NOT EXISTS idx_messages_created ON messages(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_friends_user ON friends(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_friends_friend ON friends(friend_id)",
        "CREATE INDEX IF NOT EXISTS idx_friends_status ON friends(status)",
        "CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_logs(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at)"
    ];
    
    foreach($tables as $sql) {
        $pdo->exec($sql);
    }
    
    foreach($indices as $sql) {
        $pdo->exec($sql);
    }

} catch(PDOException $e) {
    if (PHP_SAPI === 'cli') {
        echo "ERROR: No se pudo conectar a la base de datos.\n";
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
    }
    exit;
}

// Conexión a la base de datos SQLite (comentada)
/* try {
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Habilitar foreign keys en SQLite
    $pdo->exec("PRAGMA foreign_keys = ON");
    
    // Crear tablas si no existen
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            birth_date DATE NOT NULL,
            country TEXT NOT NULL,
            phone TEXT NOT NULL,
            profile_pic TEXT DEFAULT 'default.png',
            is_active INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            receiver_id INTEGER NOT NULL,
            message TEXT NOT NULL,
            message_type TEXT DEFAULT 'text',
            file_path TEXT,
            is_read INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS friends (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            friend_id INTEGER NOT NULL,
            status TEXT DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(user_id, friend_id)
        )",
        "CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            details TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )"
    ];
    
    
    // Crear índices para mejorar performance SQL1ite (comentada)
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_messages_sender ON messages(sender_id)",
        "CREATE INDEX IF NOT EXISTS idx_messages_receiver ON messages(receiver_id)",
        "CREATE INDEX IF NOT EXISTS idx_messages_created ON messages(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_friends_user ON friends(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_friends_friend ON friends(friend_id)",
        "CREATE INDEX IF NOT EXISTS idx_friends_status ON friends(status)",
        "CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_logs(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at)"
    ]; 
    
    foreach($tables as $sql) {
        $pdo->exec($sql);
    }
    
    foreach($indices as $sql) {
        $pdo->exec($sql);
    }
} catch(PDOException $e) {
    if (PHP_SAPI === 'cli') {
        echo "ERROR: No se pudo conectar a la base de datos.\n";
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
    }
    exit;
}
*/
// Función para registrar acciones
function logAction($action, $details = null) {
    global $pdo;
    try {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $action, $details, $ip_address, $user_agent]);
    } catch(PDOException $e) {
        // No interrumpir si el log falla
        error_log("Error logging action: " . $e->getMessage());
    }
}

// Función para sanitizar output
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Función para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para validar edad mínima
function validateAge($birth_date) {
    $age = floor((strtotime('today') - strtotime($birth_date)) / (60 * 60 * 24 * 365.25));
    return $age >= MIN_AGE;
}

// Función para validar username
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Función para validar password
function validatePassword($password) {
    return strlen($password) >= 8;
}
?>