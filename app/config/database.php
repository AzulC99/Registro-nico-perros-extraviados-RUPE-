<?php
/**
 * RUPE CDMX — Conexión PDO (Hostinger / PHP 8.3)
 * app/config/database.php
 */

declare(strict_types=1);

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
];

// MYSQL_ATTR_INIT_COMMAND = 1002 — no usar la constante si no está definida en el build
if (extension_loaded('pdo_mysql')) {
    $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    $pdoOptions[1002] = "SET NAMES '{$charset}' COLLATE utf8mb4_unicode_ci";
}

if (!defined('DB_OPTIONS')) {
    define('DB_OPTIONS', $pdoOptions);
}

if (!defined('DB_DSN')) {
    define(
        'DB_DSN',
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4')
    );
}

function db_connect(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
    return $pdo;
}
