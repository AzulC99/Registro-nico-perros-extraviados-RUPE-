<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\AuthException;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\NotFoundException;
use App\Helpers\Validator;
use App\Helpers\Mailer;
use App\Models\MascotaDAO;
use App\Models\SeguimientoDAO;
use App\Models\UsuarioDAO;
use App\Models\PreferenciaDAO;
use App\Models\AlcaldiaDAO;
use App\Models\RazaDAO;
use App\Models\AlbergueDAO;
use App\Models\AyudaDAO;
use App\Models\AuditoriaDAO;

final class TutorController
{
    private MascotaDAO $mascotas;
    private SeguimientoDAO $seguimientos;
    private UsuarioDAO $usuarios;
    private PreferenciaDAO $prefs;
    private AuditoriaDAO $audit;

    public function __construct()
    {
        $this->mascotas = new MascotaDAO();
        $this->seguimientos = new SeguimientoDAO();
        $this->usuarios = new UsuarioDAO();
        $this->prefs = new PreferenciaDAO();
        $this->audit = new AuditoriaDAO();
    }

    private function requireTutor(): int
    {
        if (empty($_SESSION['user_id'])) {
            throw new AuthException('No autenticado');
        }
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== ROLE_TUTOR && $role !== ROLE_ADMIN) {
            throw new ForbiddenException();
        }
        return (int) $_SESSION['user_id'];
    }

    public function panel(Request $request): void
    {
        $uid = $this->requireTutor();

        $list = [];
        $stats = [];
        try {
            $raw = $this->mascotas->listByTutor($uid);
            $stats = $this->mascotas->statsByTutor($uid) ?: [];
            foreach (is_array($raw) ? $raw : [] as $m) {
                if (!is_array($m)) {
                    continue;
                }
                if (function_exists('mascota_public_row')) {
                    $row = mascota_public_row($m);
                } else {
                    $row = [
                        'id' => (int) ($m['id_mascota'] ?? 0),
                        'id_mascota' => (int) ($m['id_mascota'] ?? 0),
                        'folio' => (string) ($m['folio'] ?? ''),
                        'nombre' => (string) ($m['nombre'] ?? ''),
                        'raza' => (string) ($m['raza_nombre'] ?? ''),
                        'id_raza' => (int) ($m['id_raza'] ?? 0),
                        'especie' => (string) ($m['especie'] ?? 'perro'),
                        'sexo' => (string) ($m['sexo'] ?? 'desconocido'),
                        'color' => (string) ($m['color'] ?? ''),
                        'edad_aprox' => (string) ($m['edad_aprox'] ?? ''),
                        'senas' => (string) ($m['senas_particulares'] ?? ''),
                        'senas_particulares' => (string) ($m['senas_particulares'] ?? ''),
                        'foto' => (string) ($m['foto_url'] ?? ''),
                        'foto_url' => (string) ($m['foto_url'] ?? ''),
                        'alcaldia' => (string) ($m['alcaldia_nombre'] ?? ''),
                        'id_alcaldia' => (int) ($m['id_alcaldia'] ?? 0),
                        'colonia' => (string) ($m['colonia'] ?? ''),
                        'fecha' => (string) ($m['fecha_extravio'] ?? ''),
                        'fecha_extravio' => (string) ($m['fecha_extravio'] ?? ''),
                        'estatus' => (string) ($m['estatus'] ?? 'En Búsqueda'),
                        'qr_token' => (string) ($m['qr_token'] ?? ''),
                        'latitud' => (string) ($m['latitud'] ?? ''),
                        'longitud' => (string) ($m['longitud'] ?? ''),
                        'notas' => (string) ($m['notas'] ?? ''),
                        'avistamientos' => [],
                    ];
                }
                // Foto siempre con URL usable
                $row['foto'] = function_exists('mascota_foto_src')
                    ? mascota_foto_src($row['foto_url'] ?? ($m['foto_url'] ?? null), (string) ($row['especie'] ?? 'perro'))
                    : (string) ($row['foto_url'] ?? '/assets/img/placeholder-perro.svg');
                $row['foto_url'] = $row['foto'];
                $row['color'] = (string) ($row['color'] ?? $m['color'] ?? '');
                $row['senas'] = (string) ($row['senas'] ?? $row['senas_particulares'] ?? $m['senas_particulares'] ?? '');
                $row['senas_particulares'] = $row['senas'];
                $row['edad_aprox'] = (string) ($row['edad_aprox'] ?? $m['edad_aprox'] ?? '');
                $row['colonia'] = (string) ($row['colonia'] ?? $m['colonia'] ?? '');
                $row['direccion_ref'] = (string) ($row['direccion_ref'] ?? $m['direccion_ref'] ?? '');
                $row['calle'] = (string) ($row['calle'] ?? $m['calle'] ?? '');
                $row['notas'] = (string) ($row['notas'] ?? $m['notas'] ?? '');
                if ($row['senas'] !== '' && stripos($row['senas'], 'collar') !== false) {
                    $row['collar'] = 'Sí (ver señas)';
                } else {
                    $row['collar'] = (string) ($row['collar'] ?? '');
                }
                $row['bitacora'] = (string) (($row['notas'] ?? '') !== '' ? $row['notas'] : 'Registro en RUPE CDMX');
                $list[] = $row;
            }
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'panel listByTutor', ['msg' => $e->getMessage()]);
            }
            $list = [];
            $stats = [];
        }

        $bitacoras = [];
        $historial = [];
        try {
            $bitacoras = $this->seguimientos->listBitacoraByTutor($uid);
            $bitById = [];
            foreach ($bitacoras as $b) {
                $bitById[(int) ($b['id_mascota'] ?? 0)] = $b;
            }
            foreach ($list as &$row) {
                $idm = (int) ($row['id_mascota'] ?? $row['id'] ?? 0);
                if (isset($bitById[$idm])) {
                    $bit = (string) ($bitById[$idm]['bitacora'] ?? '');
                    if ($bit !== '' && strcasecmp($bit, 'null') !== 0) {
                        $row['bitacora'] = $bit;
                    }
                }
            }
            unset($row);
            $historial = $this->seguimientos->listHistorialByTutor($uid);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'panel seguimientos', ['msg' => $e->getMessage()]);
            }
            $bitacoras = [];
            $historial = [];
        }

        $userRow = [];
        $prefs = [];
        try {
            $userRow = $this->usuarios->find($uid) ?: [];
            $prefs = $this->prefs->findByUsuario($uid) ?: [];
        } catch (\Throwable $e) {
            $userRow = [];
            $prefs = [];
        }

        $alcaldias = [];
        $razas = [];
        $albergues = [];
        try {
            $alcaldias = (new AlcaldiaDAO())->listActive() ?: [];
            $razas = (new RazaDAO())->listActive() ?: [];
            $albergues = (new AlbergueDAO())->listActive() ?: [];
        } catch (\Throwable $e) {
            // catálogos vacíos no bloquean el panel
        }

        Response::view('tutor/crud', [
            'csrf' => csrf_token(),
            'recaptchaSiteKey' => defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '',
            'user' => [
                'id' => $uid,
                'nombre' => (string) ($userRow['nombre'] ?? $_SESSION['user_name'] ?? ''),
                'correo' => (string) ($userRow['correo'] ?? $_SESSION['user_email'] ?? ''),
                'telefono' => (string) ($userRow['telefono'] ?? ''),
                'rol' => (string) ($_SESSION['user_role'] ?? ''),
            ],
            'mascotas' => $list,
            'bitacoras' => $bitacoras,
            'historial_seguimiento' => $historial,
            'stats' => is_array($stats) && function_exists('sanitize_data') ? sanitize_data($stats, true) : (is_array($stats) ? $stats : []),
            'alcaldias' => array_map(static function (array $a): array {
                return function_exists('sanitize_data') ? sanitize_data($a, true) : $a;
            }, is_array($alcaldias) ? $alcaldias : []),
            'razas' => array_map(static function (array $r): array {
                return function_exists('sanitize_data') ? sanitize_data($r, true) : $r;
            }, is_array($razas) ? $razas : []),
            'albergues' => array_map(static function (array $a): array {
                return function_exists('sanitize_data') ? sanitize_data($a, true) : $a;
            }, is_array($albergues) ? $albergues : []),
            'preferencias' => function_exists('sanitize_data') ? sanitize_data(is_array($prefs) ? $prefs : [], true) : (is_array($prefs) ? $prefs : []),
            'app_url' => defined('APP_URL') ? rtrim((string) APP_URL, '/') : '',
        ]);
    }




    /** GET /tutor/gpsqr | /tutor/gps-qr | /tutor/gps — vista GPS/QR */
    public function gpsqr(Request $request): void
    {
        $uid = $this->requireTutor();
        $data = $this->buildPanelData($uid);
        $data['modulo_activo'] = 'gps-qr';
        $data['seccion_activa'] = 'gps-qr';
        // Claves garantizadas para la vista
        if (!isset($data['mascotas']) || !is_array($data['mascotas'])) {
            $data['mascotas'] = $data['registros'] ?? [];
        }
        if (!isset($data['user']) || !is_array($data['user'])) {
            $data['user'] = $data['usuario'] ?? $data['tutor'] ?? [];
        }
        if (!isset($data['bitacoras']) || !is_array($data['bitacoras'])) {
            $data['bitacoras'] = $data['bitacora'] ?? [];
        }
        if (!isset($data['historial_seguimiento']) || !is_array($data['historial_seguimiento'])) {
            $data['historial_seguimiento'] = [];
            try {
                $data['historial_seguimiento'] = $this->seguimientos->listHistorialByTutor($uid) ?: [];
            } catch (\Throwable $e) {
                $data['historial_seguimiento'] = [];
            }
        }
        // Normalizar foto/GPS en mascotas para el boot JS
        if (!empty($data['mascotas']) && is_array($data['mascotas'])) {
            foreach ($data['mascotas'] as &$mm) {
                if (!is_array($mm)) continue;
                if (empty($mm['foto_url']) && !empty($mm['foto'])) {
                    $mm['foto_url'] = $mm['foto'];
                }
                if (function_exists('mascota_foto_src')) {
                    $mm['foto_url'] = mascota_foto_src($mm['foto_url'] ?? null, (string)($mm['especie'] ?? 'perro'));
                    $mm['foto'] = $mm['foto_url'];
                }
            }
            unset($mm);
        }
        try {
            Response::view('tutor/gpsqr', $data);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'tutor gpsqr view: ' . $e->getMessage());
            }
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=utf-8');
            }
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>GPS/QR</title></head><body>';
            echo '<p>No se pudo cargar el módulo GPS/QR. <a href="/tutor">Volver al panel</a></p>';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
            }
            echo '</body></html>';
        }
    }

    /** GET /tutor/{modulo} — navegar a cada página del panel tutor */
    public function modulo(Request $request): void
    {
        $uid = $this->requireTutor();
        $modulo = strtolower(trim((string) ($request->param('modulo') ?? '')));
        if ($modulo === '') {
            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
            if (preg_match('#/tutor/([^/?#]+)#i', $uri, $mm)) {
                $modulo = strtolower(rawurldecode($mm[1]));
            }
        }
        $modulo = str_replace(['_', ' '], ['-', ''], $modulo);

        $map = [
            'analisis' => 'tutor/analitico',
            'analitico' => 'tutor/analitico',
            'registro' => 'tutor/registro',
            'crud' => 'tutor/crud',
            'ficha' => 'tutor/ficha-pdf',
            'ficha-pdf' => 'tutor/ficha-pdf',
            'gps' => 'tutor/gpsqr',
            'gpsqr' => 'tutor/gpsqr',
            'gps-qr' => 'tutor/gpsqr',
            'seguimiento' => 'tutor/seguimiento',
            'estatus' => 'tutor/estatus',
            'albergues' => 'tutor/albergues',
            'perfil' => 'tutor/miperfil',
            'miperfil' => 'tutor/miperfil',
            'cuenta' => 'tutor/cuenta',
            'configuracion' => 'tutor/cuenta',
            'ayuda' => 'tutor/ayuda',
        ];

        if ($modulo === '' || !isset($map[$modulo])) {
            // fallback panel
            $this->panel($request);
            return;
        }

        $view = $map[$modulo];
        $data = $this->buildPanelData($uid);
        $secMap = [
            'analisis' => 'analisis', 'analitico' => 'analisis',
            'registro' => 'registro', 'crud' => 'crud',
            'ficha' => 'ficha-pdf', 'ficha-pdf' => 'ficha-pdf',
            'gps' => 'gps-qr', 'gpsqr' => 'gps-qr', 'gps-qr' => 'gps-qr',
            'seguimiento' => 'seguimiento', 'estatus' => 'estatus', 'albergues' => 'albergues',
            'perfil' => 'perfil', 'miperfil' => 'perfil',
            'cuenta' => 'configuracion', 'configuracion' => 'configuracion', 'ayuda' => 'ayuda',
        ];
        $data['modulo_activo'] = $modulo;
        $data['seccion_activa'] = $secMap[$modulo] ?? $modulo;
        if (!isset($data['mascotas']) || !is_array($data['mascotas'])) {
            $data['mascotas'] = $data['registros'] ?? [];
        }
        if (!isset($data['historial_seguimiento']) || !is_array($data['historial_seguimiento'])) {
            try {
                $data['historial_seguimiento'] = $this->seguimientos->listHistorialByTutor($uid) ?: [];
            } catch (\Throwable $e) {
                $data['historial_seguimiento'] = [];
            }
        }
        try {
            Response::view($view, $data);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'tutor modulo view ' . $view . ': ' . $e->getMessage());
            }
            // Si la vista falla, ir al panel principal
            $this->panel($request);
        }
    }

    /** Datos compartidos de todas las páginas tutor */
    private function buildPanelData(int $uid): array
    {
        $list = [];
        $stats = [];
        $bitacoras = [];
        $historial = [];
        $prefs = [];
        $alcaldias = [];
        $razas = [];
        $albergues = [];
        $userRow = null;

        try {
            $userRow = $this->usuarios->find($uid);
        } catch (\Throwable $e) {
        }

        try {
            $raw = $this->mascotas->listByTutor($uid);
            $stats = $this->mascotas->statsByTutor($uid) ?: [];
            foreach (is_array($raw) ? $raw : [] as $m) {
                if (!is_array($m)) {
                    continue;
                }
                if (function_exists('mascota_public_row')) {
                    $row = mascota_public_row($m);
                } else {
                    $row = function_exists('sanitize_data') ? sanitize_data($m, true) : $m;
                }
                $list[] = $row;
            }
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'tutor buildPanelData mascotas', ['msg' => $e->getMessage()]);
            }
        }

        try {
            $bitacoras = $this->seguimientos->listBitacoraByTutor($uid) ?: [];
        } catch (\Throwable $e) {
            $bitacoras = [];
        }
        try {
            $historial = $this->seguimientos->listHistorialByTutor($uid) ?: [];
        } catch (\Throwable $e) {
            $historial = [];
        }
        try {
            if (method_exists($this, 'preferencias') || isset($this->preferencias)) {
                // preferencias via DAO if available
            }
            if (class_exists(\App\Models\PreferenciaDAO::class)) {
                $prefs = (new \App\Models\PreferenciaDAO())->findByUsuario($uid) ?: [];
            }
        } catch (\Throwable $e) {
            $prefs = [];
        }
        try {
            $alcaldias = (new \App\Models\AlcaldiaDAO())->listActive() ?: [];
        } catch (\Throwable $e) {
        }
        try {
            $razas = (new \App\Models\RazaDAO())->listActive() ?: [];
        } catch (\Throwable $e) {
        }
        try {
            if (class_exists(\App\Models\AlbergueDAO::class)) {
                $albergues = (new \App\Models\AlbergueDAO())->listActive() ?: [];
            }
        } catch (\Throwable $e) {
        }

        return [
            'csrf' => function_exists('csrf_token') ? csrf_token() : '',
            'recaptchaSiteKey' => defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '',
            'user' => [
                'id' => $uid,
                'nombre' => (string) ($userRow['nombre'] ?? $_SESSION['user_name'] ?? ''),
                'correo' => (string) ($userRow['correo'] ?? $_SESSION['user_email'] ?? ''),
                'telefono' => (string) ($userRow['telefono'] ?? ''),
                'rol' => (string) ($_SESSION['user_role'] ?? ''),
            ],
            'mascotas' => $list,
            'bitacoras' => $bitacoras,
            'historial_seguimiento' => $historial,
            'stats' => is_array($stats) && function_exists('sanitize_data') ? sanitize_data($stats, true) : (is_array($stats) ? $stats : []),
            'alcaldias' => is_array($alcaldias) ? $alcaldias : [],
            'razas' => is_array($razas) ? $razas : [],
            'albergues' => is_array($albergues) ? $albergues : [],
            'preferencias' => is_array($prefs) ? $prefs : [],
            'app_url' => defined('APP_URL') ? rtrim((string) APP_URL, '/') : '',
            'modulo_activo' => '',
        ];
    }

    /** DELETE o POST soft-delete mascota del tutor */
    public function deleteMascota(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido', 419);
        }
        $id = (int) $request->param('id');
        $m = $this->mascotas->find($id);
        if (!$m || ((int) $m['id_tutor'] !== $uid && ($_SESSION['user_role'] ?? '') !== ROLE_ADMIN)) {
            throw new NotFoundException('Mascota no encontrada');
        }
        $this->mascotas->softDelete($id);
        $this->audit->log($uid, 'mascota_eliminar', 'mascotas', $id, ['folio' => (string) ($m['folio'] ?? '')]);
        Response::ok([], 'Registro eliminado');
    }

    public function listMascotas(Request $request): void
    {
        $uid = $this->requireTutor();
        $raw = $this->mascotas->listByTutor($uid);
        $stats = $this->mascotas->statsByTutor($uid) ?: [];
        $list = array_map(static function (array $m): array {
            $row = function_exists('mascota_public_row') ? mascota_public_row($m) : sanitize_data($m, true);
            $row['bitacora'] = (string) (($row['notas'] ?? '') !== '' ? $row['notas'] : 'Registro en RUPE CDMX');
            return $row;
        }, is_array($raw) ? $raw : []);
        $bitacoras = $this->seguimientos->listBitacoraByTutor($uid);
        $byId = [];
        foreach ($bitacoras as $b) {
            $byId[(int)$b['id_mascota']] = $b['bitacora'];
        }
        foreach ($list as &$row) {
            $idm = (int)($row['id_mascota'] ?? $row['id'] ?? 0);
            if (isset($byId[$idm])) {
                $row['bitacora'] = (string)$byId[$idm];
            }
        }
        unset($row);
        Response::ok([
            'mascotas' => $list,
            'bitacoras' => $bitacoras ?? [],
            'historial_seguimiento' => $this->seguimientos->listHistorialByTutor($uid),
            'stats' => sanitize_data(is_array($stats) ? $stats : [], true),
            'total' => count($list),
            'id_tutor' => $uid,
        ]);
    }

    public function getMascota(Request $request): void
    {
        $uid = $this->requireTutor();
        $id = (int) $request->param('id');
        $m = $this->mascotas->find($id);
        if (!$m || ((int) $m['id_tutor'] !== $uid && ($_SESSION['user_role'] ?? '') !== ROLE_ADMIN)) {
            throw new NotFoundException('Mascota no encontrada');
        }
        $full = $this->mascotas->findWithJoins($id) ?: $this->mascotas->findByFolio((string) ($m['folio'] ?? '')) ?: $m;
        $segs = $this->seguimientos->listByMascota($id);
        Response::ok([
            'mascota' => function_exists('mascota_public_row') ? mascota_public_row(is_array($full) ? $full : []) : sanitize_data(is_array($full) ? $full : [], true),
            'seguimientos' => array_map(static function (array $s): array {
                return sanitize_data($s, true);
            }, is_array($segs) ? $segs : []),
        ]);
    }

    public function createMascota(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }

        $input = $request->all();

        $nombre = trim((string) ($input['nombre'] ?? ''));
        if (mb_strlen($nombre) < 2) {
            Response::error('El nombre de la mascota es obligatorio (mín. 2 caracteres).', 422);
        }

        // IDs de catálogo — obligatorios para que el JOIN muestre Raza/Alcaldía
        $idRaza = (int) ($input['id_raza'] ?? 0);
        $idAlcaldia = (int) ($input['id_alcaldia'] ?? 0);
        if ($idRaza <= 0) {
            Response::error('Selecciona una raza del catálogo (id_raza).', 422);
        }
        if ($idAlcaldia <= 0) {
            Response::error('Selecciona una alcaldía del catálogo (id_alcaldia).', 422);
        }

        $especieRaw = strtolower(trim((string) ($input['especie'] ?? 'perro')));
        if (str_contains($especieRaw, 'gato')) {
            $especie = 'gato';
        } elseif (str_contains($especieRaw, 'otro')) {
            $especie = 'otro';
        } else {
            $especie = 'perro';
        }
        $sexoRaw = strtolower(trim((string) ($input['sexo'] ?? 'desconocido')));
        if (str_contains($sexoRaw, 'hembra')) {
            $sexo = 'hembra';
        } elseif (str_contains($sexoRaw, 'macho')) {
            $sexo = 'macho';
        } else {
            $sexo = 'desconocido';
        }

        $data = [
            'id_tutor' => $uid,
            'nombre' => $nombre,
            'id_raza' => $idRaza,
            'id_alcaldia' => $idAlcaldia,
            'especie' => $especie,
            'sexo' => $sexo,
            'color' => trim((string) ($input['color'] ?? '')),
            'edad_aprox' => trim((string) ($input['edad_aprox'] ?? '')),
            'senas_particulares' => trim((string) ($input['senas_particulares'] ?? '')),
            'collar' => trim((string) ($input['collar'] ?? '')),
            'microchip' => trim((string) ($input['microchip'] ?? $input['chip'] ?? '')),
            'tamano' => trim((string) ($input['tamano'] ?? '')),
            'colonia' => trim((string) ($input['colonia'] ?? '')),
            'direccion_ref' => trim((string) (
                $input['direccion_ref'] ??
                trim(
                    trim((string) ($input['calle'] ?? '')) .
                    (trim((string) ($input['calle'] ?? '')) !== '' && trim((string) ($input['referencia'] ?? '')) !== '' ? ', ' : '') .
                    trim((string) ($input['referencia'] ?? ''))
                )
            )),
            'fecha_extravio' => trim((string) ($input['fecha_extravio'] ?? '')),
            'hora_extravio' => trim((string) ($input['hora_extravio'] ?? $input['hora'] ?? '')),
            'notas' => trim((string) ($input['notas'] ?? '')),
            'estatus' => (string) ($input['estatus'] ?? (defined('STATUS_BUSQUEDA') ? STATUS_BUSQUEDA : 'En Búsqueda')),
            'latitud' => $input['latitud'] ?? '',
            'longitud' => $input['longitud'] ?? '',
        ];
        if ($data['fecha_extravio'] === '') {
            $data['fecha_extravio'] = null;
        }
        if (($data['hora_extravio'] ?? '') === '') {
            $data['hora_extravio'] = null;
        }
        if ($data['latitud'] === '' || $data['latitud'] === null) {
            $data['latitud'] = null;
        }
        if ($data['longitud'] === '' || $data['longitud'] === null) {
            $data['longitud'] = null;
        }
        if (!empty($input['id_albergue']) && (int) $input['id_albergue'] > 0) {
            $data['id_albergue'] = (int) $input['id_albergue'];
        } else {
            $data['id_albergue'] = null;
        }

        // Foto
        $data['foto_url'] = '';
        try {
            $file = $request->file('foto') ?: $request->file('foto_archivo');
            $base64 = null;
            if (!empty($input['foto_base64']) && is_string($input['foto_base64'])) {
                $base64 = $input['foto_base64'];
            } elseif (!empty($input['foto_url']) && is_string($input['foto_url']) && str_starts_with($input['foto_url'], 'data:image/')) {
                $base64 = $input['foto_url'];
            }
            $fotoUrl = save_mascota_image(is_array($file) ? $file : null, $base64);
            if ($fotoUrl) {
                $data['foto_url'] = $fotoUrl;
            }
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'upload foto failed', ['msg' => $e->getMessage()]);
            }
            $data['foto_url'] = '';
        }


        // Hora aprox. extravío (sin columna dedicada: va a senas + notas)
        $horaExtravio = trim((string) ($input['hora_extravio'] ?? $input['hora'] ?? ''));
        if ($horaExtravio !== '') {
            $senasNow = trim((string) ($data['senas_particulares'] ?? ''));
            if ($senasNow === '' || stripos($senasNow, 'Hora extravío') === false) {
                $data['senas_particulares'] = trim(
                    ($senasNow !== '' ? $senasNow . ' | ' : '') . 'Hora extravío: ' . $horaExtravio
                );
            }
            $notasNow = trim((string) ($data['notas'] ?? ''));
            if ($notasNow === '' || stripos($notasNow, 'Hora extravío') === false) {
                $data['notas'] = trim(
                    ($notasNow !== '' ? $notasNow . ' | ' : '') . 'Hora extravío: ' . $horaExtravio
                );
            }
        }

        // Teléfono del formulario: si el usuario no tiene teléfono en BD, actualizarlo
        $telForm = trim((string) ($input['telefono_tutor'] ?? $input['telefono'] ?? ''));
        if ($telForm !== '') {
            try {
                $uRow = $this->usuarios->find($uid);
                $telBd = trim((string) ($uRow['telefono'] ?? ''));
                if ($telBd === '') {
                    $this->usuarios->update($uid, [
                        'telefono' => function_exists('normalize_phone')
                            ? normalize_phone($telForm)
                            : $telForm,
                    ]);
                }
            } catch (\Throwable $e) {
            }
        }

        // Respaldo en senas si columnas nuevas no existen aún / para ficha legacy
        $metaBits = [];
        if ($data['tamano'] !== '') {
            $metaBits[] = 'Tamaño: ' . $data['tamano'];
        }
        if ($data['collar'] !== '') {
            $metaBits[] = 'Collar: ' . $data['collar'];
        }
        if ($data['microchip'] !== '') {
            $metaBits[] = 'Chip/tatuaje: ' . $data['microchip'];
        }
        if ($metaBits !== []) {
            $baseSenas = trim((string) ($data['senas_particulares'] ?? ''));
            $prefix = implode(' | ', $metaBits);
            $data['senas_particulares'] = $baseSenas !== '' ? ($prefix . ' | ' . $baseSenas) : $prefix;
        }
        // Nunca null en textos
        foreach (['color', 'edad_aprox', 'senas_particulares', 'collar', 'microchip', 'tamano', 'colonia', 'notas', 'nombre', 'foto_url'] as $tf) {
            if (!isset($data[$tf]) || $data[$tf] === null) {
                $data[$tf] = '';
            } else {
                $data[$tf] = trim((string) $data[$tf]);
            }
        }
        // Log para depuración
        if (function_exists('app_log')) {
            app_log('info', 'createMascota campos', [
                'collar' => $data['collar'],
                'microchip' => $data['microchip'],
                'tamano' => $data['tamano'],
                'color' => $data['color'],
                'sexo' => $data['sexo'] ?? '',
            ]);
        }

        try {
            $m = $this->mascotas->createAndFetch($data);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'createMascota failed', ['msg' => $e->getMessage()]);
            }
            Response::error('No se pudo guardar en base de datos: ' . $e->getMessage(), 500);
        }

        $id = (int) ($m['id_mascota'] ?? 0);
        // Releer CON JOINs para devolver raza_nombre / alcaldia_nombre
        $full = $id > 0 ? ($this->mascotas->findWithJoins($id) ?: $m) : $m;
        $folio = (string) ($full['folio'] ?? $m['folio'] ?? '');
        $qr = (string) ($full['qr_token'] ?? $m['qr_token'] ?? '');

        try {
            $this->seguimientos->create([
                'id_mascota' => $id,
                'id_usuario' => $uid,
                'folio' => $folio,
                'condicion' => 'Registro inicial',
                'referencia' => 'Folio ' . $folio,
                'contacto' => (string) ($_SESSION['user_name'] ?? 'Tutor'),
            ]);
        } catch (\Throwable $e) {
        }
        try {
            $this->audit->log($uid, 'mascota_crear', 'mascotas', $id, [
                'folio' => $folio,
                'id_raza' => $idRaza,
                'id_alcaldia' => $idAlcaldia,
            ]);
        } catch (\Throwable $e) {
        }

        $public = function_exists('mascota_public_row')
            ? mascota_public_row(is_array($full) ? $full : [])
            : (is_array($full) ? $full : []);
        if (($public['raza'] ?? '') === '' && !empty($full['raza_nombre'])) {
            $public['raza'] = (string) $full['raza_nombre'];
            $public['raza_nombre'] = $public['raza'];
        }
        if (($public['alcaldia'] ?? '') === '' && !empty($full['alcaldia_nombre'])) {
            $public['alcaldia'] = (string) $full['alcaldia_nombre'];
            $public['alcaldia_nombre'] = $public['alcaldia'];
        }
        if (function_exists('mascota_foto_src')) {
            $public['foto'] = mascota_foto_src($public['foto_url'] ?? null, (string) ($public['especie'] ?? 'perro'));
            $public['foto_url'] = $public['foto'];
        }

        Response::ok([
            'mascota'    => $public,
            'folio'      => $folio,
            'qr_token'   => $qr,
            'id_mascota' => $id,
            'foto_url'   => (string) ($public['foto_url'] ?? ''),
            'qr_url'     => $qr !== '' ? (rtrim((string) APP_URL, '/') . '/mascota/' . rawurlencode($qr) . '?avistar=1') : '',
        ], 'Mascota registrada', 201);
    }

    public function updateMascota(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página e intenta de nuevo.', 419);
        }
        $id = (int) $request->param('id');
        $m = $this->mascotas->find($id);
        if (!$m || ((int) ($m['id_tutor'] ?? 0) !== $uid && ($_SESSION['user_role'] ?? '') !== ROLE_ADMIN)) {
            throw new NotFoundException('Mascota no encontrada');
        }

        $input = $request->all();

        // Combinar Calle + Referencia → direccion_ref si vienen en el request
        if (array_key_exists('calle', $input) || array_key_exists('referencia', $input)) {
            $calle = trim((string) ($input['calle'] ?? ''));
            $ref = trim((string) ($input['referencia'] ?? ''));
            if ($calle !== '' && $ref !== '') {
                $input['direccion_ref'] = 'Calle: ' . $calle . ' | Ref: ' . $ref;
            } elseif ($calle !== '') {
                $input['direccion_ref'] = 'Calle: ' . $calle;
            } elseif ($ref !== '') {
                $input['direccion_ref'] = 'Ref: ' . $ref;
            }
        }

        // Campos a actualizar (sin nulls en texto)
        $data = [];
        if (isset($input['nombre'])) {
            $nombre = trim((string) $input['nombre']);
            if (mb_strlen($nombre) < 2) {
                Response::error('El nombre debe tener al menos 2 caracteres.', 422);
            }
            $data['nombre'] = $nombre;
        }
        if (array_key_exists('id_raza', $input) && $input['id_raza'] !== '' && $input['id_raza'] !== null) {
            $ir = (int) $input['id_raza'];
            if ($ir > 0) {
                $data['id_raza'] = $ir;
            }
        }
        if (array_key_exists('id_alcaldia', $input) && $input['id_alcaldia'] !== '' && $input['id_alcaldia'] !== null) {
            $ia = (int) $input['id_alcaldia'];
            if ($ia > 0) {
                $data['id_alcaldia'] = $ia;
            }
        }
        if (array_key_exists('id_albergue', $input)) {
            // null, '', 0 → sin albergue; >0 → asignar
            $rawAlb = $input['id_albergue'];
            if ($rawAlb === null || $rawAlb === '' || $rawAlb === false) {
                $data['id_albergue'] = null;
            } else {
                $ib = (int) $rawAlb;
                $data['id_albergue'] = $ib > 0 ? $ib : null;
            }
        }
        // Campos opcionales: no sobrescribir con vacío (protege ficha: sexo, tamaño, collar, chip, color…)
        foreach (['color', 'edad_aprox', 'senas_particulares', 'collar', 'microchip', 'tamano', 'colonia', 'direccion_ref', 'notas', 'especie', 'sexo', 'estatus'] as $sf) {
            if (!array_key_exists($sf, $input)) {
                continue;
            }
            $v = $input[$sf];
            $trim = ($v === null || $v === false) ? '' : trim((string) $v);
            if ($trim === '' && !in_array($sf, ['notas', 'estatus'], true)) {
                continue;
            }
            $data[$sf] = $trim;
        }
        if (array_key_exists('fecha_extravio', $input)) {
            $fe = trim((string) ($input['fecha_extravio'] ?? ''));
            $data['fecha_extravio'] = $fe !== '' ? $fe : null;
        }
        if (array_key_exists('latitud', $input) || array_key_exists('longitud', $input)) {
            $lat = $input['latitud'] ?? null;
            $lng = $input['longitud'] ?? null;
            $data['latitud'] = ($lat === '' || $lat === null) ? null : $lat;
            $data['longitud'] = ($lng === '' || $lng === null) ? null : $lng;
        }

        // Foto opcional
        try {
            $file = $request->file('foto');
            if (!$file) {
                $file = $request->file('foto_archivo');
            }
            $base64 = null;
            if (!empty($input['foto_base64']) && is_string($input['foto_base64'])) {
                $base64 = $input['foto_base64'];
            }
            $fotoUrl = save_mascota_image(is_array($file) ? $file : null, $base64);
            if ($fotoUrl) {
                $data['foto_url'] = $fotoUrl;
            }
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'upload foto update failed', ['msg' => $e->getMessage()]);
            }
        }

        if ($data === []) {
            Response::error('No hay datos para actualizar.', 422);
        }

        $oldStatus = (string) ($m['estatus'] ?? '');
        $ok = $this->mascotas->update($id, $data);
        if (!$ok && $ok !== false) {
            // updateById puede devolver true incluso con 0 rows
        }

        $updated = $this->mascotas->findWithJoins($id) ?: $this->mascotas->find($id);
        if (!is_array($updated)) {
            Response::error('Se guardó pero no se pudo releer el registro.', 500);
        }

        if (isset($data['estatus']) && $data['estatus'] !== '' && $data['estatus'] !== $oldStatus) {
            try {
                $this->seguimientos->create([
                    'id_mascota' => $id,
                    'id_usuario' => $uid,
                    'folio' => (string) ($m['folio'] ?? ''),
                    'condicion' => (string) ($data['estatus'] ?? 'En Búsqueda'),
                    'referencia' => "Estatus: {$oldStatus} → {$data['estatus']}",
                    'contacto' => (string) ($_SESSION['user_name'] ?? 'Tutor'),
                ]);
            } catch (\Throwable $e) {
                // no bloquear
            }
        }

        try {
            $this->audit->log($uid, 'mascota_actualizar', 'mascotas', $id, [
                'campos' => array_keys($data),
            ]);
        } catch (\Throwable $e) {
        }

        $public = function_exists('mascota_public_row')
            ? mascota_public_row($updated)
            : $updated;

        // Garantizar raza visible
        if (($public['raza'] ?? '') === '' && ($updated['raza_nombre'] ?? '') !== '') {
            $public['raza'] = (string) $updated['raza_nombre'];
            $public['raza_nombre'] = $public['raza'];
        }
        if (($public['alcaldia'] ?? '') === '' && ($updated['alcaldia_nombre'] ?? '') !== '') {
            $public['alcaldia'] = (string) $updated['alcaldia_nombre'];
            $public['alcaldia_nombre'] = $public['alcaldia'];
        }
        if (function_exists('mascota_foto_src')) {
            $public['foto'] = mascota_foto_src($public['foto_url'] ?? null, (string) ($public['especie'] ?? 'perro'));
            $public['foto_url'] = $public['foto'];
        }

        Response::ok(['mascota' => $public], 'Registro actualizado correctamente');
    }

    public function addSeguimiento(Request $request): void
    {
        $uid = $this->requireTutor();
        $allTmp = $request->all();
        $csrfTok = $request->csrf();
        if (!csrf_validate($csrfTok) && isset($allTmp['_csrf'])) {
            $csrfTok = (string) $allTmp['_csrf'];
        }
        if (!csrf_validate($csrfTok) && isset($allTmp['csrf'])) {
            $csrfTok = (string) $allTmp['csrf'];
        }
        if (!csrf_validate($csrfTok)) {
            if (function_exists('app_log')) {
                app_log('warning', 'addSeguimiento CSRF fail');
            }
            Response::error('Token CSRF inválido. Recarga la página (Ctrl+Shift+R) e inicia sesión.', 419);
        }
        $id = (int) $request->param('id');
        $m = $this->mascotas->find($id);
        if (!$m || ((int) $m['id_tutor'] !== $uid && ($_SESSION['user_role'] ?? '') !== ROLE_ADMIN)) {
            throw new NotFoundException();
        }
        $inputAll = $request->all();
        $condicion = trim((string) ($inputAll['condicion'] ?? ''));
        if ($condicion === '') {
            Response::error('La condición del animal es obligatoria.', 422);
        }
        $latIn = $inputAll['latitud'] ?? null;
        $lngIn = $inputAll['longitud'] ?? null;
        if ($latIn === '' || $latIn === false) {
            $latIn = null;
        }
        if ($lngIn === '' || $lngIn === false) {
            $lngIn = null;
        }

        // Foto reportada (archivo o base64) → seguimientos.foto_url
        $fotoUrl = '';
        try {
            $file = $request->file('foto') ?: $request->file('foto_archivo') ?: $request->file('gpsFotoArchivo');
            $base64 = null;
            if (!empty($inputAll['foto_base64']) && is_string($inputAll['foto_base64'])) {
                $base64 = $inputAll['foto_base64'];
            } elseif (!empty($inputAll['foto_url']) && is_string($inputAll['foto_url']) && str_starts_with($inputAll['foto_url'], 'data:image/')) {
                $base64 = $inputAll['foto_url'];
            }
            if ((is_array($file) && !empty($file['tmp_name'])) || $base64) {
                if (function_exists('save_mascota_image')) {
                    $saved = save_mascota_image(is_array($file) ? $file : null, $base64);
                    if ($saved) {
                        $fotoUrl = $saved;
                    }
                }
            }
            // Si no hay foto nueva, usar la de la mascota como referencia
            if ($fotoUrl === '') {
                $fotoUrl = (string) ($m['foto_url'] ?? '');
            }
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'foto avistamiento failed', ['msg' => $e->getMessage()]);
            }
        }

        try {
            $sid = $this->seguimientos->create([
                'id_mascota' => $id,
                'id_usuario' => $uid,
                'folio' => (string) ($m['folio'] ?? $inputAll['folio'] ?? ''),
                'condicion' => $condicion,
                'referencia' => trim((string) ($inputAll['referencia'] ?? '')),
                'contacto' => trim((string) ($inputAll['contacto'] ?? $inputAll['reportado_por'] ?? $inputAll['reporteroNombre'] ?? '')),
                'telefono' => (string) ($inputAll['telefono'] ?? $inputAll['reporteroTel'] ?? ''),
                'correo' => trim((string) ($inputAll['correo'] ?? $inputAll['reporteroEmail'] ?? '')),
                'latitud' => $latIn,
                'longitud' => $lngIn,
                'foto_url' => $fotoUrl !== '' ? $fotoUrl : null,
            ]);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'seguimiento create failed', ['msg' => $e->getMessage()]);
            }
            Response::error('No se pudo guardar el avistamiento en la base de datos: ' . $e->getMessage(), 500);
        }
        if (!is_int($sid) || $sid <= 0) {
            Response::error('No se generó ID de seguimiento. Intenta de nuevo.', 500);
        }

        // Guardar también en mascotas para que la ficha muestre "GPS (último)"
        if ($latIn !== null && $lngIn !== null && is_numeric($latIn) && is_numeric($lngIn)) {
            try {
                $this->mascotas->updateGps($id, $latIn, $lngIn);
            } catch (\Throwable $e) {
                if (function_exists('app_log')) {
                    app_log('error', 'gps update mascota failed', ['msg' => $e->getMessage()]);
                }
            }
        }

        try {
            $this->audit->log($uid, 'seguimiento_crear', 'seguimientos', $sid, [
                'latitud' => $latIn,
                'longitud' => $lngIn,
                'foto_url' => $fotoUrl,
            ]);
        } catch (\Throwable $e) {
            // no bloquear respuesta
        }

        $updated = $this->mascotas->findWithJoins($id) ?: $this->mascotas->find($id);
        $public = function_exists('mascota_public_row')
            ? mascota_public_row(is_array($updated) ? $updated : [])
            : (is_array($updated) ? $updated : []);

        // Fila lista para la tabla de seguimiento
        $rowSeg = [
            'id_seguimiento' => $sid,
            'id' => $sid,
            'id_mascota' => $id,
            'folio' => (string) ($m['folio'] ?? ''),
            'condicion' => $condicion,
            'referencia' => trim((string) ($inputAll['referencia'] ?? '')),
            'contacto' => trim((string) ($inputAll['contacto'] ?? $inputAll['reportado_por'] ?? '')),
            'telefono' => (string) ($inputAll['telefono'] ?? ''),
            'correo' => trim((string) ($inputAll['correo'] ?? '')),
            'latitud' => $latIn !== null ? (string) $latIn : '',
            'longitud' => $lngIn !== null ? (string) $lngIn : '',
            'foto_url' => $fotoUrl,
            'foto' => $fotoUrl,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        Response::ok([
            'id_seguimiento' => $sid,
            'seguimiento' => $rowSeg,
            'mascota' => $public,
            'latitud' => $latIn !== null ? (string) $latIn : '',
            'longitud' => $lngIn !== null ? (string) $lngIn : '',
            'foto_url' => $fotoUrl,
        ], 'Avistamiento guardado en la base de datos', 201);
    }

    /** POST /api/tutor/mascotas/{id}/gps — guardar solo GPS en ficha */
    public function updateGpsMascota(Request $request): void
    {
        $uid = $this->requireTutor();
        // CSRF: intentar continuar si token en body/header
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página (F5).', 419);
        }
        $id = (int) $request->param('id');
        if ($id <= 0) {
            Response::error('ID de mascota inválido', 422);
        }
        $m = $this->mascotas->find($id);
        if (!$m || ((int) ($m['id_tutor'] ?? 0) !== $uid && ($_SESSION['user_role'] ?? '') !== ROLE_ADMIN)) {
            throw new NotFoundException('Mascota no encontrada');
        }
        $input = $request->all();
        // También aceptar lat/lng como lat/lng o gps_lat/gps_lng
        $lat = $input['latitud'] ?? $input['lat'] ?? $input['gps_lat'] ?? null;
        $lng = $input['longitud'] ?? $input['lng'] ?? $input['lon'] ?? $input['gps_lng'] ?? null;
        if (is_string($lat)) {
            $lat = str_replace(',', '.', trim($lat));
        }
        if (is_string($lng)) {
            $lng = str_replace(',', '.', trim($lng));
        }
        if ($lat === null || $lng === null || $lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            Response::error('latitud y longitud numéricas son obligatorias', 422);
        }
        $ok = $this->mascotas->updateGps($id, $lat, $lng);
        if (!$ok) {
            // Segundo intento vía update genérico
            try {
                $ok = $this->mascotas->update($id, [
                    'latitud' => (string) $lat,
                    'longitud' => (string) $lng,
                ]);
            } catch (\Throwable $e) {
                $ok = false;
            }
        }
        if (!$ok) {
            Response::error('No se pudo guardar el GPS en mascotas', 500);
        }
        // Verificar lectura
        $check = $this->mascotas->find($id);
        if (function_exists('app_log')) {
            app_log('info', 'GPS guardado', [
                'id' => $id,
                'lat' => $lat,
                'lng' => $lng,
                'db_lat' => $check['latitud'] ?? null,
                'db_lng' => $check['longitud'] ?? null,
            ]);
        }
        // No crear fila en seguimientos aquí (evita duplicar avistamiento)
        $updated = $this->mascotas->findWithJoins($id) ?: $this->mascotas->find($id);
        $public = function_exists('mascota_public_row')
            ? mascota_public_row(is_array($updated) ? $updated : [])
            : (is_array($updated) ? $updated : []);
        Response::ok([
            'mascota' => $public,
            'latitud' => (string) $lat,
            'longitud' => (string) $lng,
        ], 'GPS guardado en la ficha');
    }

    public function perfil(Request $request): void
    {
        $uid = $this->requireTutor();
        Response::ok([
            'user' => $this->usuarios->toPublic($this->usuarios->find($uid) ?? []),
            'preferencias' => $this->prefs->findByUsuario($uid),
            'stats' => $this->mascotas->statsByTutor($uid),
        ]);
    }

    public function updatePerfil(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }
        $input = $request->all();
        $nombre = trim((string) ($input['nombre'] ?? ''));
        $correo = trim((string) ($input['correo'] ?? ''));
        $telefono = trim((string) ($input['telefono'] ?? ''));
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 120) {
            Response::error('El nombre debe tener entre 2 y 120 caracteres.', 422);
        }
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Response::error('Correo electrónico no válido.', 422);
        }
        $existing = $this->usuarios->findByEmail($correo);
        if ($existing && (int) ($existing['id_usuario'] ?? 0) !== $uid) {
            Response::error('El correo ya está en uso por otra cuenta.', 422);
        }
        $payload = [
            'nombre' => $nombre,
            'correo' => $correo,
        ];
        if ($telefono !== '') {
            $payload['telefono'] = function_exists('normalize_phone')
                ? normalize_phone($telefono)
                : preg_replace('/\D+/', '', $telefono);
        }
        if (array_key_exists('acepta_privacidad', $input)) {
            $v = $input['acepta_privacidad'];
            $payload['acepta_privacidad'] = (!empty($v) && $v !== '0' && $v !== 'false') ? 1 : 0;
        }
        $this->usuarios->update($uid, $payload);
        if (!empty($payload['nombre'])) {
            $_SESSION['user_name'] = $payload['nombre'];
        }
        if (!empty($payload['correo'])) {
            $_SESSION['user_email'] = $payload['correo'];
        }
        try {
            $this->audit->log($uid, 'perfil_actualizar');
        } catch (\Throwable $e) {
            // no bloquear
        }
        $user = $this->usuarios->toPublic($this->usuarios->find($uid) ?? []);
        Response::ok(['user' => $user], 'Perfil actualizado');
    }

    public function getPreferencias(Request $request): void
    {
        $uid = $this->requireTutor();
        $row = $this->prefs->findByUsuario($uid) ?: [];
        Response::ok([
            'preferencias' => is_array($row) ? $row : [],
        ]);
    }

    public function updatePreferencias(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }
        $in = $request->all();
        // Mapear nombres del front (camelCase) a columnas BD
        $payload = [
            'notif_email' => (int) (!empty($in['notif_email'] ?? $in['notifEmail'] ?? 0) ? 1 : 0),
            'notif_sms' => (int) (!empty($in['notif_sms'] ?? $in['notifSms'] ?? 0) ? 1 : 0),
            'notif_avistamiento' => (int) (!empty($in['notif_avistamiento'] ?? $in['notifNuevoAvistamiento'] ?? $in['notif_avistamiento'] ?? 0) ? 1 : 0),
            'notif_cambio_estatus' => (int) (!empty($in['notif_cambio_estatus'] ?? $in['notifCambioEstatus'] ?? 0) ? 1 : 0),
            'notif_boletin' => (int) (!empty($in['notif_boletin'] ?? $in['notifBoletinRUPE'] ?? $in['notifBoletin'] ?? 0) ? 1 : 0),
            'idioma' => trim((string) ($in['idioma'] ?? 'es')) ?: 'es',
            'id_alcaldia_default' => null,
        ];
        // Checkboxes pueden llegar como "1"/"0" o true/false
        foreach (['notif_email', 'notif_sms', 'notif_avistamiento', 'notif_cambio_estatus', 'notif_boletin'] as $k) {
            $v = $in[$k] ?? null;
            if ($v === null) {
                // try camel
                continue;
            }
            $payload[$k] = ($v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'on') ? 1 : 0;
        }
        $idAlc = $in['id_alcaldia_default'] ?? $in['alcaldiaDefault'] ?? $in['alcaldia_default'] ?? null;
        if ($idAlc !== null && $idAlc !== '') {
            if (is_numeric($idAlc)) {
                $payload['id_alcaldia_default'] = (int) $idAlc;
            } else {
                // nombre de alcaldía → buscar id
                try {
                    $alc = (new AlcaldiaDAO())->listActive();
                    foreach ($alc as $a) {
                        if (strcasecmp((string) ($a['nombre'] ?? ''), (string) $idAlc) === 0) {
                            $payload['id_alcaldia_default'] = (int) ($a['id_alcaldia'] ?? 0) ?: null;
                            break;
                        }
                    }
                } catch (\Throwable $e) {
                    $payload['id_alcaldia_default'] = null;
                }
            }
        }
        $this->prefs->upsert($uid, $payload);
        Response::ok([
            'preferencias' => $this->prefs->findByUsuario($uid) ?: [],
        ], 'Preferencias guardadas');
    }


    /** PUT /api/tutor/password */
    public function changePassword(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }
        $in = $request->all();
        $actual = (string) ($in['password_actual'] ?? $in['cfgPassActual'] ?? '');
        $nueva = (string) ($in['password'] ?? $in['password_nueva'] ?? $in['cfgPassNueva'] ?? '');
        $conf = (string) ($in['password_confirmation'] ?? $in['password_confirm'] ?? $in['cfgPassConfirmar'] ?? '');
        if ($actual === '' || $nueva === '' || $conf === '') {
            Response::error('Completa contraseña actual, nueva y confirmación.', 422);
        }
        if (mb_strlen($nueva) < 8) {
            Response::error('La nueva contraseña debe tener al menos 8 caracteres.', 422);
        }
        if (!preg_match('/[A-Z]/', $nueva)) {
            Response::error('La contraseña debe incluir al menos una letra mayúscula.', 422);
        }
        if (!preg_match('/[a-z]/', $nueva)) {
            Response::error('La contraseña debe incluir al menos una letra minúscula.', 422);
        }
        if (!preg_match('/\d/', $nueva)) {
            Response::error('La contraseña debe incluir al menos un número.', 422);
        }
        if (!preg_match('/[^A-Za-z0-9]/', $nueva)) {
            Response::error('La contraseña debe incluir al menos un carácter especial (ej. ! @ # $ %).', 422);
        }
        if ($nueva !== $conf) {
            Response::error('La nueva contraseña y su confirmación no coinciden.', 422);
        }
        $user = $this->usuarios->find($uid);
        $hash = (string) ($user['password_hash'] ?? $user['contrasena_hash'] ?? '');
        if (!$user || $hash === '' || !verify_password($actual, $hash)) {
            Response::error('La contraseña actual no es correcta.', 422);
        }
        $this->usuarios->update($uid, ['password_hash' => hash_password($nueva)]);
        try {
            $this->audit->log($uid, 'password_change');
        } catch (\Throwable $e) {
        }
        Response::ok([], 'Contraseña actualizada correctamente');
    }


    /** POST /api/tutor/ayuda-contacto — mensaje de soporte del tutor autenticado */
    public function contactoAyuda(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!csrf_validate($request->csrf())) {
            Response::error('Token CSRF inválido. Recarga la página.', 419);
        }
        if (!rate_limit_check('tutor_ayuda', 8, 600)) {
            Response::error('Has enviado demasiados mensajes. Intenta más tarde.', 429);
        }

        $in = $request->all();
        $asunto = trim((string) ($in['asunto'] ?? ''));
        $mensaje = trim((string) ($in['mensaje'] ?? ''));
        if (mb_strlen($asunto) < 3) {
            Response::error('Selecciona o escribe un asunto.', 422);
        }
        if (mb_strlen($mensaje) < 10) {
            Response::error('El mensaje debe tener al menos 10 caracteres.', 422);
        }
        if (mb_strlen($mensaje) > 4000) {
            Response::error('El mensaje es demasiado largo.', 422);
        }

        $user = $this->usuarios->find($uid) ?: [];
        $nombre = (string) ($user['nombre'] ?? $_SESSION['user_name'] ?? 'Tutor');
        $correo = (string) ($user['correo'] ?? $_SESSION['user_email'] ?? '');
        $telefono = (string) ($user['telefono'] ?? '');

        $destino = defined('MAIL_FROM') ? MAIL_FROM : 'admin@buscandoahuellitasrupe.com.mx';
        $html = '
          <p><strong>Mensaje de soporte — Panel Tutor RUPE CDMX</strong></p>
          <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <tr><td style="padding:6px 0;color:#666;">Tutor</td><td style="padding:6px 0;"><strong>' . htmlspecialchars($nombre) . '</strong></td></tr>
            <tr><td style="padding:6px 0;color:#666;">ID usuario</td><td style="padding:6px 0;">' . (int) $uid . '</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Correo</td><td style="padding:6px 0;">' . htmlspecialchars($correo ?: '—') . '</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Teléfono</td><td style="padding:6px 0;">' . htmlspecialchars($telefono ?: '—') . '</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Asunto</td><td style="padding:6px 0;">' . htmlspecialchars($asunto) . '</td></tr>
          </table>
          <hr style="border:none;border-top:1px solid #eee;margin:16px 0;">
          <p style="white-space:pre-wrap;">' . nl2br(htmlspecialchars($mensaje)) . '</p>
          <p style="color:#999;font-size:12px;margin-top:24px;">Sitio: buscandoahuellitasrupe.com.mx · IP: ' . htmlspecialchars(function_exists('client_ip') ? client_ip() : '') . '</p>
        ';

        $ok = false;
        try {
            $ok = Mailer::send(
                $destino,
                '[RUPE Ayuda Tutor] ' . $asunto,
                $html,
                'RUPE Soporte'
            );
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'contactoAyuda mail exception', ['msg' => $e->getMessage()]);
            }
            $ok = false;
        }

        // Copia de cortesía
        if ($ok && $correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            try {
                Mailer::send(
                    $correo,
                    'Recibimos tu mensaje — RUPE CDMX',
                    '<p>Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
                     <p>Recibimos tu mensaje sobre <em>' . htmlspecialchars($asunto) . '</em>.</p>
                     <p>El equipo de <strong>buscandoahuellitasrupe.com.mx</strong> te responderá a este correo si es necesario.</p>
                     <p>Gracias por usar RUPE CDMX.</p>',
                    $nombre
                );
            } catch (\Throwable $e) {
                // no bloquear
            }
        }

        if (!$ok) {
            Response::error('No se pudo enviar el mensaje por correo. Intenta más tarde o escribe a admin@buscandoahuellitasrupe.com.mx', 500);
        }

        try {
            $this->audit->log($uid, 'ayuda_contacto', null, null, ['asunto' => $asunto]);
        } catch (\Throwable $e) {
        }

        Response::ok([], 'Mensaje enviado. Te responderemos pronto a tu correo registrado.');
    }

    public function catalogos(Request $request): void
    {
        $tipo = (string) $request->param('tipo');
        match ($tipo) {
            'alcaldias' => Response::ok((new AlcaldiaDAO())->listActive()),
            'razas' => Response::ok((new RazaDAO())->listActive($request->query('especie'))),
            'albergues' => Response::ok((new AlbergueDAO())->listActive(
                $request->query('id_alcaldia') ? (int) $request->query('id_alcaldia') : null
            )),
            'ayuda' => Response::ok((new AyudaDAO())->listVisible($_SESSION['user_role'] ?? 'tutor')),
            default => Response::error('Catálogo no válido', 404),
        };
    }

    public function geocode(Request $request): void
    {
        $this->requireTutor();
        if (!rate_limit_check('geocode', 30, 60)) {
            Response::error('Límite de geocodificación', 429);
        }
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 3) {
            Response::error('Consulta muy corta', 422);
        }
        $url = NOMINATIM_URL . '/search?' . http_build_query([
            'format' => 'json', 'limit' => 5, 'countrycodes' => 'mx',
            'q' => $q, 'email' => NOMINATIM_EMAIL,
        ]);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: ' . NOMINATIM_USER_AGENT . "\r\n",
                'timeout' => 10,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        $data = $raw ? json_decode($raw, true) : [];
        Response::ok(is_array($data) ? $data : []);
    }


    public function listAlbergues(Request $request): void
    {
        $this->requireTutor();
        try {
            $idAlc = $request->query('id_alcaldia') !== null && $request->query('id_alcaldia') !== ''
                ? (int) $request->query('id_alcaldia') : null;
            $rows = (new AlbergueDAO())->listAll($idAlc, false); // activos e inactivos
            if (!is_array($rows)) {
                $rows = [];
            }
            // Asegurar IDs numéricos y sin nulls en strings
            $out = [];
            foreach ($rows as $r) {
                if (!is_array($r)) {
                    continue;
                }
                $id = (int) ($r['id_albergue'] ?? $r['id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }
                $correo = trim((string) ($r['correo'] ?? $r['email'] ?? ''));
                $alcNom = trim((string) ($r['alcaldia_nombre'] ?? $r['alcaldia'] ?? ''));
                $out[] = [
                    'id_albergue' => $id,
                    'id' => $id,
                    'nombre' => (string) ($r['nombre'] ?? ''),
                    'direccion' => (string) ($r['direccion'] ?? ''),
                    'id_alcaldia' => (int) ($r['id_alcaldia'] ?? 0),
                    'alcaldia' => $alcNom,
                    'alcaldia_nombre' => $alcNom,
                    'telefono' => (string) ($r['telefono'] ?? ''),
                    'correo' => $correo,
                    'email' => $correo,
                    'latitud' => isset($r['latitud']) && $r['latitud'] !== null && $r['latitud'] !== '' ? (float) $r['latitud'] : null,
                    'longitud' => isset($r['longitud']) && $r['longitud'] !== null && $r['longitud'] !== '' ? (float) $r['longitud'] : null,
                    'capacidad' => (int) ($r['capacidad'] ?? 0),
                    'ocupacion' => (int) ($r['ocupacion'] ?? 0),
                    'activo' => (int) ($r['activo'] ?? 1),
                    'tipo' => (string) (($r['tipo'] ?? '') !== '' ? $r['tipo'] : 'Albergue'),
                ];
            }
            Response::ok($out);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'listAlbergues: ' . $e->getMessage());
            }
            Response::ok([]);
        }
    }

    public function createAlbergue(Request $request): void
    {
        $this->requireTutor();
        $input = $request->all();
        try {
            $id = (new AlbergueDAO())->create([
                'nombre'      => $input['nombre'] ?? '',
                'tipo'        => $input['tipo'] ?? 'Albergue',
                'direccion'   => $input['direccion'] ?? '',
                'id_alcaldia' => $input['id_alcaldia'] ?? ($input['alcaldia_id'] ?? 0),
                'telefono'    => $input['telefono'] ?? '',
                'correo'      => $input['correo'] ?? '',
                'latitud'     => $input['latitud'] ?? null,
                'longitud'    => $input['longitud'] ?? null,
                'capacidad'   => $input['capacidad'] ?? null,
                'activo'      => isset($input['activo']) ? ((int) $input['activo'] ? 1 : 0) : 1,
            ]);
            $row = (new AlbergueDAO())->findById((int) $id);
            Response::ok($row ?: ['id_albergue' => (int) $id], 'Albergue guardado', 201);
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Response::error('No se pudo guardar el albergue: ' . $e->getMessage(), 500);
        }
    }

    public function updateAlbergue(Request $request): void
    {
        $this->requireTutor();
        $id = (int) ($request->param('id') ?? 0);
        if ($id <= 0) {
            Response::error('ID de albergue inválido', 422);
        }
        $input = $request->all();
        try {
            $ok = (new AlbergueDAO())->update($id, $input);
            if (!$ok) {
                Response::error('No se actualizó el albergue', 404);
            }
            $row = (new AlbergueDAO())->findById($id);
            Response::ok($row, 'Albergue actualizado');
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Response::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function deleteAlbergue(Request $request): void
    {
        $this->requireTutor();
        $id = (int) ($request->param('id') ?? 0);
        if ($id <= 0) {
            Response::error('ID de albergue inválido', 422);
        }
        $dao = new AlbergueDAO();
        $row = $dao->findById($id);
        if (!$row) {
            Response::error('Albergue no encontrado', 404);
        }
        if ((int) ($row['ocupacion'] ?? 0) > 0) {
            Response::error('No se puede eliminar: tiene casos en resguardo asignados.', 422);
        }
        // Eliminación real: la baja lógica (activo=0) dejaba el albergue en el directorio como "Inactivo"
        // (listAlbergues incluye inactivos), por lo que parecía que "eliminar" no hacía nada.
        // mascotas.id_albergue tiene ON DELETE SET NULL, así que no se pierden registros de mascotas.
        try {
            $deleted = $dao->delete($id);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'deleteAlbergue: ' . $e->getMessage());
            }
            Response::error('No se pudo eliminar el albergue (tiene registros relacionados).', 409);
            return;
        }
        if (!$deleted) {
            Response::error('No se pudo eliminar el albergue.', 500);
            return;
        }
        Response::ok(['id_albergue' => $id], 'Albergue eliminado');
    }


    /**
     * POST /api/tutor/mensaje-ciudadano
     * Envía mensaje de agradecimiento / respuesta al ciudadano (correo).
     */
    public function mensajeCiudadano(Request $request): void
    {
        $uid = $this->requireTutor();
        if (!rate_limit_check('tutor_msg_ciudadano', 10, 60)) {
            Response::error('Demasiados mensajes. Espera un momento.', 429);
        }

        $in = $request->all();
        if (!is_array($in) || $in === []) {
            $raw = file_get_contents('php://input');
            $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
            $in = is_array($decoded) ? $decoded : [];
        }

        $correo = trim((string) ($in['correo'] ?? $in['email'] ?? ''));
        $nombre = trim((string) ($in['nombre'] ?? $in['contacto'] ?? 'Ciudadano'));
        $folio = trim((string) ($in['folio'] ?? ''));
        $mensaje = trim((string) ($in['mensaje'] ?? ''));
        $asunto = trim((string) ($in['asunto'] ?? ''));

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Response::error('Indica un correo válido del ciudadano.', 422);
        }
        if (mb_strlen($mensaje) < 100) {
            Response::error('El mensaje debe tener al menos 100 caracteres.', 422);
        }
        if (mb_strlen($mensaje) > 2000) {
            $mensaje = mb_substr($mensaje, 0, 2000);
        }
        if ($asunto === '') {
            $asunto = 'Gracias por tu avistamiento' . ($folio !== '' ? ' — Folio ' . $folio : '') . ' · RUPE CDMX';
        }
        if (mb_strlen($nombre) > 80) {
            $nombre = mb_substr($nombre, 0, 80);
        }

        $tutorNombre = 'Tutor RUPE';
        try {
            $u = $this->usuarios->find($uid);
            if (is_array($u) && !empty($u['nombre'])) {
                $tutorNombre = (string) $u['nombre'];
            }
        } catch (\Throwable $e) {
        }

        $html = '
          <div style="font-family:system-ui,sans-serif;max-width:560px;margin:0 auto;">
            <h2 style="color:#6B1D2F;">Mensaje de un tutor RUPE CDMX</h2>
            <p>Hola <strong>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
            ' . ($folio !== '' ? '<p>Referencia de folio: <strong>#' . htmlspecialchars($folio, ENT_QUOTES, 'UTF-8') . '</strong></p>' : '') . '
            <div style="background:#f8f9fa;border-left:4px solid #6B1D2F;padding:12px 16px;margin:16px 0;white-space:pre-wrap;">'
            . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) .
            '</div>
            <p style="color:#666;font-size:13px;">Enviado por: ' . htmlspecialchars($tutorNombre, ENT_QUOTES, 'UTF-8') . '<br>
            Plataforma: buscandoahuellitasrupe.com.mx</p>
          </div>';

        $ok = false;
        try {
            $ok = Mailer::send($correo, $asunto, $html, $tutorNombre);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'mensajeCiudadano', ['msg' => $e->getMessage()]);
            }
            $ok = false;
        }

        if (!$ok) {
            // Fallback: registrar en auditoría aunque el correo falle en entorno local
            try {
                $this->audit->log($uid, 'mensaje_ciudadano_fail', 'seguimientos', null, [
                    'correo' => $correo,
                    'folio' => $folio,
                ]);
            } catch (\Throwable $e) {
            }
            Response::error('No se pudo enviar el correo. Verifica la configuración de correo del servidor.', 500);
        }

        try {
            $this->audit->log($uid, 'mensaje_ciudadano', 'seguimientos', null, [
                'correo' => $correo,
                'folio' => $folio,
                'asunto' => $asunto,
            ]);
        } catch (\Throwable $e) {
        }

        Response::ok([
            'correo' => $correo,
            'folio' => $folio,
        ], 'Mensaje enviado al ciudadano correctamente.');
    }

}
