<?php
// Database configuration
define('DB_HOST', 'fdb1034.awardspace.net');
define('DB_USER', '4669157_educ');
define('DB_PASS', '1lovemyself');
define('DB_NAME', '4669157_educ');

// Admin access password (for initial access to registration)
define('ADMIN_ACCESS_PASSWORD', 'admin123');

// Website configuration
define('SITE_NAME', 'EDUSYNCHUB');
define('SITE_URL', 'http://edusynchub.mywebcommunity.org/');

// Security settings
define('MAX_LOGIN_ATTEMPTS', 5); // Maximum allowed login attempts
define('LOGIN_ATTEMPT_TIMEOUT', 15); // Minutes to lock after too many attempts

// Establish database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false, // Set to true if using HTTPS
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Function to initialize ALL database tables (both user and admin)
function initializeAllTables($pdo) {
    // Your existing user/quiz tables
    $userTables = [
        // Your existing user table(s)
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Your existing quiz table(s)
        "CREATE TABLE IF NOT EXISTS quiz_questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question TEXT NOT NULL,
            category VARCHAR(255) NOT NULL,
            difficulty ENUM('Easy','Medium','Hard') NOT NULL,
            options JSON NOT NULL,
            correct_answer_index INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Add any other existing tables you have here...
    ];
    
    // NEW Admin tables (add these)
    $adminTables = [
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            full_name VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL DEFAULT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            role ENUM('superadmin','admin','moderator') DEFAULT 'admin'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS admin_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS admin_password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
极           admin_id INT NOT NULL,
            reset_token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8极mb4",
        
        "CREATE TABLE IF NOT EXISTS admin_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    // Create all tables
    $allTables = array_merge($userTables, $adminTables);
    
    foreach ($allTables as $query) {
        try {
            $pdo->exec($query);
        } catch (PDOException $e) {
            // Log error but continue with other tables
            error_log("Table creation error: " . $e->getMessage());
        }
    }
    
    // Create initial superadmin account if it doesn't exist
    try {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = 'superadmin'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO admins (username, password, email, full_name, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'superadmin', 
                $hashedPassword, 
                'superadmin@edusynchub.com', 
                'System Superadmin', 
                'superadmin'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Superadmin creation error: " . $e->getMessage());
    }
}

// Include functions (your existing functions.php)
require_once 'functions.php';

// Auto-initialize tables if visiting admin page
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
    initializeAllTables($pdo);
}
?>