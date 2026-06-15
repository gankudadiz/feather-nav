<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database
{
    private static $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                if (self::isSqlite()) {
                    $path = self::getSqlitePath();
                    $directory = dirname($path);

                    if (!is_dir($directory)) {
                        mkdir($directory, 0775, true);
                    }

                    self::$pdo = new PDO('sqlite:' . $path, null, null, $options);
                    self::$pdo->exec('PRAGMA foreign_keys = ON');
                    self::$pdo->exec('PRAGMA journal_mode = WAL');
                    self::$pdo->exec('PRAGMA busy_timeout = 5000');
                } else {
                    $dsn = sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                        self::env('DB_HOST', '127.0.0.1'),
                        self::env('DB_PORT', '3307'),
                        self::env('DB_DATABASE', 'personal_nav')
                    );

                    self::$pdo = new PDO(
                        $dsn,
                        self::env('DB_USERNAME', 'root'),
                        self::env('DB_PASSWORD', ''),
                        $options
                    );
                }
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                if (self::env('APP_DEBUG', 'false') === 'true') {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('Database connection failed');
            }
        }

        return self::$pdo;
    }

    public static function getConnectionName(): string
    {
        return strtolower(self::env('DB_CONNECTION', 'mysql'));
    }

    public static function isSqlite(): bool
    {
        return self::getConnectionName() === 'sqlite';
    }

    public static function getSqlitePath(): string
    {
        $path = self::env(
            'DB_SQLITE_PATH',
            self::env('DB_DATABASE_PATH', dirname(__DIR__) . '/storage/database/personal_nav.sqlite')
        );

        if (!str_starts_with($path, '/')) {
            $path = dirname(__DIR__) . '/' . ltrim($path, '/');
        }

        return $path;
    }

    public static function getDatabaseLabel(): string
    {
        return self::isSqlite()
            ? self::getSqlitePath()
            : self::env('DB_DATABASE', 'personal_nav');
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false || $value === null ? $default : (string) $value;
    }
}
