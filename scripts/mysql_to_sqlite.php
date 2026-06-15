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

$fresh = in_array('--fresh', $argv, true);
$tables = ['categories', 'links', 'users', 'audit_logs', 'settings'];

try {
    $mysql = createMysqlConnection();
    $sqlite = createSqliteConnection();

    importSqliteSchema($sqlite);

    if (targetHasData($sqlite, $tables)) {
        if (!$fresh) {
            echo "Error: target SQLite database already contains data. Re-run with --fresh to clear target tables first.\n";
            exit(1);
        }

        clearTargetTables($sqlite, array_reverse($tables));
    }

    $sqlite->beginTransaction();

    foreach ($tables as $table) {
        migrateTable($mysql, $sqlite, $table);
    }

    $sqlite->commit();

    echo "Migration completed successfully.\n";
    echo "Target SQLite: " . resolveSqlitePath() . "\n";
} catch (Throwable $e) {
    if (isset($sqlite) && $sqlite instanceof PDO && $sqlite->inTransaction()) {
        $sqlite->rollBack();
    }

    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

function createMysqlConnection(): PDO
{
    $host = envValue('DB_HOST', '127.0.0.1');
    $port = envValue('DB_PORT', '3307');
    $database = envValue('DB_DATABASE', 'personal_nav');
    $username = envValue('DB_USERNAME', 'root');
    $password = envValue('DB_PASSWORD', '');

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}

function createSqliteConnection(): PDO
{
    $path = resolveSqlitePath();
    $directory = dirname($path);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');

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

function importSqliteSchema(PDO $sqlite): void
{
    $schema = file_get_contents(__DIR__ . '/../database/init.sqlite.sql');
    if ($schema === false) {
        throw new RuntimeException('database/init.sqlite.sql not found.');
    }

    foreach (splitSqlStatements($schema) as $statement) {
        $sqlite->exec($statement);
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

function targetHasData(PDO $sqlite, array $tables): bool
{
    foreach ($tables as $table) {
        $count = (int) $sqlite->query('SELECT COUNT(*) FROM "' . $table . '"')->fetchColumn();
        if ($count > 0) {
            return true;
        }
    }

    return false;
}

function clearTargetTables(PDO $sqlite, array $tables): void
{
    $sqlite->exec('PRAGMA foreign_keys = OFF');

    foreach ($tables as $table) {
        $sqlite->exec('DELETE FROM "' . $table . '"');
    }

    $sqlite->exec("DELETE FROM sqlite_sequence WHERE name IN ('categories', 'links', 'users', 'audit_logs', 'settings')");
    $sqlite->exec('PRAGMA foreign_keys = ON');
}

function migrateTable(PDO $mysql, PDO $sqlite, string $table): void
{
    $rows = $mysql->query('SELECT * FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "Skipped $table: 0 rows.\n";
        return;
    }

    $columns = array_keys($rows[0]);
    $quotedColumns = array_map(fn (string $column): string => '"' . str_replace('"', '""', $column) . '"', $columns);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $insert = $sqlite->prepare(
        'INSERT INTO "' . $table . '" (' . implode(', ', $quotedColumns) . ') VALUES (' . $placeholders . ')'
    );

    foreach ($rows as $row) {
        $insert->execute(array_values($row));
    }

    echo "Migrated $table: " . count($rows) . " rows.\n";
}

function envValue(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    return $value === false || $value === null ? $default : (string) $value;
}
