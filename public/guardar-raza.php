<?php
/**
 * RUPE — Guardado directo de raza (tabla razas)
 * POST JSON o form-urlencoded. Requiere sesión admin.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(array $payload, int $code = 200): never {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once dirname(__DIR__) . '/app/config/config.php';
    require_once dirname(__DIR__) . '/app/config/database.php';
    require_once dirname(__DIR__) . '/app/helpers/functions.php';
} catch (Throwable $e) {
    out(['success' => false, 'message' => 'Bootstrap: ' . $e->getMessage()], 500);
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
$uid  = (int) ($_SESSION['user_id'] ?? 0);
$adminRole = defined('ROLE_ADMIN') ? ROLE_ADMIN : 'administrador';
$roleNorm = mb_strtolower(trim($role));
$isAdmin = $uid > 0 && (
    $role === $adminRole
    || $roleNorm === 'administrador'
    || $roleNorm === 'admin'
    || str_contains($roleNorm, 'admin')
);
if (!$isAdmin) {
    out([
        'success' => false,
        'message' => 'Debes iniciar sesión como administrador. (sesión: role=' . $role . ', uid=' . $uid . ')',
        'debug_session' => [
            'uid' => $uid,
            'role' => $role,
            'session_name' => session_name(),
            'has_cookie' => isset($_COOKIE[session_name()]),
        ],
    ], 403);
}

// Body
$all = [];
$ct = (string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
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

$nombre = trim((string) ($all['nombre'] ?? ''));
$tamano = trim((string) ($all['tamano'] ?? $all['tamaño'] ?? ''));
$id     = (int) ($all['id'] ?? $all['id_raza'] ?? 0);
$activo = isset($all['activo']) ? (int) (bool) $all['activo'] : 1;

if (mb_strlen($nombre) < 2) {
    out(['success' => false, 'message' => 'Nombre obligatorio (mín. 2). Keys: ' . implode(',', array_keys($all))], 422);
}
if ($tamano === '') {
    out(['success' => false, 'message' => 'Selecciona el tamaño.'], 422);
}

// Normalizar tamaño
$t = mb_strtolower($tamano);
if (str_contains($t, 'mini')) $tamano = 'Miniatura';
elseif (str_contains($t, 'peque') || str_contains($t, 'chico')) $tamano = 'Pequeño';
elseif (str_contains($t, 'median')) $tamano = 'Mediano';
elseif (str_contains($t, 'gigant')) $tamano = 'Gigante';
elseif (str_contains($t, 'grand')) $tamano = 'Grande';

try {
    $pdo = db_connect();
    $dbName = (string) ($pdo->query('SELECT DATABASE()')->fetchColumn() ?: (defined('DB_NAME') ? DB_NAME : ''));

    // Asegurar columnas
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM razas')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $names = [];
        foreach ($cols as $c) {
            $names[strtolower((string) ($c['Field'] ?? ''))] = true;
        }
        if (!isset($names['tamano'])) {
            $pdo->exec("ALTER TABLE razas ADD COLUMN tamano VARCHAR(40) NULL DEFAULT NULL");
        }
        if (!isset($names['especie'])) {
            $pdo->exec("ALTER TABLE razas ADD COLUMN especie VARCHAR(40) NULL DEFAULT 'perro'");
        }
        if (!isset($names['activo'])) {
            $pdo->exec('ALTER TABLE razas ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1');
        }
    } catch (Throwable $eCol) {
        // continuar
    }

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE razas SET nombre = :n, especie = 'perro', tamano = :t, activo = :a WHERE id_raza = :id"
        );
        $stmt->execute(['n' => $nombre, 't' => $tamano, 'a' => $activo, 'id' => $id]);
        $newId = $id;
    } else {
        // ¿existe?
        $chk = $pdo->prepare("SELECT id_raza FROM razas WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:n)) LIMIT 1");
        $chk->execute(['n' => $nombre]);
        $ex = $chk->fetch(PDO::FETCH_ASSOC);
        if ($ex && (int) ($ex['id_raza'] ?? 0) > 0) {
            $newId = (int) $ex['id_raza'];
            $pdo->prepare(
                "UPDATE razas SET nombre = :n, especie = 'perro', tamano = :t, activo = :a WHERE id_raza = :id"
            )->execute(['n' => $nombre, 't' => $tamano, 'a' => $activo, 'id' => $newId]);
        } else {
            $newId = 0;
            try {
                $pdo->prepare(
                    "INSERT INTO razas (nombre, especie, tamano, activo) VALUES (:n, 'perro', :t, :a)"
                )->execute(['n' => $nombre, 't' => $tamano, 'a' => $activo]);
                $newId = (int) $pdo->lastInsertId();
            } catch (Throwable $e1) {
                // ENUM u otras columnas
                try {
                    $pdo->prepare(
                        "INSERT INTO razas (nombre, especie, tamano) VALUES (:n, 'perro', :t)"
                    )->execute(['n' => $nombre, 't' => $tamano]);
                    $newId = (int) $pdo->lastInsertId();
                } catch (Throwable $e2) {
                    try {
                        $pdo->prepare('INSERT INTO razas (nombre) VALUES (:n)')->execute(['n' => $nombre]);
                        $newId = (int) $pdo->lastInsertId();
                        if ($newId > 0) {
                            try {
                                $pdo->prepare('UPDATE razas SET tamano = :t, especie = \'perro\' WHERE id_raza = :id')
                                    ->execute(['t' => $tamano, 'id' => $newId]);
                            } catch (Throwable $e3) {}
                        }
                    } catch (Throwable $e4) {
                        out([
                            'success' => false,
                            'message' => 'INSERT falló: ' . $e4->getMessage(),
                            'database' => $dbName,
                        ], 500);
                    }
                }
            }
        }
    }

    if ($newId <= 0) {
        $chk = $pdo->prepare("SELECT id_raza FROM razas WHERE nombre = :n ORDER BY id_raza DESC LIMIT 1");
        $chk->execute(['n' => $nombre]);
        $ex = $chk->fetch(PDO::FETCH_ASSOC);
        $newId = (int) ($ex['id_raza'] ?? 0);
    }

    $stmt = $pdo->prepare(
        'SELECT id_raza, nombre, especie, tamano, activo, created_at FROM razas WHERE id_raza = :id LIMIT 1'
    );
    $stmt->execute(['id' => $newId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        out([
            'success' => false,
            'message' => 'No se encontró la fila tras INSERT (id=' . $newId . ') en BD ' . $dbName,
            'database' => $dbName,
        ], 500);
    }

    $row['codigo'] = 'RZ-' . str_pad((string) $row['id_raza'], 2, '0', STR_PAD_LEFT);
    $row['especie'] = 'perro';
    $row['_database'] = $dbName;

    out([
        'success' => true,
        'message' => 'Guardado OK en ' . $dbName . ' · id_raza=' . $row['id_raza'],
        'data' => $row,
        'database' => $dbName,
    ], $id > 0 ? 200 : 201);
} catch (Throwable $e) {
    out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
