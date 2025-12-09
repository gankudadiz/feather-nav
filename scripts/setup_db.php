<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} else {
    echo "Error: .env file not found. Please copy .env.example to .env and configure it.\n";
    exit(1);
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? 'personal_nav';

$adminUser = $_ENV['ADMIN_USERNAME'] ?? 'admin';
$adminPass = $_ENV['ADMIN_PASSWORD'] ?? 'admin123';

echo "Connecting to MySQL server...\n";

try {
    // Connect without database selected first
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    echo "Creating database '$database' if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Select the database
    $pdo->exec("USE `$database`");
    
    // Read init.sql
    $sqlFile = __DIR__ . '/../database/init.sql';
    if (!file_exists($sqlFile)) {
        echo "Error: database/init.sql not found.\n";
        exit(1);
    }
    
    echo "Importing database structure...\n";
    $sql = file_get_contents($sqlFile);
    
    // Remove comments to avoid issues with parsing
    $lines = explode("\n", $sql);
    $cleanSql = "";
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && strpos($line, '--') !== 0) {
            $cleanSql .= $line . "\n";
        }
    }
    
    // Split by semicolon
    $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
    
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            // Skip CREATE DATABASE and USE if they are present (just in case)
            if (stripos($stmt, 'CREATE DATABASE') === 0 || stripos($stmt, 'USE') === 0) {
                continue;
            }
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // Ignore harmless errors
                $code = $e->getCode();
                $msg = $e->getMessage();
                
                // 42S01: Base table or view already exists
                // 42000: Syntax error or access violation (often for duplicate keys in MySQL)
                // 1061: Duplicate key name
                // 1062: Duplicate entry
                
                $ignore = false;
                if (strpos($msg, 'already exists') !== false) $ignore = true;
                if (strpos($msg, 'Duplicate key name') !== false) $ignore = true;
                
                if (!$ignore) {
                    echo "Warning executing statement: " . substr($stmt, 0, 50) . "...\n";
                    echo "Error: " . $msg . "\n";
                }
            }
        }
    }
    
    // Insert/Update Admin User
    echo "Configuring admin user...\n";
    
    // Check if users table exists (it should now)
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$adminUser]);
    
    if ($stmt->fetchColumn() > 0) {
        echo "User '$adminUser' already exists. Updating password...\n";
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update->execute([$hash, $adminUser]);
    } else {
        echo "Creating user '$adminUser'...\n";
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $insert->execute([$adminUser, $hash]);
    }
    
    echo "Database setup completed successfully!\n";
    echo "Database: $database\n";
    echo "Admin User: $adminUser\n";
    
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
