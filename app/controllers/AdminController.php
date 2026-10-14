<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Mailer;

use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\NotFoundException;
use App\Helpers\Validator;
use App\Models\UsuarioDAO;
use App\Models\MascotaDAO;
use App\Models\AlcaldiaDAO;
use App\Models\RazaDAO;
use App\Models\AuditoriaDAO;
use App\Models\ConfiguracionDAO;
use App\Models\AyudaDAO;

final class AdminController
{
    private function requireAdmin(): int
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== ROLE_ADMIN) {
            throw new ForbiddenException('Solo administradores');
        }
        return (int) $_SESSION['user_id'];
    }

    /** Datos compartidos de sesión + BD para todas las vistas admin */
    private function viewData(string $modulo = 'reportes'): array
    {
        $uid = $this->requireAdmin();
        $userRow = (new UsuarioDAO())->find($uid) ?: [];
        $mascotaDao = new MascotaDAO();
        $base = [
            'csrf' => csrf_token(),
            'app_url' => rtrim(APP_URL, '/'),
            'modulo' => $modulo,
            'user' => [
                'id' => $uid,
                'nombre' => (string) ($userRow['nombre'] ?? $_SESSION['user_name'] ?? ''),
                'correo' => (string) ($userRow['correo'] ?? $_SESSION['user_email'] ?? ''),
                'telefono' => (string) ($userRow['telefono'] ?? ''),
                'rol' => ROLE_ADMIN,
            ],
        ];

        // Catálogos y listados reales según módulo (solo BD)
        switch ($modulo) {
            case 'usuarios':
                $base['usuarios'] = (new UsuarioDAO())->listByRole(null, 200, 0);
                break;
            case 'alcaldias':
                $base['alcaldias'] = (new AlcaldiaDAO())->listActive();
                break;
            case 'razas':
                $base['razas'] = array_values((new RazaDAO())->listAllAdmin());
                break;
            case 'auditoria':
                $base['auditoria'] = (new AuditoriaDAO())->listRecent(150, 0);
                break;
            case 'configuracion':
                $base['config'] = (new ConfiguracionDAO())->allAsMap();
                try { $base['alcaldias'] = array_values((new AlcaldiaDAO())->listAllAdmin()); } catch (\Throwable $e) { $base['alcaldias'] = []; }
                break;
            case 'ayuda':
                $base['faqs'] = (new AyudaDAO())->all('categoria, orden');
                break;
            case 'perfil':
                $base['usuario'] = $userRow ? (new UsuarioDAO())->toPublic($userRow) : $base['user'];
                // also load mascotas for perfil widgets
                $listP = $mascotaDao->listAll([], 200, 0);
                $base['mascotas'] = array_map(static function (array $m): array {
                    $row = function_exists('mascota_public_row') ? mascota_public_row($m) : (function_exists('sanitize_data') ? sanitize_data($m, true) : $m);
                    foreach (['folio','nombre','raza_nombre','alcaldia_nombre','tutor_nombre','estatus','foto_url','created_at','fecha_extravio','tamano','color'] as $k) {
                        if (array_key_exists($k, $m)) $row[$k] = $m[$k];
                    }
                    $row['tutor'] = (string) ($m['tutor_nombre'] ?? '');
                    return $row;
                }, $listP);
                break;
            case 'calculos':
            case 'reportes':
            default:
                try {
                    $base['alcaldias'] = array_values((new AlcaldiaDAO())->listAllAdmin());
                } catch (\Throwable $e) {
                    $base['alcaldias'] = [];
                }
                try {
                    $base['razas'] = array_values((new RazaDAO())->listAllAdmin());
                } catch (\Throwable $e) {
                    $base['razas'] = [];
                }
                $list = $mascotaDao->listAll([], 500, 0);
                $base['mascotas'] = array_map(static function (array $m): array {
                    $row = function_exists('mascota_public_row') ? mascota_public_row($m) : (function_exists('sanitize_data') ? sanitize_data($m, true) : $m);
                    // Campos de JOIN y extras para expedientes (siempre desde BD)
                    foreach ([
                        'id_mascota','folio','nombre','especie','sexo','color','edad_aprox',
                        'senas_particulares','collar','microchip','tamano','colonia','direccion_ref',
                        'latitud','longitud','fecha_extravio','estatus','qr_token','activo','created_at',
                        'antiguedad_dias','raza_nombre','alcaldia_nombre','tutor_nombre','tutor_telefono'
                    ] as $k) {
                        if (array_key_exists($k, $m)) {
                            $row[$k] = $m[$k];
                        }
                    }
                    $row['tutor'] = (string) ($m['tutor_nombre'] ?? $row['tutor'] ?? '');
                    $row['foto'] = (string) ($m['foto_url'] ?? $row['foto_url'] ?? '');
                    $row['foto_url'] = (string) ($m['foto_url'] ?? $row['foto_url'] ?? '');
                    return $row;
                }, $list);
                $base['stats'] = $mascotaDao->statsGlobal();
                $base['por_alcaldia'] = $mascotaDao->statsByAlcaldia();
                $base['alcaldias'] = (new AlcaldiaDAO())->listActive();
                $base['razas'] = array_values((new RazaDAO())->listAllAdmin());
                break;
        }
        return $base;
    }

    public function panel(Request $request): void
    {
        Response::view('admin/modulo-reportes', $this->viewData('reportes'));
    }

    public function modulo(Request $request): void
    {
        $mod = (string) $request->param('modulo');
        $map = [
            'usuarios' => 'admin/modulo-usuarios',
            'alcaldias' => 'admin/modulo-alcaldias',
            'razas' => 'admin/modulo-razas',
            'reportes' => 'admin/modulo-reportes',
            'auditoria' => 'admin/modulo-auditoria',
            'configuracion' => 'admin/modulo-configuracion',
            'perfil' => 'admin/modulo-perfil',
            'ayuda' => 'admin/modulo-ayuda',
            'calculos' => 'admin/modulo-calculos',
        ];
        if (!isset($map[$mod])) {
            throw new NotFoundException();
        }
        Response::view($map[$mod], $this->viewData($mod));
    }

    public function listUsuarios(Request $request): void
    {
        $this->requireAdmin();
        $rol = $request->query('rol');
        Response::ok((new UsuarioDAO())->listByRole($rol ? (string) $rol : null));
    }



    /**
     * Contraseña plana exactamente 8 caracteres (solo A-Z y 2-9).
     * Sin minúsculas ni símbolos para evitar confusión al copiar del correo.
     */
    /**
     * Contraseña plana 8 caracteres: mayúscula + minúscula + número + especial.
     * Misma política que registro/login (Validator::password).
     */
    private static function generarPasswordPlana(int $length = 8): string
    {
        $length = max(8, $length);
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $digits = '23456789';
        $special = '!@#$%&*?';
        $all = $upper . $lower . $digits . $special;

        try {
            // Garantizar al menos un carácter de cada tipo
            $chars = [
                $upper[random_int(0, strlen($upper) - 1)],
                $lower[random_int(0, strlen($lower) - 1)],
                $digits[random_int(0, strlen($digits) - 1)],
                $special[random_int(0, strlen($special) - 1)],
            ];
            for ($i = count($chars); $i < $length; $i++) {
                $chars[] = $all[random_int(0, strlen($all) - 1)];
            }
            // Mezclar
            for ($i = count($chars) - 1; $i > 0; $i--) {
                $j = random_int(0, $i);
                $tmp = $chars[$i];
                $chars[$i] = $chars[$j];
                $chars[$j] = $tmp;
            }
            $password = implode('', $chars);
        } catch (\Throwable $e) {
            $password = 'Ru' . substr(bin2hex(random_bytes(2)), 0, 4) . '1!';
        }

        // Verificar política; si falla, forzar patrón fijo válido de 8
        if (
            !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)
            || strlen($password) < 8
        ) {
            $password = 'Rupe' . random_int(10, 99) . '!a';
            $password = substr($password, 0, 8);
            // Rupe12!a = 8 chars con todos los tipos
            $password = 'Ru' . (string) random_int(10, 99) . '!aB';
            $password = substr($password, 0, 8);
        }

        return $password;
    }

    /** POST /api/admin/usuarios — crear admin: misma contraseña en BD (hash) y correo (plana) */
    public function createUsuario(Request $request): void
    {
        $adminId = $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }

        $in = $request->all();
        $nombre = trim((string) ($in['nombre'] ?? ''));
        $correo = strtolower(trim((string) ($in['correo'] ?? '')));
        $telefono = trim((string) ($in['telefono'] ?? ''));

        if (mb_strlen($nombre) < 2) {
            Response::error('El nombre debe tener al menos 2 caracteres.', 422);
        }
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Response::error('Correo no válido.', 422);
        }

        // ÚNICA fuente de verdad de la contraseña plana (8 caracteres)
        $plainPassword = self::generarPasswordPlana(8);

        $dao = new UsuarioDAO();
        if ($dao->findByEmail($correo)) {
            Response::error('Ese correo ya está registrado.', 422);
        }

        // Hash bcrypt a partir de la misma cadena
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        if ($hash === false || !is_string($hash) || !password_verify($plainPassword, $hash)) {
            Response::error('Error al generar hash de contraseña.', 500);
        }

        $id = $dao->create([
            'nombre'            => $nombre,
            'correo'            => $correo,
            'telefono'          => $telefono !== '' ? $telefono : null,
            'password_hash'     => $hash,
            'rol'               => ROLE_ADMIN,
            'activo'            => 1,
            'acepta_privacidad' => 1,
        ]);

        if ($id <= 0) {
            Response::error('No se pudo insertar el usuario en la base de datos.', 500);
        }

        // Forzar UPDATE del hash (evita columnas con valor por defecto / triggers raros)
        $dao->update($id, ['password_hash' => $hash]);

        // Leer de BD y verificar que password_verify funciona con la misma plana
        $row = $dao->find($id);
        if (!$row) {
            Response::error('Usuario creado pero no se pudo releer de la BD.', 500);
        }
        $dbHash = (string) ($row['password_hash'] ?? '');
        if ($dbHash === '' || !password_verify($plainPassword, $dbHash)) {
            // Reintentar un segundo hash y UPDATE
            $hash2 = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 10]);
            $dao->update($id, ['password_hash' => $hash2]);
            $row = $dao->find($id);
            $dbHash = (string) ($row['password_hash'] ?? '');
            if ($dbHash === '' || !password_verify($plainPassword, $dbHash)) {
                Response::error(
                    'La contraseña no coincidió al guardar en la base de datos. Revisa que password_hash sea VARCHAR(255).',
                    500
                );
            }
        }

        try {
            (new AuditoriaDAO())->log($adminId, 'usuario_crear_admin', 'usuarios', $id, [
                'correo' => $correo,
                'pass_len' => strlen($plainPassword),
            ]);
        } catch (\Throwable $e) {
        }

        // Correo con LA MISMA $plainPassword ya verificada contra la BD
        $mailOk = false;
        try {
            $mailOk = Mailer::sendAdminCredentials($correo, $nombre, $plainPassword);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'createUsuario mail: ' . $e->getMessage());
            }
            $mailOk = false;
        }

        $user = $dao->toPublic($row);
        $msg = $mailOk
            ? 'Administrador creado. Se envió el correo con la contraseña a ' . $correo
            : 'Administrador creado, pero no se pudo enviar el correo.';

        Response::ok([
            'user'               => function_exists('sanitize_data') ? sanitize_data($user, true) : $user,
            'password_generada'  => $plainPassword,
            'correo'             => $correo,
            'mail_enviado'       => $mailOk,
            'password_verificada'=> true,
        ], $msg, 201);
    }

    public function updateUsuario(Request $request): void
    {
        $adminId = $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido', 419);
        }
        $id = (int) $request->param('id');
        $dao = new UsuarioDAO();
        $user = $dao->find($id);
        if (!$user) {
            throw new NotFoundException();
        }
        $all = $request->all();
        $data = Validator::make($all)->validate();
        $payload = [];
        $prevActivo = (int) ($user['activo'] ?? 0);
        if (array_key_exists('activo', $all) || isset($data['activo'])) {
            $payload['activo'] = (int) (bool) ($all['activo'] ?? $data['activo'] ?? 0);
        }
        if (isset($data['rol']) && in_array($data['rol'], [ROLE_ADMIN, ROLE_TUTOR], true)) {
            $payload['rol'] = $data['rol'];
        }
        if (isset($data['nombre'])) {
            $payload['nombre'] = $data['nombre'];
        }
        if ($payload === []) {
            Response::error('Sin cambios', 422);
        }
        $dao->update($id, $payload);
        try {
            (new AuditoriaDAO())->log($adminId, 'usuario_actualizar', 'usuarios', $id, $payload);
        } catch (\Throwable $e) {
        }

        $mailOk = null;
        $fresh = $dao->find($id) ?: $user;
        if (isset($payload['activo']) && (int) $payload['activo'] !== $prevActivo) {
            $correo = (string) ($fresh['correo'] ?? '');
            $nombre = (string) ($fresh['nombre'] ?? 'Usuario');
            $activo = (int) $payload['activo'] === 1;
            if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                try {
                    $mailOk = Mailer::sendUserStatusChange($correo, $nombre, $activo);
                } catch (\Throwable $e) {
                    $mailOk = false;
                    if (function_exists('app_log')) {
                        app_log('error', 'updateUsuario status mail: ' . $e->getMessage());
                    }
                }
            }
        }

        $msg = 'Usuario actualizado';
        if ($mailOk === true) {
            $msg = (int) ($payload['activo'] ?? $prevActivo) === 1
                ? 'Usuario activo. Se notificó por correo.'
                : 'Usuario inactivo. Se notificó por correo.';
        } elseif ($mailOk === false) {
            $msg = 'Usuario actualizado, pero no se pudo enviar el correo de notificación.';
        }

        Response::ok([
            'user' => sanitize_data($dao->toPublic($fresh), true),
            'mail_enviado' => $mailOk,
        ], $msg);
    }

    public function listAlcaldias(Request $request): void
    {
        $this->requireAdmin();
        $list = array_values((new AlcaldiaDAO())->listAllAdmin());
        Response::ok($list);
    }

    public function saveAlcaldia(Request $request): void
    {
        $adminId = $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido', 419);
        }
        $dao = new AlcaldiaDAO();
        $id = (int) ($request->param('id') ?? 0);
        $all = $request->all();
        if ($id > 0 && array_key_exists('activo', $all) && !isset($all['nombre'])) {
            $dao->update($id, ['activo' => (int) (bool) $all['activo']]);
            (new AuditoriaDAO())->log($adminId, 'alcaldia_actualizar', 'alcaldias', $id, ['activo' => $all['activo']]);
            Response::ok(sanitize_data($dao->find($id) ?: [], true), 'Actualizado');
        }
        $data = Validator::make($all)->required('nombre')->min('nombre', 2)->validate();
        if (isset($all['clave'])) {
            $data['clave'] = $all['clave'];
        }
        if (isset($all['zona'])) {
            $data['zona'] = trim((string) $all['zona']);
        }
        if (isset($all['cp_base']) || isset($all['codigo_postal'])) {
            $data['cp_base'] = trim((string) ($all['cp_base'] ?? $all['codigo_postal'] ?? ''));
        }
        if (isset($all['activo'])) {
            $data['activo'] = (int) (bool) $all['activo'];
        }
        if ($id > 0) {
            $dao->update($id, $data);
            (new AuditoriaDAO())->log($adminId, 'alcaldia_actualizar', 'alcaldias', $id);
            Response::ok(sanitize_data($dao->find($id) ?: [], true), 'Actualizado');
        }
        $newId = $dao->create($data);
        (new AuditoriaDAO())->log($adminId, 'alcaldia_crear', 'alcaldias', $newId);
        Response::ok(sanitize_data($dao->find($newId) ?: [], true), 'Creado', 201);
    }

    public function listRazas(Request $request): void
    {
        $this->requireAdmin();
        $dao = new RazaDAO();
        $list = array_values($dao->listAllAdmin());
        $dbName = \App\Core\Database::getConnectedName();
        $tamanosMascotas = $dao->listTamanosFromMascotas();
        try {
            $pdo = \App\Core\Database::getInstance();
            $dbName = (string) ($pdo->query('SELECT DATABASE()')->fetchColumn() ?: $dbName);
        } catch (\Throwable $e) {
        }
        Response::ok([
            'items' => $list,
            'razas' => $list,
            'database' => $dbName,
            'total' => count($list),
            'tamanos_mascotas' => $tamanosMascotas,
        ]);
    }

    public function saveRaza(Request $request): void
    {
        try {
            $adminId = $this->requireAdmin();
        } catch (\Throwable $e) {
            Response::error('Debes iniciar sesión como administrador.', 403);
        }

        // Unificar body: JSON + POST + input + raw
        $all = [];
        try {
            $all = $request->all();
        } catch (\Throwable $e) {
            $all = [];
        }
        if (!is_array($all)) {
            $all = [];
        }
        if ($all === []) {
            $inp = $request->input();
            if (is_array($inp)) {
                $all = $inp;
            }
        }
        if ($all === [] && !empty($_POST) && is_array($_POST)) {
            $all = $_POST;
        }
        if ($all === []) {
            $raw = file_get_contents('php://input');
            if (is_string($raw) && trim($raw) !== '') {
                $dec = json_decode($raw, true);
                if (is_array($dec)) {
                    $all = $dec;
                } else {
                    parse_str($raw, $parsed);
                    if (is_array($parsed) && $parsed !== []) {
                        $all = $parsed;
                    }
                }
            }
        }

        $id = (int) ($request->param('id') ?? $all['id'] ?? $all['id_raza'] ?? 0);
        $dao = new RazaDAO();
        $pdo = \App\Core\Database::getInstance();
        $dbName = \App\Core\Database::getConnectedName();
        try {
            $dbName = (string) ($pdo->query('SELECT DATABASE()')->fetchColumn() ?: $dbName);
        } catch (\Throwable $e) {
        }

        // Toggle activo sin nombre
        if ($id > 0 && array_key_exists('activo', $all) && empty($all['nombre'])) {
            $dao->update($id, ['activo' => (int) (bool) $all['activo']]);
            $row = $dao->find($id) ?: ['id_raza' => $id, 'activo' => (int) (bool) $all['activo']];
            $row['_database'] = $dbName;
            Response::ok($row, 'Estado actualizado en ' . $dbName);
        }

        $nombre = trim((string) ($all['nombre'] ?? $all['nombre_raza'] ?? ''));
        $tamano = trim((string) ($all['tamano'] ?? $all['tamaño'] ?? $all['size'] ?? ''));
        if (mb_strlen($nombre) < 2) {
            Response::error(
                'El nombre de la raza es obligatorio (mín. 2 caracteres). Keys recibidas: ' .
                implode(',', array_keys($all)),
                422
            );
        }
        if ($tamano === '') {
            Response::error('Selecciona el tamaño de la raza.', 422);
        }

        $data = [
            'nombre'  => $nombre,
            'especie' => 'perro',
            'tamano'  => $tamano,
            'activo'  => isset($all['activo']) ? (int) (bool) $all['activo'] : 1,
        ];

        if (function_exists('app_log')) {
            app_log('info', 'saveRaza attempt', ['id' => $id, 'data' => $data, 'db' => $dbName, 'admin' => $adminId]);
        }

        try {
            // Asegurar columnas
            $dao->ensureExtraColumns();

            if ($id > 0) {
                $dao->update($id, $data);
                // Verificación forzada
                $stmt = $pdo->prepare('SELECT id_raza, nombre, especie, tamano, activo FROM razas WHERE id_raza = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!$row) {
                    Response::error('UPDATE ejecutado pero no se encontró id_raza=' . $id . ' en BD ' . $dbName, 500);
                }
                $row['especie'] = 'perro';
                $row['especie_label'] = 'perro';
                $row['codigo'] = 'RZ-' . str_pad((string) $row['id_raza'], 2, '0', STR_PAD_LEFT);
                $row['_database'] = $dbName;
                Response::ok($row, 'Raza actualizada en ' . $dbName);
            }

            // CREATE
            $newId = $dao->create($data);

            // Verificación directa SQL (no solo DAO)
            $stmt = $pdo->prepare(
                'SELECT id_raza, nombre, COALESCE(especie,\'perro\') AS especie, COALESCE(tamano,\'\') AS tamano, COALESCE(activo,1) AS activo, created_at
                 FROM razas WHERE id_raza = :id LIMIT 1'
            );
            $stmt->execute(['id' => $newId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                // Último intento: buscar por nombre
                $stmt2 = $pdo->prepare(
                    'SELECT id_raza, nombre, COALESCE(especie,\'perro\') AS especie, COALESCE(tamano,\'\') AS tamano, COALESCE(activo,1) AS activo
                     FROM razas WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:n)) ORDER BY id_raza DESC LIMIT 1'
                );
                $stmt2->execute(['n' => $nombre]);
                $row = $stmt2->fetch(\PDO::FETCH_ASSOC);
            }
            if (!$row) {
                Response::error(
                    'INSERT reportó id=' . $newId . ' pero SELECT no encuentra la fila en BD ' . $dbName .
                    '. Revisa permisos INSERT en tabla razas.',
                    500
                );
            }

            // Si tamano quedó vacío, forzar UPDATE
            if (trim((string) ($row['tamano'] ?? '')) === '') {
                try {
                    $pdo->prepare('UPDATE razas SET tamano = :t, especie = \'perro\' WHERE id_raza = :id')
                        ->execute(['t' => $tamano, 'id' => (int) $row['id_raza']]);
                    $row['tamano'] = $tamano;
                } catch (\Throwable $eUp) {
                }
            }

            $row['especie'] = 'perro';
            $row['especie_label'] = 'perro';
            $row['codigo'] = 'RZ-' . str_pad((string) $row['id_raza'], 2, '0', STR_PAD_LEFT);
            $row['_database'] = $dbName;
            if (function_exists('app_log')) {
                app_log('info', 'saveRaza OK', ['row' => $row]);
            }
            Response::ok($row, 'Guardado OK en BD ' . $dbName . ' · id_raza=' . $row['id_raza'] . ' · ' . $nombre, 201);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'saveRaza FAIL', ['msg' => $e->getMessage(), 'db' => $dbName]);
            }
            Response::error('Error al guardar en razas (' . $dbName . '): ' . $e->getMessage(), 500);
        }
    }

    public function reportes(Request $request): void
    {
        $this->requireAdmin();
        $dao = new MascotaDAO();
        $filters = array_filter([
            'estatus' => $request->query('estatus'),
            'id_alcaldia' => $request->query('id_alcaldia'),
            'q' => $request->query('q'),
        ], static fn($v) => $v !== null && $v !== '' && $v !== 0);

        $list = $dao->listAll($filters, 1000, 0);

        // Mapa id_raza / nombre → tamaño (catálogo + moda mascotas)
        $mapaTamRazas = [];
        try {
            foreach ((new RazaDAO())->listAllAdmin() as $rz) {
                $tn = trim((string) ($rz['tamano'] ?? ''));
                if ($tn === '' || $tn === '—') {
                    continue;
                }
                $idR = (int) ($rz['id_raza'] ?? 0);
                $nm = mb_strtolower(trim((string) ($rz['nombre'] ?? '')));
                if ($idR > 0) {
                    $mapaTamRazas['id:' . $idR] = $tn;
                }
                if ($nm !== '') {
                    $mapaTamRazas['n:' . $nm] = $tn;
                }
            }
        } catch (\Throwable $e) {
            $mapaTamRazas = [];
        }

        $out = [];
        foreach ($list as $m) {
            if (!is_array($m)) {
                continue;
            }
            $row = function_exists('mascota_public_row')
                ? mascota_public_row($m)
                : (function_exists('sanitize_data') ? sanitize_data($m, true) : $m);

            // Campos reales de JOINs (tutor, raza, alcaldía)
            $row['id_mascota'] = (int) ($m['id_mascota'] ?? $row['id_mascota'] ?? 0);
            $row['folio'] = (string) ($m['folio'] ?? $row['folio'] ?? '');
            $row['nombre'] = (string) ($m['nombre'] ?? $row['nombre'] ?? '');
            $row['especie'] = (string) ($m['especie'] ?? $row['especie'] ?? 'perro');
            $row['sexo'] = (string) ($m['sexo'] ?? $row['sexo'] ?? '');
            $row['color'] = (string) ($m['color'] ?? $row['color'] ?? '');
            $row['edad_aprox'] = $m['edad_aprox'] ?? $row['edad_aprox'] ?? null;
            $tamRow = trim((string) ($m['tamano'] ?? $row['tamano'] ?? ''));
            if ($tamRow === '' || $tamRow === '—') {
                $idRazaM = (int) ($m['id_raza'] ?? $row['id_raza'] ?? 0);
                $nomRaza = mb_strtolower(trim((string) ($m['raza_nombre'] ?? $row['raza'] ?? $row['raza_nombre'] ?? '')));
                if ($idRazaM > 0 && isset($mapaTamRazas['id:' . $idRazaM])) {
                    $tamRow = $mapaTamRazas['id:' . $idRazaM];
                } elseif ($nomRaza !== '' && isset($mapaTamRazas['n:' . $nomRaza])) {
                    $tamRow = $mapaTamRazas['n:' . $nomRaza];
                }
            }
            $row['tamano'] = $tamRow;
            $row['collar'] = (string) ($m['collar'] ?? $row['collar'] ?? '');
            $row['microchip'] = (string) ($m['microchip'] ?? $row['microchip'] ?? '');
            $row['senas_particulares'] = (string) ($m['senas_particulares'] ?? $row['senas_particulares'] ?? '');
            $row['colonia'] = (string) ($m['colonia'] ?? $row['colonia'] ?? '');
            $row['direccion_ref'] = (string) ($m['direccion_ref'] ?? $row['direccion_ref'] ?? '');
            $row['latitud'] = $m['latitud'] ?? $row['latitud'] ?? null;
            $row['longitud'] = $m['longitud'] ?? $row['longitud'] ?? null;
            $row['fecha_extravio'] = (string) ($m['fecha_extravio'] ?? $row['fecha_extravio'] ?? '');
            $row['estatus'] = (string) ($m['estatus'] ?? $row['estatus'] ?? '');
            $row['qr_token'] = (string) ($m['qr_token'] ?? $row['qr_token'] ?? '');
            $row['created_at'] = (string) ($m['created_at'] ?? $row['created_at'] ?? '');
            $row['antiguedad_dias'] = isset($m['antiguedad_dias']) ? (int) $m['antiguedad_dias'] : null;
            $row['raza_nombre'] = (string) ($m['raza_nombre'] ?? $row['raza_nombre'] ?? '');
            $row['alcaldia_nombre'] = (string) ($m['alcaldia_nombre'] ?? $row['alcaldia_nombre'] ?? '');
            $row['tutor_nombre'] = (string) ($m['tutor_nombre'] ?? $row['tutor_nombre'] ?? '');
            $row['tutor_telefono'] = (string) ($m['tutor_telefono'] ?? $row['tutor_telefono'] ?? '');
            $row['tutor'] = $row['tutor_nombre'];
            $row['foto_url'] = (string) ($m['foto_url'] ?? $row['foto_url'] ?? '');
            $row['foto'] = $row['foto_url'];
            $out[] = $row;
        }

        Response::ok([
            'mascotas' => $out,
            'stats' => $dao->statsGlobal(),
            'por_alcaldia' => $dao->statsByAlcaldia(),
            'database' => \App\Core\Database::getConnectedName(),
            'total' => count($out),
        ]);
    }

    public function updateReporteEstatus(Request $request): void
    {
        $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido', 419);
        }
        $id = (int) ($request->param('id') ?? 0);
        $all = $request->all();
        $estatus = trim((string) ($all['estatus'] ?? 'En Búsqueda'));
        // Solo valores válidos del ENUM de mascotas
        $permitidos = ['En Búsqueda', 'En Resguardo', 'Localizado'];
        if (!in_array($estatus, $permitidos, true)) {
            // Mapear etiquetas del panel
            if (preg_match('/validado|seguimiento|búsqueda|busqueda/i', $estatus)) {
                $estatus = 'En Búsqueda';
            } elseif (preg_match('/resguardo/i', $estatus)) {
                $estatus = 'En Resguardo';
            } elseif (preg_match('/localizado|resuelto/i', $estatus)) {
                $estatus = 'Localizado';
            } else {
                $estatus = 'En Búsqueda';
            }
        }
        // Validar en bandeja = mantener en búsqueda activa (publicado)
        // "Validado" en UI → En Búsqueda (confirmado)
        if (isset($all['accion']) && strtolower((string) $all['accion']) === 'validar') {
            $estatus = 'En Búsqueda';
        }
        $dao = new MascotaDAO();
        if ($id <= 0) {
            Response::error('ID de mascota inválido', 422);
        }
        $row = $dao->find($id);
        if (!$row) {
            Response::error('Reporte no encontrado', 404);
        }
        $ok = $dao->update($id, ['estatus' => $estatus]);
        if (!$ok) {
            Response::error('No se pudo actualizar el estatus', 500);
        }
        $fresh = $dao->find($id) ?: [];
        Response::ok(sanitize_data($fresh, true), 'Estatus actualizado');
    }

    public function auditoria(Request $request): void
    {
        $this->requireAdmin();
        $list = (new AuditoriaDAO())->listRecent(150, 0);
        $list = array_map(static function (array $a): array {
            $accion = (string) ($a['accion'] ?? '');
            $tabla = (string) ($a['tabla'] ?? '');
            $modulo = (string) ($a['modulo'] ?? '');
            if ($modulo === '') {
                $modulo = \App\Models\AuditoriaDAO::moduloDesdeAccion($accion);
            }
            if ($tabla === '') {
                $tabla = $modulo;
            }
            $detalleTexto = (string) ($a['detalle_texto'] ?? '');
            if ($detalleTexto === '') {
                $detalleTexto = \App\Models\AuditoriaDAO::detalleDesdeAccion(
                    $accion,
                    $tabla,
                    $a['detalle'] ?? null,
                    (int) ($a['id_registro'] ?? 0) ?: null
                );
            }
            return [
                'id_auditoria'   => (int) ($a['id_auditoria'] ?? 0),
                'id_usuario'     => (int) ($a['id_usuario'] ?? 0),
                'usuario_nombre' => (string) ($a['usuario_nombre'] ?? ''),
                'usuario_correo' => (string) ($a['usuario_correo'] ?? ''),
                'accion'         => $accion,
                'tabla'          => $tabla,
                'modulo'         => $modulo,
                'id_registro'    => (int) ($a['id_registro'] ?? 0),
                'detalle'        => is_string($a['detalle'] ?? null) ? (string) $a['detalle'] : json_encode($a['detalle'] ?? new \stdClass(), JSON_UNESCAPED_UNICODE),
                'detalle_texto'  => $detalleTexto,
                'ip'             => (string) ($a['ip'] ?? ''),
                'user_agent'     => (string) ($a['user_agent'] ?? ''),
                'created_at'     => (string) ($a['created_at'] ?? ''),
            ];
        }, $list);
        Response::ok($list);
    }


    public function changePassword(Request $request): void
    {
        $uid = $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }
        $in = $request->all();
        $actual = trim((string) ($in['password_actual'] ?? $in['cfgAdminPassActual'] ?? ''));
        $nueva = trim((string) ($in['password'] ?? $in['password_nueva'] ?? $in['cfgAdminPassNueva'] ?? ''));
        $conf = trim((string) ($in['password_confirmation'] ?? $in['password_confirm'] ?? $in['cfgAdminPassConfirmar'] ?? ''));
        if ($actual === '' || $nueva === '' || $conf === '') {
            Response::error('Completa contraseña actual, nueva y confirmación.', 422);
        }
        if (mb_strlen($nueva) < 8) {
            Response::error('La nueva contraseña debe tener al menos 8 caracteres.', 422);
        }
        if (!preg_match('/[A-Z]/', $nueva)) {
            Response::error('Debe incluir al menos una letra mayúscula.', 422);
        }
        if (!preg_match('/[a-z]/', $nueva)) {
            Response::error('Debe incluir al menos una letra minúscula.', 422);
        }
        if (!preg_match('/\d/', $nueva)) {
            Response::error('Debe incluir al menos un número.', 422);
        }
        if (!preg_match('/[^A-Za-z0-9]/', $nueva)) {
            Response::error('Debe incluir al menos un carácter especial (!@#$%&*).', 422);
        }
        if ($nueva !== $conf) {
            Response::error('La nueva contraseña y su confirmación no coinciden.', 422);
        }
        $dao = new UsuarioDAO();
        $user = $dao->find($uid);
        $hash = (string) ($user['password_hash'] ?? '');
        if (!$user || $hash === '' || !verify_password($actual, $hash)) {
            Response::error('La contraseña actual no es correcta.', 422);
        }
        $dao->update($uid, ['password_hash' => hash_password($nueva)]);
        try {
            (new AuditoriaDAO())->log($uid, 'password_change', 'configuracion', $uid, []);
        } catch (\Throwable $e) {
        }
        Response::ok([], 'Contraseña actualizada correctamente');
    }

    public function getConfig(Request $request): void
    {
        $this->requireAdmin();
        Response::ok((new ConfiguracionDAO())->allAsMap());
    }

    public function setConfig(Request $request): void
    {
        $adminId = $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido', 419);
        }
        $clave = (string) $request->input('clave', '');
        $valor = $request->input('valor');
        $tipo = (string) $request->input('tipo', 'string');
        if ($clave === '') {
            Response::error('Clave requerida', 422);
        }
        (new ConfiguracionDAO())->set($clave, $valor, $tipo);
        (new AuditoriaDAO())->log($adminId, 'config_actualizar', 'configuraciones', null, ['clave' => $clave]);
        Response::ok([], 'Configuración guardada');
    }

    public function listAyuda(Request $request): void
    {
        $this->requireAdmin();
        Response::ok((new AyudaDAO())->all('categoria, orden'));
    }

    public function saveAyuda(Request $request): void
    {
        $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido', 419);
        }
        $dao = new AyudaDAO();
        $id = (int) ($request->param('id') ?? 0);
        $data = Validator::make($request->all())->required('pregunta', 'respuesta')->validate();
        try {
            if ($id > 0) {
                $dao->update($id, $data);
                $row = $dao->find($id) ?: [];
                Response::ok(sanitize_data($row, true), 'FAQ actualizada');
                return;
            }
            $newId = $dao->create($data);
            $row = $dao->find($newId) ?: ['id' => $newId];
            Response::ok(sanitize_data($row, true), 'FAQ creada', 201);
        } catch (\Throwable $e) {
            Response::error('No se pudo guardar: ' . $e->getMessage(), 500);
        }
    }

    public function perfil(Request $request): void
    {
        $adminId = $this->requireAdmin();
        $dao = new UsuarioDAO();
        $row = $dao->find($adminId);
        if (!$row) {
            Response::error('Usuario no encontrado', 404);
        }
        $public = $dao->toPublic($row);
        // Stats desde reportes
        $stats = ['total' => 0, 'validados' => 0, 'resueltos' => 0, 'pendientes' => 0];
        try {
            $list = (new MascotaDAO())->listAll([], 500, 0) ?: [];
            $stats['total'] = count($list);
            foreach ($list as $m) {
                $e = strtolower((string) ($m['estatus'] ?? ''));
                if (str_contains($e, 'localiz') || str_contains($e, 'resuelt') || str_contains($e, 'reunif')) {
                    $stats['resueltos']++;
                } elseif (str_contains($e, 'pendiente') || str_contains($e, 'valid')) {
                    $stats['pendientes']++;
                } else {
                    $stats['validados']++;
                }
            }
        } catch (\Throwable $e) {
        }
        $actividad = [];
        try {
            $actividad = (new AuditoriaDAO())->listRecent(12, 0);
            $actividad = array_values(array_filter($actividad, static function ($a) use ($adminId) {
                return (int) ($a['id_usuario'] ?? 0) === (int) $adminId
                    || true; // mostrar recientes del sistema también
            }));
            $actividad = array_slice($actividad, 0, 8);
        } catch (\Throwable $e) {
        }
        Response::ok([
            'usuario' => [
                'id_usuario' => (int) ($public['id_usuario'] ?? $adminId),
                'nombre' => (string) ($public['nombre'] ?? ''),
                'correo' => (string) ($public['correo'] ?? ''),
                'telefono' => (string) ($public['telefono'] ?? ''),
                'rol' => (string) ($public['rol'] ?? 'admin'),
                'activo' => (int) ($public['activo'] ?? 1),
                'created_at' => (string) ($public['created_at'] ?? ''),
                'ultimo_acceso' => (string) ($public['ultimo_acceso'] ?? ''),
            ],
            'stats' => $stats,
            'actividad' => $actividad,
        ]);
    }

    public function updatePerfil(Request $request): void
    {
        $adminId = $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }
        $all = $request->all();
        $nombre = trim((string) ($all['nombre'] ?? ''));
        $correo = strtolower(trim((string) ($all['correo'] ?? '')));
        $telefono = trim((string) ($all['telefono'] ?? ''));
        if (mb_strlen($nombre) < 2) {
            Response::error('El nombre debe tener al menos 2 caracteres.', 422);
        }
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Response::error('Correo electrónico no válido.', 422);
        }
        if ($telefono !== '' && mb_strlen(preg_replace('/\D+/', '', $telefono)) < 8) {
            Response::error('Teléfono no válido (mín. 8 dígitos).', 422);
        }
        $dao = new UsuarioDAO();
        // Correo único si cambió
        $otro = $dao->findByEmail($correo);
        if ($otro && (int) ($otro['id_usuario'] ?? 0) !== $adminId) {
            Response::error('Ese correo ya está registrado por otro usuario.', 422);
        }
        $ok = $dao->update($adminId, [
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono !== '' ? $telefono : null,
        ]);
        if (!$ok) {
            Response::error('No se pudo actualizar el perfil en la base de datos.', 500);
        }
        $row = $dao->find($adminId);
        if (!$row) {
            Response::error('Perfil actualizado pero no se pudo releer.', 500);
        }
        // Sincronizar sesión
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $correo;
        try {
            (new AuditoriaDAO())->log($adminId, 'perfil_admin_actualizar', 'usuarios', $adminId, [
                'nombre' => $nombre,
                'correo' => $correo,
            ]);
        } catch (\Throwable $e) {
        }
        $public = $dao->toPublic($row);
        Response::ok([
            'usuario' => [
                'id_usuario' => (int) ($public['id_usuario'] ?? $adminId),
                'nombre' => (string) ($public['nombre'] ?? $nombre),
                'correo' => (string) ($public['correo'] ?? $correo),
                'telefono' => (string) ($public['telefono'] ?? $telefono),
                'rol' => (string) ($public['rol'] ?? 'admin'),
                'created_at' => (string) ($public['created_at'] ?? ''),
            ],
        ], 'Perfil actualizado correctamente.');
    }


    /** POST /api/admin/ayuda-contacto — mensaje de soporte del admin autenticado */
    public function contactoAyuda(Request $request): void
    {
        $adminId = $this->requireAdmin();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }
        if (!rate_limit_check('admin_ayuda', 10, 300)) {
            Response::error('Has enviado demasiados mensajes. Intenta más tarde.', 429);
        }

        $all = $request->all();
        $asunto = trim((string) ($all['asunto'] ?? ''));
        $mensaje = trim((string) ($all['mensaje'] ?? ''));
        if ($asunto === '' || mb_strlen($asunto) < 2) {
            Response::error('Selecciona o escribe un asunto.', 422);
        }
        if (mb_strlen($mensaje) < 10) {
            Response::error('El mensaje debe tener al menos 10 caracteres.', 422);
        }

        $dao = new UsuarioDAO();
        $admin = $dao->find($adminId) ?: [];
        $nombre = (string) ($admin['nombre'] ?? $_SESSION['user_name'] ?? 'Administrador');
        $correo = (string) ($admin['correo'] ?? $_SESSION['user_email'] ?? '');

        $destinatario = defined('MAIL_FROM') && MAIL_FROM
            ? MAIL_FROM
            : 'admin@buscandoahuellitasrupe.com.mx';

        $html = '
          <p><strong>Mensaje desde Centro de Ayuda (panel Admin RUPE)</strong></p>
          <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <tr><td style="padding:6px 0;color:#666;width:130px;">Administrador</td><td style="padding:6px 0;"><strong>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong></td></tr>
            <tr><td style="padding:6px 0;color:#666;">Correo</td><td style="padding:6px 0;">' . htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') . '</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Asunto</td><td style="padding:6px 0;">' . htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8') . '</td></tr>
            <tr><td style="padding:6px 0;color:#666;">ID usuario</td><td style="padding:6px 0;">#' . (int) $adminId . '</td></tr>
          </table>
          <hr style="border:none;border-top:1px solid #eee;margin:16px 0;">
          <p style="white-space:pre-wrap;">' . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) . '</p>
          <p style="color:#999;font-size:12px;margin-top:20px;">' . date('Y-m-d H:i:s') . ' · IP ' . htmlspecialchars(function_exists('client_ip') ? client_ip() : '', ENT_QUOTES, 'UTF-8') . '</p>
        ';

        $ok = false;
        try {
            $ok = \App\Helpers\Mailer::send(
                $destinatario,
                'Ayuda Admin: ' . $asunto,
                $html,
                'RUPE Soporte',
                $correo !== '' ? $correo : null,
                $nombre
            );
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'admin ayuda mail', ['msg' => $e->getMessage()]);
            }
            $ok = false;
        }

        if (!$ok) {
            Response::error(
                'No se pudo enviar el correo a ' . $destinatario . '. Revisa SMTP en Hostinger.',
                500
            );
        }

        try {
            (new AuditoriaDAO())->log($adminId, 'ayuda_admin_contacto', 'ayuda', null, [
                'asunto' => $asunto,
                'destino' => $destinatario,
            ]);
        } catch (\Throwable $e) {
        }

        Response::ok(['enviado' => true, 'destino' => $destinatario], 'Mensaje enviado a ' . $destinatario);
    }

}
