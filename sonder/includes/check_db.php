<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sonder_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mostrar columnas de users
    echo "=== Columnas actuales de la tabla users ===\n";
    $result = $pdo->query('DESCRIBE users');
    $columns = $result->fetchAll();
    
    if (empty($columns)) {
        echo "La tabla users no existe.\n";
        echo "Recreando tablas...\n";
        
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
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
        )";
        
        $pdo->exec($sql);
        echo "✓ Tabla users creada exitosamente\n";
    } else {
        foreach($columns as $col) {
            echo $col['Field'] . " - " . $col['Type'] . "\n";
        }
        
        // Verificar si existe la columna is_active
        $hasIsActive = false;
        foreach($columns as $col) {
            if ($col['Field'] === 'is_active') {
                $hasIsActive = true;
                break;
            }
        }
        
        if (!$hasIsActive) {
            echo "\n⚠️ Falta la columna 'is_active'. Agregándola...\n";
            $pdo->exec("ALTER TABLE users ADD COLUMN is_active INT DEFAULT 1 AFTER profile_pic");
            echo "✓ Columna 'is_active' agregada exitosamente\n";
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
