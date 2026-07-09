<?php
/**
 * SIZSR - Database connection (PDO Singleton)
 * Reads credentials from environment variables on Render/production;
 * falls back to XAMPP defaults for local development.
 */

declare(strict_types=1);

final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    private static function cfg(string $key, string $default): string
    {
        $env = getenv($key);
        return ($env !== false && $env !== '') ? (string)$env : $default;
    }

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = self::cfg('DB_HOST', '127.0.0.1');
        $port = self::cfg('DB_PORT', '3306');
        $name = self::cfg('DB_NAME', 'sizsr_db');
        $user = self::cfg('DB_USER', 'root');
        $pass = self::cfg('DB_PASS', '');
        $charset = self::cfg('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            if (defined('SIZSR_DEBUG') && SIZSR_DEBUG) {
                die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
            }
            http_response_code(500);
            die('A database error occurred. Please try again later.');
        }

        return self::$instance;
    }
}
/**
 * Shorthand helper to get the shared PDO instance.
 */
function db(): PDO
{
    return Database::getConnection();
}
