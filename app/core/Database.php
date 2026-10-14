<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;
    private static string $connectedDb = '';

    private function __construct() {}
    private function __clone() {}

    public static function getConnectedName(): string
    {
        return self::$connectedDb !== ''
            ? self::$connectedDb
            : (defined('DB_NAME') ? (string) DB_NAME : '');
    }

    public static function getInstance(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $options = defined('DB_OPTIONS') ? DB_OPTIONS : [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $dsn = defined('DB_DSN')
            ? DB_DSN
            : ('mysql:host=' . (defined('DB_HOST') ? DB_HOST : 'localhost')
                . ';dbname=' . (defined('DB_NAME') ? DB_NAME : '')
                . ';charset=' . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'));

        try {
            self::$instance = new PDO(
                $dsn,
                defined('DB_USER') ? DB_USER : '',
                defined('DB_PASS') ? DB_PASS : '',
                $options
            );
            self::$connectedDb = defined('DB_NAME') ? (string) DB_NAME : '';
            return self::$instance;
        } catch (PDOException $e) {
            // Intentar BD alternativa una sola vez
            if (defined('DB_NAME_ALT') && DB_NAME_ALT !== '' && defined('DB_HOST')) {
                try {
                    $dsn2 = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME_ALT . ';charset=' . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');
                    self::$instance = new PDO($dsn2, DB_USER, DB_PASS, $options);
                    self::$connectedDb = (string) DB_NAME_ALT;
                    return self::$instance;
                } catch (PDOException $e2) {
                    if (function_exists('app_log')) {
                        app_log('error', 'DB fail primary+alt', ['p' => $e->getMessage(), 'a' => $e2->getMessage()]);
                    }
                    throw $e;
                }
            }
            if (function_exists('app_log')) {
                app_log('error', 'DB connection failed', ['msg' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    public static function begin(): void
    {
        self::getInstance()->beginTransaction();
    }

    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    public static function rollBack(): void
    {
        if (self::getInstance()->inTransaction()) {
            self::getInstance()->rollBack();
        }
    }
}
