<?php
/**
 * RUPE — Guardar raza (endpoint directo, sin router)
 * POST JSON o form: nombre, tamano, id (opcional)
 * Solo administradores con sesión activa.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(bool $ok, $data, string $msg, int $code = 200): void
{
    http_response_code($code);
    echo json_encode([
        'success' => $ok,
        'message' => $msg,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once dirname(__DIR__) . '/app/config/config.php';
    require_once dirname(__DIR__) . '/app/config/database.php';
    require_once dirname(__DIR__) . '/app/helpers/functions.php';
} catch (Throwable $e) {
    out(false, null, 'Bootstrap: ' . $e->getMessage(), 500);
}

// Misma sesión que el resto de la app (RUPESESSID)
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (defined('SESSION_NAME')) {
        session_name(SESSION_NAME);
    } else {
        session_name('RUPESESSID');
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @session_start();
}

$role = (string) ($_SESSION['user_role'] ?? '');
$uid = (int) ($_SESSION['user_id'] ?? 0);
$adminRole = defined('ROLE_ADMIN') ? ROLE_ADMIN : 'administrador';
$roleNorm = mb_strtolower(trim($role));
$isAdmin = $uid > 0 && (
    $role === $adminRole
    || $roleNorm === 'administrador'
    || $roleNorm === 'admin'
    || str_contains($roleNorm, 'admin')
);
if (!$isAdmin) {
    out(false, [
        'uid' => $uid,
        'role' => $role,
        'session_name' => session_name(),
    ], 'Debes iniciar sesión como administrador. (role=' . $role . ', uid=' . $uid . ')', 403);
}

// Body
$all = [];
$ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
$raw = file_get_contents('php://input');
if (is_string($raw) && $raw !== '' && stripos($ct, 'json') !== false) {
    $dec = json_decode($raw, true);
    if (is_array($dec)) {
        $all = $dec;
    }
}
if ($all === [] && !empty($_POST)) {
    $all = $_POST;
}
if ($all === [] && is_string($raw) && $raw !== '') {
    $dec = json_decode($raw, true);
    if (is_array($dec)) {
        $all = $dec;
    } else {
        parse_str($raw, $parsed);
        if (is_array($parsed)) {
            $all = $parsed;
        }
    }
}

$nombre = trim((string) ($all['nombre'] ?? $all['nombre_raza'] ?? ''));
$tamano = trim((string) ($all['tamano'] ?? $all['tamaño'] ?? ''));
$id = (int) ($all['id'] ?? $all['id_raza'] ?? 0);

// Normalizar tamaño
$t = mb_strtolower($tamano);
if (str_contains($t, 'mini')) {
    $tamano = 'Miniatura';
} elseif (str_contains($t, 'peque') || str_contains($t, 'chico')) {
    $tamano = 'Pequeño';
} elseif (str_contains($t, 'median')) {
    $tamano = 'Mediano';
} elseif (str_contains($t, 'gigant')) {
    $tamano = 'Gigante';
} elseif (str_contains($t, 'grand')) {
    $tamano = 'Grande';
}

if (mb_strlen($nombre) < 2) {
    out(false, ['keys' => array_keys($all)], 'Nombre obligatorio (mín. 2 caracteres).', 422);
}
if ($tamano === '') {
    out(false, null, 'Selecciona el tamaño.', 422);
}

try {
    $pdo = db_connect();
    $dbName = (string) ($pdo->query('SELECT DATABASE()')->fetchColumn() ?: (defined('DB_NAME') ? DB_NAME : ''));

    // Asegurar columna tamano
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM razas LIKE 'tamano'")->fetchAll();
        if (!$cols) {
            $pdo->exec("ALTER TABLE razas ADD COLUMN tamano VARCHAR(40) NULL DEFAULT NULL");
        }
    } catch (Throwable $e) {
        // continuar
    }

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE razas SET nombre = :n, especie = 'perro', tamano = :t, activo = 1 WHERE id_raza = :id"
        );
        $stmt->execute(['n' => $nombre, 't' => $tamano, 'id' => $id]);
        $rid = $id;
    } else {
        // ¿Existe por nombre+perro?
        $chk = $pdo->prepare(
            "SELECT id_raza FROM razas WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:n)) AND (especie = 'perro' OR especie IS NULL OR especie = '') LIMIT 1"
        );
        $chk->execute(['n' => $nombre]);
        $ex = $chk->fetch(PDO::FETCH_ASSOC);
        if ($ex && (int) $ex['id_raza'] > 0) {
            $rid = (int) $ex['id_raza'];
            $pdo->prepare(
                "UPDATE razas SET nombre = :n, especie = 'perro', tamano = :t, activo = 1 WHERE id_raza = :id"
            )->execute(['n' => $nombre, 't' => $tamano, 'id' => $rid]);
        } else {
            try {
                $ins = $pdo->prepare(
                    "INSERT INTO razas (nombre, especie, tamano, activo) VALUES (:n, 'perro', :t, 1)"
                );
                $ins->execute(['n' => $nombre, 't' => $tamano]);
                $rid = (int) $pdo->lastInsertId();
            } catch (Throwable $eIns) {
                // Sin columna tamano
                $ins = $pdo->prepare(
                    "INSERT INTO razas (nombre, especie, activo) VALUES (:n, 'perro', 1)"
                );
                $ins->execute(['n' => $nombre]);
                $rid = (int) $pdo->lastInsertId();
                try {
                    $pdo->prepare('UPDATE razas SET tamano = :t WHERE id_raza = :id')
                        ->execute(['t' => $tamano, 'id' => $rid]);
                } catch (Throwable $e2) {
                }
            }
        }
    }

    if ($rid <= 0) {
        out(false, ['_database' => $dbName], 'No se obtuvo id_raza tras INSERT.', 500);
    }

    $stmt = $pdo->prepare(
        'SELECT id_raza, nombre, especie, tamano, activo, created_at FROM razas WHERE id_raza = :id LIMIT 1'
    );
    $stmt->execute(['id' => $rid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        out(false, ['_database' => $dbName, 'id_intentado' => $rid], 'INSERT ok pero SELECT vacío en BD ' . $dbName, 500);
    }

    $row['codigo'] = 'RZ-' . str_pad((string) $row['id_raza'], 2, '0', STR_PAD_LEFT);
    $row['especie'] = 'perro';
    $row['_database'] = $dbName;

    out(true, $row, 'Guardado en razas · BD ' . $dbName . ' · id=' . $row['id_raza'], $id > 0 ? 200 : 201);
} catch (Throwable $e) {
    out(false, null, 'Error SQL: ' . $e->getMessage(), 500);
}
