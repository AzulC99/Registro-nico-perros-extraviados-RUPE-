<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Validator;
use App\Helpers\Recaptcha;
use App\Helpers\Mailer;
use App\Models\UsuarioDAO;
use App\Models\PasswordResetDAO;
use App\Models\PreferenciaDAO;
use App\Models\AuditoriaDAO;
use App\Core\Exceptions\AuthException;
use App\Core\Exceptions\ValidationException;

final class AuthController
{
    private UsuarioDAO $usuarios;
    private PasswordResetDAO $resets;
    private PreferenciaDAO $prefs;
    private AuditoriaDAO $audit;

    public function __construct()
    {
        $this->usuarios = new UsuarioDAO();
        $this->resets   = new PasswordResetDAO();
        $this->prefs    = new PreferenciaDAO();
        $this->audit    = new AuditoriaDAO();
    }

    public function login(Request $request): void
    {
        if (!rate_limit_check('login', 15, 60)) {
            Response::error('Demasiados intentos. Espera un momento.', 429);
        }
        $v = Validator::make($request->all())->required('correo', 'password')->email('correo');
        $data = $v->validate();
        $data['password'] = trim((string) ($data['password'] ?? ''));
        $data['correo'] = strtolower(trim((string) ($data['correo'] ?? '')));
        Recaptcha::assertValid($request->input('recaptcha_token'), 'login');
        $user = $this->usuarios->findByEmail($data['correo']);
        if (!$user) {
            throw new AuthException('Credenciales incorrectas');
        }

        // activo: 0 / "0" / false / null → inactiva
        $activoRaw = $user['activo'] ?? 0;
        $isActivo = ($activoRaw === 1 || $activoRaw === '1' || $activoRaw === true || $activoRaw === 'true');

        if (!$isActivo) {
            try {
                $this->audit->log((int) $user['id_usuario'], 'login_cuenta_inactiva');
            } catch (\Throwable $e) {
            }
            // Respuesta explícita (código en raíz para el front)
            json_response([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. No puedes iniciar sesión. Contacta al administrador de RUPE CDMX.',
                'code'    => 'cuenta_inactiva',
                'errors'  => ['code' => 'cuenta_inactiva', 'activo' => 0],
            ], 403);
        }

        $hash = (string) ($user['password_hash'] ?? '');
        if ($hash === '' || !verify_password(trim((string) $data['password']), $hash)) {
            try {
                $this->audit->log((int) $user['id_usuario'], 'login_fallido');
            } catch (\Throwable $e) {
            }
            throw new AuthException('Credenciales incorrectas');
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = (int) $user['id_usuario'];
        $_SESSION['user_role']  = $user['rol'];
        $_SESSION['user_name']  = $user['nombre'];
        $_SESSION['user_email'] = $user['correo'];
        $this->usuarios->touchLastAccess((int) $user['id_usuario']);
        try {
            $this->audit->log((int) $user['id_usuario'], 'login_ok');
        } catch (\Throwable $e) {
        }

        Response::ok([
            'user'   => $this->usuarios->toPublic($user),
            'csrf'   => csrf_token(),
            'activo' => 1,
            'estado' => 'Activo',
        ], 'Sesión iniciada. Tu cuenta está activa.');
    }

    public function register(Request $request): void
    {
        if (!rate_limit_check('register', 8, 60)) {
            Response::error('Demasiados intentos.', 429);
        }
        $v = Validator::make($request->all())
            ->required('nombre', 'correo', 'telefono', 'password', 'password_confirmation')
            ->email('correo')->min('nombre', 3)->max('nombre', 120)
            ->password('password', 8)->confirmed('password')->phone('telefono');
        $data = $v->validate();
        $data['password'] = trim((string) ($data['password'] ?? ''));
        $data['correo'] = strtolower(trim((string) ($data['correo'] ?? '')));
        Recaptcha::assertValid($request->input('recaptcha_token'), 'register');
        if ($this->usuarios->findByEmail($data['correo'])) {
            throw new ValidationException('El correo ya está registrado', ['correo' => ['Ya existe una cuenta con este correo.']]);
        }
        $id = $this->usuarios->create([
            'nombre' => $data['nombre'],
            'correo' => $data['correo'],
            'telefono' => normalize_phone($data['telefono'] ?? ''),
            'password_hash' => hash_password($data['password']),
            'rol' => ROLE_TUTOR,
            'acepta_privacidad' => !empty($data['acepta_privacidad']) ? 1 : 0,
        ]);
        $this->prefs->upsert($id, []);
        $this->audit->log($id, 'registro_tutor');
        Mailer::sendWelcome($data['correo'], $data['nombre']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['user_role'] = ROLE_TUTOR;
        $_SESSION['user_name'] = $data['nombre'];
        $_SESSION['user_email'] = strtolower(trim($data['correo']));
        $user = $this->usuarios->find($id) ?: [];
        Response::ok(['user' => sanitize_data($this->usuarios->toPublic($user), true), 'csrf' => csrf_token()], 'Cuenta creada', 201);
    }

    public function logout(Request $request): void
    {
        $uid = $_SESSION['user_id'] ?? null;
        if ($uid) { $this->audit->log((int) $uid, 'logout'); }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
        }
        session_destroy();
        Response::ok([], 'Sesión cerrada');
    }

    public function me(Request $request): void
    {
        if (empty($_SESSION['user_id'])) { throw new AuthException('No autenticado'); }
        $user = $this->usuarios->find((int) $_SESSION['user_id']);
        if (!$user || !(int) $user['activo']) { throw new AuthException('Sesión inválida'); }
        Response::ok(['user' => $this->usuarios->toPublic($user), 'csrf' => csrf_token()]);
    }

    public function forgot(Request $request): void
    {
        if (!rate_limit_check('forgot', 8, 60)) {
            Response::error('Demasiados intentos. Espera un momento.', 429);
        }
        $v = Validator::make($request->all())->required('correo')->email('correo');
        $data = $v->validate();
        $data['correo'] = strtolower(trim((string) ($data['correo'] ?? '')));

        try {
            Recaptcha::assertValid($request->input('recaptcha_token'), 'forgot');
        } catch (\Throwable $e) {
            // No bloquear recuperación si reCAPTCHA falla en hosting
            if (function_exists('app_log')) {
                app_log('warning', 'forgot recaptcha', ['msg' => $e->getMessage()]);
            }
        }

        $user = $this->usuarios->findByEmail($data['correo']);
        $payload = [
            'expires_in' => 604800,
            'reset_url'  => '',
            'token'      => '',
        ];

        if ($user) {
            $activoRaw = $user['activo'] ?? 1;
            $isActivo = ($activoRaw === 1 || $activoRaw === '1' || $activoRaw === true || $activoRaw === 'true');
            if (!$isActivo) {
                Response::error('Tu cuenta está inactiva. Contacta al administrador.', 403, ['code' => 'cuenta_inactiva']);
            }

            $token = bin2hex(random_bytes(32));
            $idReset = 0;
            try {
                $idReset = $this->resets->createToken((int) $user['id_usuario'], $token, 604800);
            } catch (\Throwable $e) {
                if (function_exists('app_log')) {
                    app_log('error', 'forgot createToken', ['msg' => $e->getMessage()]);
                }
            }

            if ($idReset <= 0) {
                // Verificar si aun así quedó un token válido
                $check = $this->resets->findValid($token);
                if (!$check) {
                    Response::error('No se pudo generar el enlace. Intenta de nuevo en un momento.', 500);
                }
            }

            // Sesión de respaldo para abrir el enlace seguido
            if (session_status() === PHP_SESSION_ACTIVE || @session_start()) {
                $_SESSION['rupe_reset_token'] = $token;
                $_SESSION['rupe_reset_token_at'] = time();
            }

            $payload['token'] = $token;
            $payload['reset_url'] = '/auth/reset?token=' . rawurlencode($token);

            try {
                Mailer::sendPasswordReset((string) $user['correo'], (string) ($user['nombre'] ?? ''), $token);
            } catch (\Throwable $e) {
                if (function_exists('app_log')) {
                    app_log('error', 'forgot mail', ['msg' => $e->getMessage()]);
                }
            }
            try {
                $this->audit->log((int) $user['id_usuario'], 'password_reset_solicitado');
            } catch (\Throwable $e) {
            }

            Response::ok(
                $payload,
                'Enlace listo. Ábrelo ahora o revisa tu correo.'
            );
        }

        // Mismo mensaje genérico si el correo no existe (no enumerar usuarios)
        Response::ok(
            $payload,
            'Si el correo está registrado, el enlace estará disponible. Revisa bandeja y spam.'
        );
    }

    public function reset(Request $request): void
    {
        $v = Validator::make($request->all())
            ->required('token', 'password', 'password_confirmation')
            ->password('password', 8)->confirmed('password');
        $data = $v->validate();
        $data['password'] = trim((string) ($data['password'] ?? ''));
        $data['token'] = trim((string) ($data['token'] ?? ''));
        if ($data['token'] === '' || strlen($data['token']) < 32) {
            throw new ValidationException('Token inválido o expirado', ['token' => ['Enlace no válido o incompleto.']]);
        }
        Recaptcha::assertValid($request->input('recaptcha_token'), 'reset');
        $row = $this->resets->findValid($data['token']);
        if (!$row) {
            throw new ValidationException('Token inválido o expirado', ['token' => ['Este enlace ya expiró o fue utilizado. Solicita uno nuevo desde Recuperar contraseña.']]);
        }
        $uid = (int) ($row['id_usuario'] ?? 0);
        if ($uid <= 0) {
            throw new ValidationException('Token inválido o expirado', ['token' => ['Enlace no válido.']]);
        }
        $this->usuarios->update($uid, ['password_hash' => hash_password($data['password'])]);
        $this->resets->markUsed((int) $row['id_reset']);
        try {
            $this->audit->log($uid, 'password_reset_ok');
        } catch (\Throwable $e) {
        }
        Response::ok([], 'Contraseña actualizada. Ya puedes iniciar sesión.');
    }


    /** GET /auth/logout — cierre de sesión web y redirección */
    public function logoutWeb(Request $request): void
    {
        $uid = $_SESSION['user_id'] ?? null;
        if ($uid) {
            $this->audit->log((int) $uid, 'logout');
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool) ($p['secure'] ?? false), (bool) ($p['httponly'] ?? true));
        }
        session_destroy();
        redirect('/');
    }

    public function showForgot(Request $request): void
    {
        Response::view('auth/forgot', ['csrf' => csrf_token()]);
    }

    public function showReset(Request $request): void
    {
        $token = '';
        try {
            $token = trim((string) ($request->query('token') ?? ''));
            if ($token === '' && isset($_GET['token'])) {
                $token = trim((string) $_GET['token']);
            }
            if ($token === '') {
                $token = trim((string) ($request->input('token') ?? ''));
            }
        } catch (\Throwable $e) {
            $token = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
        }

        // Extraer de URL completa pegada
        if ($token !== '' && (str_contains($token, 'token=') || str_contains($token, 'http'))) {
            if (preg_match('/[?&]token=([a-fA-F0-9]{32,128})/', $token, $mm)) {
                $token = $mm[1];
            } elseif (preg_match('/([a-fA-F0-9]{64})/', $token, $mm)) {
                $token = $mm[1];
            }
        }
        $token = preg_replace('/\s+/', '', $token) ?? '';

        // Respaldo: token recién generado en la misma sesión
        if ($token === '' || strlen($token) < 32) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
            $sessTok = (string) ($_SESSION['rupe_reset_token'] ?? '');
            if (strlen($sessTok) >= 32) {
                $token = $sessTok;
            }
        }

        $tokenOk = false;
        $tokenMsg = 'Solicita un enlace nuevo desde Recuperar contraseña.';
        $correoMask = '';
        $nombre = '';

        if (strlen($token) >= 32) {
            try {
                $row = $this->resets->findValid($token);
                // Fallback sesión: mismo token generado hace poco
                if (!$row) {
                    if (session_status() !== PHP_SESSION_ACTIVE) {
                        @session_start();
                    }
                    $sessTok = (string) ($_SESSION['rupe_reset_token'] ?? '');
                    $sessAt = (int) ($_SESSION['rupe_reset_token_at'] ?? 0);
                    if ($sessTok !== '' && hash_equals($sessTok, $token) && $sessAt > 0 && (time() - $sessAt) < 604800) {
                        $uid = (int) ($_SESSION['user_id'] ?? 0);
                        // Buscar usuario por correo en sesión o dejar form usable
                        $row = [
                            'id_reset' => 0,
                            'id_usuario' => $uid,
                            'correo' => (string) ($_SESSION['user_email'] ?? ''),
                            'nombre' => (string) ($_SESSION['user_name'] ?? ''),
                        ];
                        // Intentar localizar usuario desde el token en BD aunque used_at esté set por error
                        $any = $this->resets->findAny($token);
                        if (is_array($any)) {
                            $row = $any;
                        }
                    }
                }
                if (is_array($row) && $row !== []) {
                    // Si solo estaba "used" pero es el token de sesión reciente, permitir de todos modos
                    $status = (string) ($row['token_status'] ?? '');
                    $allow = ($status === '' || $status === 'valid');
                    if (!$allow) {
                        if (session_status() !== PHP_SESSION_ACTIVE) {
                            @session_start();
                        }
                        $sessTok = (string) ($_SESSION['rupe_reset_token'] ?? '');
                        if ($sessTok !== '' && hash_equals($sessTok, $token)) {
                            $allow = true;
                        }
                    }
                    // findValid no trae token_status → permitir
                    if (!isset($row['token_status']) || $allow) {
                        $tokenOk = true;
                        $tokenMsg = '';
                        $correo = (string) ($row['correo'] ?? '');
                        $nombre = (string) ($row['nombre'] ?? '');
                        if ($correo !== '' && str_contains($correo, '@')) {
                            [$u, $d] = explode('@', $correo, 2);
                            $uMask = mb_substr($u, 0, 1) . str_repeat('*', max(1, mb_strlen($u) - 1));
                            $correoMask = $uMask . '@' . $d;
                        }
                    } else {
                        $tokenMsg = 'Este enlace ya fue utilizado. Solicita uno nuevo.';
                    }
                } else {
                    $any = null;
                    try {
                        $any = $this->resets->findAny($token);
                    } catch (\Throwable $e2) {
                        $any = null;
                    }
                    if (is_array($any) && ($any['token_status'] ?? '') === 'used') {
                        $tokenMsg = 'Este enlace ya fue utilizado. Solicita uno nuevo.';
                    } else {
                        $tokenMsg = 'Token no válido. Solicita un enlace nuevo y ábrelo con el botón de esta pantalla.';
                    }
                }
            } catch (\Throwable $e) {
                if (function_exists('app_log')) {
                    app_log('error', 'showReset', ['msg' => $e->getMessage()]);
                }
                $tokenMsg = 'No se pudo validar el enlace. Solicita uno nuevo.';
            }
        }

        try {
            $csrf = function_exists('csrf_token') ? csrf_token() : '';
        } catch (\Throwable $e) {
            $csrf = '';
        }

        Response::view('auth/reset', [
            'csrf'        => $csrf,
            'token'       => $token,
            'token_ok'    => $tokenOk,
            'token_msg'   => $tokenMsg,
            'correo_mask' => $correoMask,
            'nombre'      => $nombre,
        ]);
    }

}
