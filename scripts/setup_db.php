<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} else {
    echo "Error: .env file not found. Please copy .env.example to .env and configure it.\n";
    exit(1);
}

$connection = strtolower(envValue('DB_CONNECTION', 'mysql'));
$adminUser = envValue('ADMIN_USERNAME', 'admin');
$adminPass = envValue('ADMIN_PASSWORD', 'admin123');

try {
    if ($connection === 'sqlite') {
        $pdo = setupSqlite();
        $databaseLabel = resolveSqlitePath();
    } else {
        $pdo = setupMysql();
        $databaseLabel = envValue('DB_DATABASE', 'personal_nav');
    }

    configureAdminUser($pdo, $adminUser, $adminPass);

    echo "Database setup completed successfully!\n";
    echo "Connection: $connection\n";
    echo "Database: $databaseLabel\n";
    echo "Admin User: $adminUser\n";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}

function setupMysql(): PDO
{
    $host = envValue('DB_HOST', '127.0.0.1');
    $port = envValue('DB_PORT', '3307');
    $username = envValue('DB_USERNAME', 'root');
    $password = envValue('DB_PASSWORD', '');
    $database = envValue('DB_DATABASE', 'personal_nav');

    echo "Connecting to MySQL server...\n";

    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "Creating database '$database' if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");

    importSqlFile($pdo, __DIR__ . '/../database/init.sql', 'mysql');

    return $pdo;
}

function setupSqlite(): PDO
{
    $path = resolveSqlitePath();
    $directory = dirname($path);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    echo "Opening SQLite database '$path'...\n";

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');

    importSqlFile($pdo, __DIR__ . '/../database/init.sqlite.sql', 'sqlite');

    return $pdo;
}

function resolveSqlitePath(): string
{
    $path = envValue('DB_SQLITE_PATH', envValue('DB_DATABASE_PATH', __DIR__ . '/../storage/database/personal_nav.sqlite'));

    if (!str_starts_with($path, '/')) {
        $path = __DIR__ . '/../' . ltrim($path, '/');
    }

    return $path;
}

function importSqlFile(PDO $pdo, string $sqlFile, string $driver): void
{
    if (!file_exists($sqlFile)) {
        echo "Error: $sqlFile not found.\n";
        exit(1);
    }

    echo "Importing database structure from " . basename($sqlFile) . "...\n";

    $sql = file_get_contents($sqlFile);
    $statements = splitSqlStatements($sql);

    foreach ($statements as $statement) {
        if ($driver === 'mysql' && shouldSkipMysqlBootstrapStatement($statement)) {
            continue;
        }

        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            if (isIgnorableSetupError($e)) {
                continue;
            }

            echo "Warning executing statement: " . substr($statement, 0, 80) . "...\n";
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

function splitSqlStatements(string $sql): array
{
    $lines = explode("\n", $sql);
    $cleanSql = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }

        $cleanSql .= $line . "\n";
    }

    return array_values(array_filter(array_map('trim', explode(';', $cleanSql))));
}

function shouldSkipMysqlBootstrapStatement(string $statement): bool
{
    return stripos($statement, 'CREATE DATABASE') === 0 || stripos($statement, 'USE') === 0;
}

function isIgnorableSetupError(PDOException $e): bool
{
    $message = $e->getMessage();

    return str_contains($message, 'already exists')
        || str_contains($message, 'Duplicate key name')
        || (str_contains($message, 'index') && str_contains($message, 'already exists'));
}

function configureAdminUser(PDO $pdo, string $adminUser, string $adminPass): void
{
    echo "Configuring admin user...\n";

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$adminUser]);

    $hash = password_hash($adminPass, PASSWORD_DEFAULT);

    if ($stmt->fetchColumn() > 0) {
        echo "User '$adminUser' already exists. Updating password...\n";
        $update = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
        $update->execute([$hash, $adminUser]);
        return;
    }

    echo "Creating user '$adminUser'...\n";
    $insert = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    $insert->execute([$adminUser, $hash]);
}

function envValue(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    return $value === false || $value === null ? $default : (string) $value;
}
