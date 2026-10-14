<?php
/**
 * RUPE CDMX — Funciones auxiliares globales
 * app/helpers/functions.php
 * PHP 8.3.31 · sin Composer
 */

declare(strict_types=1);

/**
 * Respuesta JSON estandarizada
 */
function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Éxito JSON
 */
function json_ok(mixed $data = null, string $message = 'OK', int $code = 200): never
{
    if ($data === null) {
        $data = [];
    } elseif (is_array($data) && function_exists('sanitize_data')) {
        $data = sanitize_data($data, true);
    }
    json_response([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ], $code);
}

/**
 * Error JSON
 */
function json_error(string $message, int $code = 400, mixed $errors = null): never
{
    $payload = [
        'success' => false,
        'message' => $message,
    ];
    if ($errors !== null) {
        $payload['errors'] = $errors;
    }
    json_response($payload, $code);
}

/**
 * Generar token CSRF
 */
function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

/**
 * Validar token CSRF
 */
function csrf_validate(?string $token): bool
{
    if ($token === null || $token === '') {
        return false;
    }
    $session = $_SESSION[CSRF_TOKEN_KEY] ?? '';
    return hash_equals($session, $token);
}

/**
 * Sanitizar string
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Solo dígitos / teléfono MX
 */
function normalize_phone(?string $phone): string
{
    $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
    if (str_starts_with($digits, '52') && strlen($digits) === 12) {
        $digits = substr($digits, 2);
    }
    return $digits;
}

/**
 * Validar email
 */
function is_valid_email(?string $email): bool
{
    return filter_var((string) $email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash de contraseña
 */
function hash_password(string $plain): string
{
    return password_hash($plain, PASSWORD_ALGO, PASSWORD_OPTIONS);
}

/**
 * Verificar contraseña
 */
function verify_password(string $plain, string $hash): bool
{
    return password_verify($plain, $hash);
}

/**
 * Generar folio único de reporte (RUPE-YYYYMMDD-XXXX)
 */
function generate_folio(): string
{
    return 'RUPE-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
}

/**
 * IP del cliente (detrás de proxy Hostinger)
 */
function client_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', (string) $_SERVER[$key])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * User-Agent truncado
 */
function client_ua(): string
{
    return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

/**
 * Redirección
 */
function redirect(string $path): never
{
    $url = str_starts_with($path, 'http') ? $path : rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    header('Location: ' . $url, true, 302);
    exit;
}

/**
 * Requerir login (web)
 */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('/auth/login');
    }
}

/**
 * Requerir rol
 */
function require_role(string ...$roles): void
{
    require_login();
    $role = $_SESSION['user_role'] ?? '';
    if (!in_array($role, $roles, true)) {
        json_error('Acceso denegado', 403);
    }
}

/**
 * Rate limit simple por IP (archivo)
 */
function rate_limit_check(string $key, int $max = API_RATE_LIMIT, int $window = API_RATE_WINDOW): bool
{
    $dir = STORAGE_PATH . '/rate';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    $file = $dir . '/' . hash('sha256', $key . client_ip()) . '.json';
    $now  = time();
    $data = ['count' => 0, 'start' => $now];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }
        if (($now - (int) $data['start']) > $window) {
            $data = ['count' => 0, 'start' => $now];
        }
    }

    $data['count'] = (int) $data['count'] + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);

    return $data['count'] <= $max;
}

/**
 * Log simple
 */
function app_log(string $level, string $message, array $context = []): void
{
    $dir = LOG_PATH;
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    $line = sprintf(
        "[%s] %s %s %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message,
        $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
    );
    @file_put_contents($dir . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
}


/**
 * URL absoluta de la app
 */
function url(string $path = ''): string
{
    $base = rtrim(defined('APP_URL') ? APP_URL : '', '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base . '/' : $base . '/' . $path;
}

/**
 * URL de asset público
 */
function asset(string $path): string
{
    $base = defined('ASSETS_URL') ? ASSETS_URL : (rtrim(APP_URL, '/') . '/assets');
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Campo hidden CSRF para formularios
 */
function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
}


/**
 * Guardar imagen subida (archivo $_FILES o data-URL base64).
 * Devuelve URL pública relativa o absoluta, o null si falla.
 */
function save_mascota_image(?array $file = null, ?string $base64 = null): ?string
{
    $dir = defined('UPLOAD_PATH') ? UPLOAD_PATH : (dirname(__DIR__, 2) . '/public/assets/uploads');
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    if (!is_dir($dir) || !is_writable($dir)) {
        app_log('error', 'UPLOAD_PATH no escribible', ['dir' => $dir]);
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $maxBytes = 5 * 1024 * 1024; // 5 MB

    // --- Archivo multipart ---
    if ($file && is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $tmp = $file['tmp_name'] ?? '';
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp) || $size <= 0 || $size > $maxBytes) {
            return null;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!isset($allowed[$mime])) {
            return null;
        }
        $ext = $allowed[$mime];
        $name = 'mascota_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }
        @chmod($dest, 0644);
        return '/assets/uploads/' . $name;
    }

    // --- Base64 / data-URL ---
    if ($base64 && is_string($base64) && str_starts_with($base64, 'data:image/')) {
        if (!preg_match('#^data:(image/(?:jpeg|png|gif|webp));base64,(.+)$#i', $base64, $m)) {
            return null;
        }
        $mime = strtolower($m[1]);
        if (!isset($allowed[$mime])) {
            return null;
        }
        $bin = base64_decode($m[2], true);
        if ($bin === false || strlen($bin) < 32 || strlen($bin) > $maxBytes) {
            return null;
        }
        $ext = $allowed[$mime];
        $name = 'mascota_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (@file_put_contents($dest, $bin) === false) {
            return null;
        }
        @chmod($dest, 0644);
        return '/assets/uploads/' . $name;
    }

    return null;
}

/**
 * Folio único garantizado (reintenta si colisiona)
 */
function generate_folio_unique(?\PDO $db = null): string
{
    $db = $db ?? \App\Core\Database::getInstance();
    for ($i = 0; $i < 8; $i++) {
        $folio = generate_folio();
        $stmt = $db->prepare('SELECT 1 FROM mascotas WHERE folio = :folio LIMIT 1');
        $stmt->execute(['folio' => $folio]);
        if (!$stmt->fetchColumn()) {
            return $folio;
        }
    }
    return 'RUPE-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}


/**
 * URL de foto de mascota lista para <img src>.
 * - Rutas locales /assets/uploads/...
 * - HTTPS externos (demo)
 * - Si vacío o inválido: placeholder según especie (nunca null ni "null")
 */
function mascota_foto_src(?string $fotoUrl, string $especie = 'perro'): string
{
    $foto = trim((string) ($fotoUrl ?? ''));
    if ($foto === '' || strcasecmp($foto, 'null') === 0 || strcasecmp($foto, 'undefined') === 0) {
        $foto = '';
    }
    if ($foto !== '') {
        // Local uploads
        if (str_starts_with($foto, '/assets/uploads/') || str_starts_with($foto, 'assets/uploads/')) {
            return str_starts_with($foto, '/') ? $foto : '/' . $foto;
        }
        // Placeholder local
        if (str_starts_with($foto, '/assets/img/') || str_starts_with($foto, 'assets/img/')) {
            return str_starts_with($foto, '/') ? $foto : '/' . $foto;
        }
        // Solo nombre de archivo subido
        if (preg_match('#^mascota_[a-zA-Z0-9._-]+\.(jpe?g|png|gif|webp)$#i', $foto)) {
            return '/assets/uploads/' . $foto;
        }
        if (str_starts_with($foto, 'uploads/')) {
            return '/assets/' . ltrim($foto, '/');
        }
        // URL absoluta http(s)
        if (preg_match('#^https?://#i', $foto)) {
            return $foto;
        }
        // Data URI
        if (str_starts_with($foto, 'data:image/')) {
            return $foto;
        }
        // Cualquier otra ruta relativa bajo public
        if (str_starts_with($foto, '/')) {
            return $foto;
        }
    }
    $esp = strtolower($especie);
    if (str_contains($esp, 'gato') || $esp === 'felino') {
        return '/assets/img/placeholder-gato.svg';
    }
    if (str_contains($esp, 'perro') || $esp === 'canino') {
        return '/assets/img/placeholder-perro.svg';
    }
    return '/assets/img/placeholder-mascota.svg';
}

/**
 * SELECT de foto por id_mascota (consulta directa)
 */
function mascota_foto_by_id(int $idMascota): string
{
    if ($idMascota <= 0) {
        return mascota_foto_src(null);
    }
    try {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare('SELECT foto_url, especie FROM mascotas WHERE id_mascota = :id AND activo = 1 LIMIT 1');
        $stmt->execute(['id' => $idMascota]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return mascota_foto_src(null);
        }
        return mascota_foto_src(
            isset($row['foto_url']) ? (string) $row['foto_url'] : null,
            (string) ($row['especie'] ?? 'perro')
        );
    } catch (\Throwable $e) {
        return mascota_foto_src(null);
    }
}

/**
 * Convierte null a valores seguros para JSON/vistas (sin errores en frontend).
 * - null string-like → ""
 * - null numérico (ids opcionales) se dejan como 0 solo si $zeroIds=true
 */
function sanitize_value(mixed $value, bool $zeroIds = false): mixed
{
    if ($value === null) {
        return $zeroIds ? 0 : '';
    }
    if (is_string($value)) {
        $trim = trim($value);
        if ($trim === '' || strcasecmp($trim, 'null') === 0 || strcasecmp($trim, 'undefined') === 0) {
            return '';
        }
        return $value;
    }
    if (is_array($value)) {
        return sanitize_data($value, $zeroIds);
    }
    return $value;
}

/**
 * Recorre un array/lista y elimina nulls de forma recursiva.
 */
function sanitize_data(array $data, bool $zeroIds = false): array
{
    $out = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $out[$key] = sanitize_data($value, $zeroIds);
            continue;
        }
        if ($value === null) {
            // IDs opcionales → 0; resto → cadena vacía
            if ($zeroIds && is_string($key) && (
                str_starts_with($key, 'id_') || $key === 'id' || str_ends_with($key, '_id')
            )) {
                $out[$key] = 0;
            } else {
                $out[$key] = '';
            }
            continue;
        }
        if (is_string($value) && (strcasecmp(trim($value), 'null') === 0 || strcasecmp(trim($value), 'undefined') === 0)) {
            $out[$key] = '';
            continue;
        }
        $out[$key] = $value;
    }
    return $out;
}

/**
 * Fila pública de mascota sin nulls (controllers / API).
 */

/**
 * Extrae Tamaño / Collar / Chip desde senas_particulares (texto compuesto del registro).
 * @return array{tamano:string,collar:string,chip:string,senas_limpias:string}
 */
function rupe_extract_from_senas(?string $senas): array
{
    $s = trim((string) ($senas ?? ''));
    $out = ['tamano' => '', 'collar' => '', 'chip' => '', 'senas_limpias' => $s];
    if ($s === '') {
        return $out;
    }
    if (preg_match('/Tamaño\s*:\s*([^|]+)/iu', $s, $m)) {
        $out['tamano'] = trim($m[1]);
    }
    if (preg_match('/Collar\s*:\s*([^|]+)/iu', $s, $m)) {
        $out['collar'] = trim($m[1]);
    }
    if (preg_match('/Chip(?:\/tatuaje)?\s*:\s*([^|]+)/iu', $s, $m)) {
        $out['chip'] = trim($m[1]);
    }
    // Quitar meta-campos del texto de señas para mostrar solo lo libre
    $limpio = preg_replace('/\s*\|\s*/', ' | ', $s) ?? $s;
    $limpio = preg_replace('/(?:Raza|Tamaño|Collar|Chip(?:\/tatuaje)?|Hora extravío)\s*:\s*[^|]*/iu', '', $limpio) ?? $limpio;
    $limpio = preg_replace('/\s*\|\s*\|\s*/', ' | ', $limpio) ?? $limpio;
    $limpio = trim($limpio, " \t\n\r\0\x0B|");
    $out['senas_limpias'] = $limpio;
    return $out;
}

function mascota_public_row(array $m): array
{
    $especie = (string) ($m['especie'] ?? 'perro');
    if ($especie === '') {
        $especie = 'perro';
    }
    $fotoRaw = $m['foto_url'] ?? ($m['foto'] ?? '');
    $foto = function_exists('mascota_foto_src')
        ? mascota_foto_src(is_string($fotoRaw) ? $fotoRaw : null, $especie)
        : (string) ($fotoRaw ?? '');

    $idRaza = $m['id_raza'] ?? null;
    $idAlc = $m['id_alcaldia'] ?? null;
    $idMascota = (int) ($m['id_mascota'] ?? $m['id'] ?? 0);

    $lat = $m['latitud'] ?? '';
    $lng = $m['longitud'] ?? '';
    $senasRaw = (string) ($m['senas'] ?? $m['senas_particulares'] ?? '');
    $extra = function_exists('rupe_extract_from_senas') ? rupe_extract_from_senas($senasRaw) : ['tamano' => '', 'collar' => '', 'chip' => '', 'senas_limpias' => $senasRaw];
    $tamano = (string) (($m['tamano'] ?? '') !== '' ? $m['tamano'] : ($extra['tamano'] ?? ''));
    $collar = (string) (($m['collar'] ?? '') !== '' ? $m['collar'] : ($extra['collar'] ?? ''));
    $chip = (string) (($m['microchip'] ?? $m['chip'] ?? '') !== '' ? ($m['microchip'] ?? $m['chip']) : ($extra['chip'] ?? ''));
    return [
        'id' => $idMascota,
        'id_mascota' => $idMascota,
        'folio' => (string) ($m['folio'] ?? ''),
        'nombre' => (string) ($m['nombre'] ?? ''),
        'raza' => (string) ($m['raza'] ?? $m['raza_nombre'] ?? ''),
        'raza_nombre' => (string) ($m['raza_nombre'] ?? $m['raza'] ?? ''),
        'id_raza' => ($idRaza !== null && $idRaza !== '' && (int) $idRaza > 0) ? (int) $idRaza : 0,
        'especie' => $especie,
        'sexo' => (string) (($m['sexo'] ?? '') !== '' ? $m['sexo'] : 'desconocido'),
        'color' => (string) ($m['color'] ?? ''),
        'edad_aprox' => (string) ($m['edad_aprox'] ?? ''),
        'senas' => (string) ($m['senas'] ?? $m['senas_particulares'] ?? ''),
        'senas_particulares' => (string) ($m['senas_particulares'] ?? $m['senas'] ?? ''),
        'foto' => $foto,
        'foto_url' => $foto,
        'alcaldia' => (string) ($m['alcaldia'] ?? $m['alcaldia_nombre'] ?? ''),
        'alcaldia_nombre' => (string) ($m['alcaldia_nombre'] ?? $m['alcaldia'] ?? ''),
        'id_alcaldia' => ($idAlc !== null && $idAlc !== '' && (int) $idAlc > 0) ? (int) $idAlc : 0,
        'colonia' => (string) ($m['colonia'] ?? ''),
        'direccion_ref' => (string) ($m['direccion_ref'] ?? ''),
        'fecha' => (string) ($m['fecha'] ?? $m['fecha_extravio'] ?? ''),
        'fecha_extravio' => (string) ($m['fecha_extravio'] ?? $m['fecha'] ?? ''),
        'estatus' => (string) (($m['estatus'] ?? '') !== '' ? $m['estatus'] : 'En Búsqueda'),
        'qr_token' => (string) ($m['qr_token'] ?? ''),
        'latitud' => ($lat !== null && $lat !== '') ? (string) $lat : '',
        'longitud' => ($lng !== null && $lng !== '') ? (string) $lng : '',
        'notas' => (string) ($m['notas'] ?? ''),
        'tamano' => $tamano,
        'collar' => $collar,
        'chip' => $chip,
        'microchip' => $chip,
        'avistamientos' => is_array($m['avistamientos'] ?? null) ? $m['avistamientos'] : [],
        'id_albergue' => (int) ($m['id_albergue'] ?? 0),
        'albergueId' => (int) ($m['id_albergue'] ?? 0),
        'albergue' => (string) ($m['albergue_nombre'] ?? $m['albergue'] ?? ''),
        'albergue_nombre' => (string) ($m['albergue_nombre'] ?? $m['albergue'] ?? ''),
    ];
}
