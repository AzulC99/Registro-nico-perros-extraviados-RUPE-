<?php
declare(strict_types=1);

namespace App\Models;

final class SeguimientoDAO extends BaseDAO
{
    protected string $table = 'seguimientos';
    protected string $primaryKey = 'id_seguimiento';

    /**
     * Avistamientos de una mascota. id_seguimiento siempre int > 0.
     */
    public function listByMascota(int $idMascota): array
    {
        $rows = $this->query(
            'SELECT s.id_seguimiento,
                    s.id_mascota,
                    s.id_usuario,
                    s.folio,
                    s.condicion,
                    s.foto_url,
                    s.referencia,
                    s.contacto,
                    s.telefono,
                    s.correo,
                    s.latitud,
                    s.longitud,
                    s.created_at,
                    m.folio AS mascota_folio,
                    m.nombre AS mascota,
                    u.nombre AS usuario_nombre
             FROM seguimientos s
             LEFT JOIN mascotas m ON m.id_mascota = s.id_mascota
             LEFT JOIN usuarios u ON u.id_usuario = s.id_usuario
             WHERE s.id_mascota = :id_mascota
             ORDER BY s.id_seguimiento DESC',
            ['id_mascota' => $idMascota]
        )->fetchAll();

        $out = [];
        foreach ($rows ?: [] as $r) {
            if (!is_array($r)) {
                continue;
            }
            $mapped = $this->mapRow($r);
            if ($mapped['id_seguimiento'] > 0) {
                $out[] = $mapped;
            }
        }
        return $out;
    }

    public function listBitacoraByTutor(int $idTutor): array
    {
        $sql = 'SELECT
                    m.id_mascota,
                    COALESCE(m.folio, \'\') AS folio,
                    COALESCE(m.nombre, \'\') AS mascota,
                    COALESCE(m.estatus, \'En Búsqueda\') AS estatus,
                    COALESCE(m.notas, \'\') AS notas,
                    COALESCE(s.condicion, m.notas, \'Registro en RUPE CDMX\') AS bitacora,
                    COALESCE(s.created_at, m.created_at, \'\') AS ultima_actividad,
                    COALESCE(s.condicion, \'Observado\') AS ultimo_tipo
                FROM mascotas m
                LEFT JOIN (
                    SELECT s1.id_mascota, s1.condicion, s1.created_at, s1.referencia
                    FROM seguimientos s1
                    INNER JOIN (
                        SELECT id_mascota, MAX(id_seguimiento) AS max_id
                        FROM seguimientos
                        GROUP BY id_mascota
                    ) ult ON ult.max_id = s1.id_seguimiento
                ) s ON s.id_mascota = m.id_mascota
                WHERE m.id_tutor = :id_tutor AND m.activo = 1
                ORDER BY m.created_at DESC';

        $rows = $this->query($sql, ['id_tutor' => $idTutor])->fetchAll();
        $out = [];
        foreach ($rows ?: [] as $r) {
            if (!is_array($r)) {
                continue;
            }
            $idM = (int) ($r['id_mascota'] ?? 0);
            if ($idM <= 0) {
                continue;
            }
            $bit = trim((string) ($r['bitacora'] ?? ''));
            if ($bit === '' || strcasecmp($bit, 'null') === 0) {
                $bit = 'Registro en RUPE CDMX';
            }
            $out[] = [
                'id_mascota'       => $idM,
                'folio'            => $this->str($r['folio'] ?? ''),
                'mascota'          => $this->str($r['mascota'] ?? ''),
                'estatus'          => $this->str(($r['estatus'] ?? '') !== '' ? $r['estatus'] : 'En Búsqueda'),
                'notas'            => $this->str($r['notas'] ?? ''),
                'bitacora'         => $bit,
                'ultima_actividad' => $this->str($r['ultima_actividad'] ?? ''),
                'ultimo_tipo'      => $this->str($r['ultimo_tipo'] ?? 'Observado'),
            ];
        }
        return $out;
    }

    public function listHistorialByTutor(int $idTutor): array
    {
        $sql = 'SELECT
                    s.id_seguimiento,
                    s.id_mascota,
                    s.id_usuario,
                    s.folio,
                    s.condicion,
                    s.foto_url,
                    s.referencia,
                    s.contacto,
                    s.telefono,
                    s.correo,
                    s.latitud,
                    s.longitud,
                    s.created_at,
                    m.folio AS mascota_folio,
                    m.nombre AS mascota,
                    m.estatus AS estatus,
                    u.nombre AS usuario_nombre
                FROM seguimientos s
                INNER JOIN mascotas m ON m.id_mascota = s.id_mascota
                LEFT JOIN usuarios u ON u.id_usuario = s.id_usuario
                WHERE m.id_tutor = :id_tutor AND m.activo = 1
                ORDER BY s.id_seguimiento DESC
                LIMIT 300';

        $rows = $this->query($sql, ['id_tutor' => $idTutor])->fetchAll();
        $out = [];
        foreach ($rows ?: [] as $r) {
            if (!is_array($r)) {
                continue;
            }
            $mapped = $this->mapRow($r);
            if ($mapped['id_seguimiento'] <= 0) {
                continue;
            }
            $mapped['estatus'] = $this->str(($r['estatus'] ?? '') !== '' ? $r['estatus'] : 'En Búsqueda');
            $mapped['bitacora'] = trim($mapped['condicion'] . ($mapped['referencia'] !== '' ? ' · ' . $mapped['referencia'] : ''), ' ·');
            $mapped['descripcion'] = $mapped['bitacora'];
            $mapped['tipo'] = 'avistamiento';
            $out[] = $mapped;
        }
        return $out;
    }

    public function create(array $data): int
    {
        $idMascota = (int) ($data['id_mascota'] ?? 0);
        if ($idMascota <= 0) {
            throw new \InvalidArgumentException('id_mascota inválido');
        }

        $folio = $this->str($data['folio'] ?? '');
        $condicion = $this->str($data['condicion'] ?? '');
        if ($condicion === '') {
            $condicion = 'Observado';
        }
        $referencia = $this->str($data['referencia'] ?? '');
        $contacto = $this->str($data['contacto'] ?? $data['reportado_por'] ?? $data['reportero'] ?? '');
        $telefono = preg_replace('/\D+/', '', (string) ($data['telefono'] ?? '')) ?: '';
        $correo = $this->str($data['correo'] ?? '');

        // Compat descripcion antigua
        $descLegacy = $this->str($data['descripcion'] ?? '');
        if ($descLegacy !== '') {
            if ($condicion === 'Observado' && preg_match('/Avistamiento\s*:\s*([^·|]+)/iu', $descLegacy, $m)) {
                $condicion = trim($m[1]) ?: $condicion;
            }
            if ($referencia === '' && preg_match('/Ref(?:erencia)?\s*:\s*([^·|]+)/iu', $descLegacy, $m)) {
                $referencia = trim($m[1]);
            }
            if ($contacto === '' && preg_match('/Report[oó]\s*:\s*([^·|]+)/iu', $descLegacy, $m)) {
                $contacto = trim($m[1]);
            }
            if ($telefono === '' && preg_match('/Tel\s*:\s*([^·|]+)/iu', $descLegacy, $m)) {
                $telefono = preg_replace('/\D+/', '', $m[1]) ?: '';
            }
            if ($correo === '' && preg_match('/Email\s*:\s*([^·|]+)/iu', $descLegacy, $m)) {
                $correo = trim($m[1]);
            }
        }

        $lat = null;
        $lng = null;
        if (isset($data['latitud']) && $data['latitud'] !== '' && $data['latitud'] !== null && is_numeric($data['latitud'])) {
            $lat = round((float) $data['latitud'], 7);
        }
        if (isset($data['longitud']) && $data['longitud'] !== '' && $data['longitud'] !== null && is_numeric($data['longitud'])) {
            $lng = round((float) $data['longitud'], 7);
        }

        $foto = $this->str($data['foto_url'] ?? '');
        if ($foto === '') {
            $foto = null;
        } else {
            $foto = mb_substr($foto, 0, 255);
        }

        $idUsuario = (int) ($data['id_usuario'] ?? 0);

        // Evitar duplicado reciente: mismo mascota + GPS (~15 min)
        if ($lat !== null && $lng !== null) {
            try {
                $dup = $this->query(
                    'SELECT id_seguimiento FROM seguimientos
                     WHERE id_mascota = :m
                       AND ABS(COALESCE(latitud, 0) - :lat) < 0.0002
                       AND ABS(COALESCE(longitud, 0) - :lng) < 0.0002
                       AND created_at >= (NOW() - INTERVAL 15 MINUTE)
                     ORDER BY id_seguimiento DESC LIMIT 1',
                    [
                        'm' => $idMascota,
                        'lat' => $lat,
                        'lng' => $lng,
                    ]
                )->fetch();
                if ($dup && (int) ($dup['id_seguimiento'] ?? 0) > 0) {
                    return (int) $dup['id_seguimiento'];
                }
            } catch (\Throwable $e) {
                // continuar con insert
            }
        }

        $sql = 'INSERT INTO seguimientos
                    (id_mascota, id_usuario, folio, condicion, foto_url, referencia, contacto, telefono, correo, latitud, longitud, created_at)
                VALUES
                    (:id_mascota, :id_usuario, :folio, :condicion, :foto_url, :referencia, :contacto, :telefono, :correo, :latitud, :longitud, NOW())';
        $this->query($sql, [
            'id_mascota' => $idMascota,
            'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
            'folio'      => $folio !== '' ? mb_substr($folio, 0, 40) : null,
            'condicion'  => mb_substr($condicion, 0, 80),
            'foto_url'   => $foto,
            'referencia' => $referencia !== '' ? mb_substr($referencia, 0, 120) : null,
            'contacto'   => $contacto !== '' ? mb_substr($contacto, 0, 80) : null,
            'telefono'   => $telefono !== '' ? mb_substr($telefono, 0, 20) : null,
            'correo'     => $correo !== '' ? mb_substr($correo, 0, 100) : null,
            'latitud'    => $lat,
            'longitud'   => $lng,
        ]);

        $id = (int) $this->db->lastInsertId();
        if ($id <= 0) {
            $row = $this->query(
                'SELECT id_seguimiento FROM seguimientos WHERE id_mascota = :m ORDER BY id_seguimiento DESC LIMIT 1',
                ['m' => $idMascota]
            )->fetch();
            $id = (int) ($row['id_seguimiento'] ?? 0);
        }
        if ($id <= 0) {
            throw new \RuntimeException('INSERT seguimientos no devolvió id_seguimiento numérico');
        }
        return $id;
    }

    /** @param array<string,mixed> $r */
    private function mapRow(array $r): array
    {
        $id = (int) ($r['id_seguimiento'] ?? $r['id'] ?? 0);
        $idMascota = (int) ($r['id_mascota'] ?? 0);
        $idUsuario = (int) ($r['id_usuario'] ?? 0);

        $foto = $this->str($r['foto_url'] ?? '');
        $contacto = $this->str($r['contacto'] ?? '');
        if ($contacto === '') {
            $contacto = $this->str($r['usuario_nombre'] ?? '');
        }
        if ($contacto === '') {
            $contacto = 'Ciudadano';
        }

        $folio = $this->str($r['folio'] ?? '');
        if ($folio === '') {
            $folio = $this->str($r['mascota_folio'] ?? '');
        }

        $cond = $this->str($r['condicion'] ?? '');
        if ($cond === '') {
            $cond = 'Observado';
        }

        $lat = $r['latitud'] ?? null;
        $lng = $r['longitud'] ?? null;
        $latStr = ($lat !== null && $lat !== '' && is_numeric($lat)) ? (string) $lat : '';
        $lngStr = ($lng !== null && $lng !== '' && is_numeric($lng)) ? (string) $lng : '';

        return [
            'id'             => $id,
            'id_seguimiento' => $id,
            'id_mascota'     => $idMascota,
            'id_usuario'     => $idUsuario,
            'folio'          => $folio,
            'condicion'      => $cond,
            'foto_url'       => $foto,
            'foto'           => $foto,
            'referencia'     => $this->str($r['referencia'] ?? ''),
            'contacto'       => $contacto,
            'reportado_por'  => $contacto,
            'reportadoPor'   => $contacto,
            'telefono'       => $this->str($r['telefono'] ?? ''),
            'correo'         => $this->str($r['correo'] ?? ''),
            'latitud'        => $latStr,
            'longitud'       => $lngStr,
            'lat'            => $latStr,
            'lng'            => $lngStr,
            'created_at'     => $this->str($r['created_at'] ?? ''),
            'fechaHora'      => $this->str($r['created_at'] ?? ''),
            'mascota'        => $this->str($r['mascota'] ?? ''),
            'tipo'           => 'avistamiento',
            'descripcion'    => trim($cond . ' · ' . $this->str($r['referencia'] ?? ''), ' ·'),
        ];
    }

    private function str(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        $s = trim((string) $v);
        if ($s === '' || strcasecmp($s, 'null') === 0) {
            return '';
        }
        return $s;
    }
}
