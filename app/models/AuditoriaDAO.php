<?php
declare(strict_types=1);

namespace App\Models;

final class AuditoriaDAO extends BaseDAO
{
    protected string $table = 'auditoria';
    protected string $primaryKey = 'id_auditoria';

    /** Mapa acción → módulo legible */
    public static function moduloDesdeAccion(string $accion): string
    {
        $a = strtolower(trim($accion));
        $map = [
            'login_ok' => 'Autenticación',
            'login_fallido' => 'Autenticación',
            'login_cuenta_inactiva' => 'Autenticación',
            'logout' => 'Autenticación',
            'registro_tutor' => 'Registro',
            'password_reset_solicitado' => 'Seguridad',
            'password_reset_ok' => 'Seguridad',
            'password_change' => 'Seguridad',
            'perfil_actualizar' => 'Perfil tutor',
            'mascota_crear' => 'Mascotas',
            'mascota_actualizar' => 'Mascotas',
            'mascota_eliminar' => 'Mascotas',
            'seguimiento_crear' => 'Seguimiento',
            'ayuda_contacto' => 'Ayuda',
            'usuario_crear_admin' => 'Usuarios',
            'usuario_actualizar' => 'Usuarios',
            'alcaldia_crear' => 'Alcaldías',
            'alcaldia_actualizar' => 'Alcaldías',
            'config_actualizar' => 'Configuración',
            'raza_crear' => 'Razas',
            'raza_actualizar' => 'Razas',
            'validar_reporte' => 'Reportes',
        ];
        if (isset($map[$a])) {
            return $map[$a];
        }
        if (str_contains($a, 'login') || str_contains($a, 'logout') || str_contains($a, 'auth')) {
            return 'Autenticación';
        }
        if (str_contains($a, 'mascota') || str_contains($a, 'folio')) {
            return 'Mascotas';
        }
        if (str_contains($a, 'usuario')) {
            return 'Usuarios';
        }
        if (str_contains($a, 'raza')) {
            return 'Razas';
        }
        if (str_contains($a, 'alcaldia')) {
            return 'Alcaldías';
        }
        return 'Sistema';
    }

    public static function detalleDesdeAccion(string $accion, ?string $tabla, $detalleRaw, ?int $idRegistro = null): string
    {
        // Si ya hay detalle JSON o texto
        if (is_array($detalleRaw)) {
            $parts = [];
            foreach ($detalleRaw as $k => $v) {
                if (is_scalar($v) || $v === null) {
                    $parts[] = $k . ': ' . (string) $v;
                }
            }
            if ($parts !== []) {
                return implode(' · ', $parts);
            }
        }
        if (is_string($detalleRaw) && trim($detalleRaw) !== '' && $detalleRaw !== 'null') {
            $decoded = json_decode($detalleRaw, true);
            if (is_array($decoded)) {
                $parts = [];
                foreach ($decoded as $k => $v) {
                    if (is_scalar($v) || $v === null) {
                        $parts[] = $k . ': ' . (string) $v;
                    }
                }
                if ($parts !== []) {
                    return implode(' · ', $parts);
                }
            }
            return $detalleRaw;
        }

        $a = strtolower(trim($accion));
        $labels = [
            'login_ok' => 'Inicio de sesión exitoso',
            'login_fallido' => 'Intento de acceso con credenciales incorrectas',
            'login_cuenta_inactiva' => 'Intento de acceso con cuenta inactiva',
            'logout' => 'Cierre de sesión',
            'registro_tutor' => 'Alta de cuenta tutor',
            'password_reset_solicitado' => 'Solicitud de restablecimiento de contraseña',
            'password_reset_ok' => 'Contraseña restablecida correctamente',
            'password_change' => 'Cambio de contraseña desde el panel',
            'perfil_actualizar' => 'Actualización de datos de perfil',
            'mascota_crear' => 'Registro de mascota extraviada',
            'mascota_actualizar' => 'Actualización de ficha de mascota',
            'mascota_eliminar' => 'Eliminación de registro de mascota',
            'seguimiento_crear' => 'Nuevo avistamiento / seguimiento GPS',
            'ayuda_contacto' => 'Mensaje de ayuda enviado',
            'usuario_crear_admin' => 'Alta de usuario administrador',
            'usuario_actualizar' => 'Actualización de usuario',
            'alcaldia_crear' => 'Alta de alcaldía en catálogo',
            'alcaldia_actualizar' => 'Actualización de alcaldía',
            'config_actualizar' => 'Cambio de configuración del sistema',
        ];
        $txt = $labels[$a] ?? ('Evento: ' . $accion);
        if ($tabla) {
            $txt .= ' · tabla: ' . $tabla;
        }
        if ($idRegistro && $idRegistro > 0) {
            $txt .= ' · id: ' . $idRegistro;
        }
        return $txt;
    }

    public function log(?int $idUsuario, string $accion, ?string $tabla = null, ?int $idRegistro = null, ?array $detalle = null): void
    {
        if ($tabla === null || $tabla === '') {
            $mod = self::moduloDesdeAccion($accion);
            // Guardar módulo legible en columna tabla cuando no hay tabla BD
            $tabla = match (true) {
                str_contains(strtolower($accion), 'login'), str_contains(strtolower($accion), 'logout') => 'sesiones',
                str_contains(strtolower($accion), 'password') => 'usuarios',
                str_contains(strtolower($accion), 'mascota') => 'mascotas',
                str_contains(strtolower($accion), 'seguimiento') => 'seguimientos',
                str_contains(strtolower($accion), 'usuario') => 'usuarios',
                str_contains(strtolower($accion), 'alcaldia') => 'alcaldias',
                str_contains(strtolower($accion), 'raza') => 'razas',
                str_contains(strtolower($accion), 'config') => 'configuraciones',
                default => strtolower(str_replace(' ', '_', $mod)),
            };
        }
        if ($detalle === null) {
            $detalle = [
                'evento' => self::detalleDesdeAccion($accion, $tabla, null, $idRegistro),
                'modulo' => self::moduloDesdeAccion($accion),
            ];
        } elseif (!isset($detalle['modulo'])) {
            $detalle['modulo'] = self::moduloDesdeAccion($accion);
        }

        $this->insert([
            'id_usuario'  => $idUsuario,
            'accion'      => $accion,
            'tabla'       => $tabla,
            'id_registro' => $idRegistro,
            'detalle'     => json_encode($detalle, JSON_UNESCAPED_UNICODE),
            'ip'          => function_exists('client_ip') ? client_ip() : null,
            'user_agent'  => function_exists('client_ua') ? client_ua() : null,
        ]);
    }

    public function listRecent(int $limit = 100, int $offset = 0): array
    {
        $limit = max(1, min(500, (int) $limit));
        $offset = max(0, (int) $offset);
        try {
            $rows = $this->query(
                'SELECT a.id_auditoria, a.id_usuario, a.accion, a.tabla, a.id_registro,
                        a.detalle, a.ip, a.user_agent, a.created_at,
                        COALESCE(u.nombre, \'\') AS usuario_nombre,
                        COALESCE(u.correo, \'\') AS usuario_correo,
                        COALESCE(u.rol, \'\') AS usuario_rol
                 FROM auditoria a
                 LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
                 ORDER BY a.created_at DESC, a.id_auditoria DESC
                 LIMIT ' . $limit . ' OFFSET ' . $offset
            )->fetchAll() ?: [];
        } catch (\Throwable $e) {
            try {
                $rows = $this->query(
                    'SELECT a.*, COALESCE(u.nombre, \'\') AS usuario_nombre, COALESCE(u.correo, \'\') AS usuario_correo
                     FROM auditoria a
                     LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
                     ORDER BY a.id_auditoria DESC
                     LIMIT ' . $limit . ' OFFSET ' . $offset
                )->fetchAll() ?: [];
            } catch (\Throwable $e2) {
                return [];
            }
        }

        // Enriquecer filas vacías (registros antiguos sin tabla/detalle)
        foreach ($rows as &$a) {
            $accion = (string) ($a['accion'] ?? '');
            $tabla = trim((string) ($a['tabla'] ?? ''));
            if ($tabla === '' || $tabla === '—') {
                $a['tabla'] = self::moduloDesdeAccion($accion);
            }
            $a['modulo'] = self::moduloDesdeAccion($accion);
            $a['detalle_texto'] = self::detalleDesdeAccion(
                $accion,
                $tabla !== '' ? $tabla : null,
                $a['detalle'] ?? null,
                isset($a['id_registro']) ? (int) $a['id_registro'] : null
            );
        }
        unset($a);

        return $rows;
    }
}
