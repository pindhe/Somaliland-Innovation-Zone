<?php
/**
 * SIZSR - Database connection (PDO Singleton)
 */

declare(strict_types=1);

final class Database
{
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'sizsr_db';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            self::DB_HOST,
            self::DB_PORT,
            self::DB_NAME,
            self::DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
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
