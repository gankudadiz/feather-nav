<?php

declare(strict_types=1);

namespace App;

use PDO;

class Database
{
    private static $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'] ?? '127.0.0.1',
                $_ENV['DB_PORT'] ?? '3306',
                $_ENV['DB_DATABASE'] ?? 'personal_nav'
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$pdo = new PDO(
                    $dsn,
                    $_ENV['DB_USERNAME'] ?? 'root',
                    $_ENV['DB_PASSWORD'] ?? '',
                    $options
                );
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('Database connection failed');
            }
        }

        return self::$pdo;
    }
}