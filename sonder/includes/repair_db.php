<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sonder_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Verificando todas las columnas ===\n";
    $result = $pdo->query('DESCRIBE users');
    $columns = $result->fetchAll();
    
    $columnNames = array_map(function($col) { return $col['Field']; }, $columns);
    
    // Agregar columnas faltantes
    if (!in_array('is_active', $columnNames)) {
        echo "Agregando columna 'is_active'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active INT DEFAULT 1");
        echo "✓ is_active agregada\n";
    }
    
    if (!in_array('updated_at', $columnNames)) {
        echo "Agregando columna 'updated_at'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✓ updated_at agregada\n";
    }
    
    // Crear las otras tablas si no existen
    $tables = [
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
    
    foreach($tables as $sql) {
        $pdo->exec($sql);
    }
    
    echo "✓ Todas las tablas creadas correctamente\n";
    
    // Crear índices
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
    
    foreach($indices as $sql) {
        $pdo->exec($sql);
    }
    
    echo "✓ Índices creados correctamente\n";
    
    // Mostrar estructura final
    echo "\n=== Estructura final de la tabla users ===\n";
    $result = $pdo->query('DESCRIBE users');
    $columns = $result->fetchAll();
    foreach($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
