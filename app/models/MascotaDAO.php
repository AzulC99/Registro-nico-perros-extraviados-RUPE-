<?php
declare(strict_types=1);

namespace App\Models;

final class MascotaDAO extends BaseDAO
{
    protected string $table = 'mascotas';
    protected string $primaryKey = 'id_mascota';

    public function findByFolio(string $folio): ?array
    {
        $row = $this->query(
            'SELECT m.*, r.nombre AS raza_nombre, a.nombre AS alcaldia_nombre,
                    u.nombre AS tutor_nombre, u.correo AS tutor_correo, u.telefono AS tutor_telefono,
                    ab.nombre AS albergue_nombre
             FROM mascotas m
             LEFT JOIN razas r ON r.id_raza = m.id_raza
             LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
             LEFT JOIN usuarios u ON u.id_usuario = m.id_tutor
             LEFT JOIN albergues ab ON ab.id_albergue = m.id_albergue
             WHERE m.folio = :folio AND m.activo = 1 LIMIT 1',
            ['folio' => $folio]
        )->fetch();
        return $row ?: null;
    }

    public function findByQrToken(string $token): ?array
    {
        $row = $this->query(
            'SELECT m.*, r.nombre AS raza_nombre, a.nombre AS alcaldia_nombre,
                    u.nombre AS tutor_nombre, u.correo AS tutor_correo, u.telefono AS tutor_telefono,
                    ab.nombre AS albergue_nombre
             FROM mascotas m
             LEFT JOIN razas r ON r.id_raza = m.id_raza
             LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
             LEFT JOIN usuarios u ON u.id_usuario = m.id_tutor
             LEFT JOIN albergues ab ON ab.id_albergue = m.id_albergue
             WHERE m.qr_token = :token AND m.activo = 1 LIMIT 1',
            ['token' => $token]
        )->fetch();
        return $row ?: null;
    }


    /** SELECT foto_url por id_mascota (consulta directa) */
    public function fotoById(int $id): ?string
    {
        $row = $this->query(
            'SELECT foto_url, especie FROM mascotas WHERE id_mascota = :id AND activo = 1 LIMIT 1',
            ['id' => $id]
        )->fetch();
        if (!$row) {
            return null;
        }
        $src = function_exists('mascota_foto_src')
            ? mascota_foto_src(
                isset($row['foto_url']) ? (string) $row['foto_url'] : null,
                (string) ($row['especie'] ?? 'perro')
            )
            : (string) ($row['foto_url'] ?? '');
        return $src !== '' ? $src : null;
    }

        public function listByTutor(int $idTutor): array
    {
        $this->ensureHoraColumn();
        $sqlFull = "SELECT
                    m.id_mascota,
                    m.folio,
                    m.id_tutor,
                    COALESCE(m.nombre, '') AS nombre,
                    m.id_raza,
                    COALESCE(r.nombre, '') AS raza_nombre,
                    COALESCE(m.especie, 'perro') AS especie,
                    COALESCE(m.sexo, 'desconocido') AS sexo,
                    COALESCE(m.color, '') AS color,
                    COALESCE(m.edad_aprox, '') AS edad_aprox,
                    COALESCE(m.senas_particulares, '') AS senas_particulares,
                    COALESCE(m.collar, '') AS collar,
                    COALESCE(m.microchip, '') AS microchip,
                    COALESCE(m.tamano, '') AS tamano,
                    COALESCE(m.foto_url, '') AS foto_url,
                    m.id_alcaldia,
                    COALESCE(a.nombre, '') AS alcaldia_nombre,
                    COALESCE(m.colonia, '') AS colonia,
                    COALESCE(m.direccion_ref, '') AS direccion_ref,
                    COALESCE(
                        CAST(m.latitud AS CHAR),
                        (SELECT CAST(s.latitud AS CHAR) FROM seguimientos s
                         WHERE s.id_mascota = m.id_mascota AND s.latitud IS NOT NULL AND CAST(s.latitud AS CHAR) <> ''
                         ORDER BY s.created_at DESC LIMIT 1),
                        ''
                    ) AS latitud,
                    COALESCE(
                        CAST(m.longitud AS CHAR),
                        (SELECT CAST(s.longitud AS CHAR) FROM seguimientos s
                         WHERE s.id_mascota = m.id_mascota AND s.longitud IS NOT NULL AND CAST(s.longitud AS CHAR) <> ''
                         ORDER BY s.created_at DESC LIMIT 1),
                        ''
                    ) AS longitud,
                    COALESCE(CAST(m.fecha_extravio AS CHAR), '') AS fecha_extravio,
                    COALESCE(m.estatus, 'En Búsqueda') AS estatus,
                    m.id_albergue,
                    COALESCE(m.notas, '') AS notas,
                    COALESCE(m.qr_token, '') AS qr_token,
                    m.activo,
                    m.created_at
             FROM mascotas m
             LEFT JOIN razas r ON r.id_raza = m.id_raza
             LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
             WHERE m.id_tutor = :id_tutor AND m.activo = 1
             ORDER BY m.created_at DESC";

        $sqlSafe = "SELECT
                    m.id_mascota,
                    m.folio,
                    m.id_tutor,
                    COALESCE(m.nombre, '') AS nombre,
                    m.id_raza,
                    COALESCE(r.nombre, '') AS raza_nombre,
                    COALESCE(m.especie, 'perro') AS especie,
                    COALESCE(m.sexo, 'desconocido') AS sexo,
                    COALESCE(m.color, '') AS color,
                    COALESCE(m.edad_aprox, '') AS edad_aprox,
                    COALESCE(m.senas_particulares, '') AS senas_particulares,
                    COALESCE(m.foto_url, '') AS foto_url,
                    m.id_alcaldia,
                    COALESCE(a.nombre, '') AS alcaldia_nombre,
                    COALESCE(m.colonia, '') AS colonia,
                    COALESCE(m.direccion_ref, '') AS direccion_ref,
                    COALESCE(CAST(m.latitud AS CHAR), '') AS latitud,
                    COALESCE(CAST(m.longitud AS CHAR), '') AS longitud,
                    COALESCE(CAST(m.fecha_extravio AS CHAR), '') AS fecha_extravio,
                    COALESCE(m.estatus, 'En Búsqueda') AS estatus,
                    m.id_albergue,
                    COALESCE(m.notas, '') AS notas,
                    COALESCE(m.qr_token, '') AS qr_token,
                    m.activo,
                    m.created_at
             FROM mascotas m
             LEFT JOIN razas r ON r.id_raza = m.id_raza
             LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
             WHERE m.id_tutor = :id_tutor AND m.activo = 1
             ORDER BY m.created_at DESC";

        try {
            return $this->query($sqlFull, ['id_tutor' => $idTutor])->fetchAll() ?: [];
        } catch (\Throwable $e) {
            try {
                return $this->query($sqlSafe, ['id_tutor' => $idTutor])->fetchAll() ?: [];
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }

    public function findWithJoins(int $id): ?array
    {
        $this->ensureHoraColumn();
        $sql = "SELECT m.*,
                    COALESCE(r.nombre, '') AS raza_nombre,
                    COALESCE(a.nombre, '') AS alcaldia_nombre,
                    COALESCE(m.foto_url, '') AS foto_url,
                    COALESCE(m.color, '') AS color,
                    COALESCE(m.senas_particulares, '') AS senas_particulares,
                    COALESCE(m.collar, '') AS collar,
                    COALESCE(m.microchip, '') AS microchip,
                    COALESCE(m.tamano, '') AS tamano,
                    COALESCE(m.edad_aprox, '') AS edad_aprox,
                    COALESCE(m.colonia, '') AS colonia
             FROM mascotas m
             LEFT JOIN razas r ON r.id_raza = m.id_raza
             LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
             WHERE m.id_mascota = :id LIMIT 1";
        $row = $this->query($sql, ['id' => $id])->fetch();
        return $row ?: null;
    }

    public function listAll(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $this->ensureHoraColumn();
        $where = ['1=1'];
        $params = [];
        // Admin: no filtrar por activo (ver todos los reportes de tutores)

        if (!empty($filters['estatus'])) {
            $where[] = 'm.estatus = :estatus';
            $params['estatus'] = $filters['estatus'];
        }
        if (!empty($filters['id_alcaldia'])) {
            $where[] = 'm.id_alcaldia = :id_alcaldia';
            $params['id_alcaldia'] = (int) $filters['id_alcaldia'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(m.folio LIKE :q1 OR m.nombre LIKE :q2)';
            $q = '%' . $filters['q'] . '%';
            $params['q1'] = $q;
            $params['q2'] = $q;
        }

        $limit = max(1, min(1000, (int) $limit));
        $offset = max(0, (int) $offset);
        $whereSql = implode(' AND ', $where);

        // Consulta principal: columnas base + JOINs (sin asumir collar/microchip/correo)
        $sql = "SELECT m.id_mascota, m.folio, m.id_tutor, m.nombre, m.id_raza, m.especie, m.sexo,
                       m.color, m.edad_aprox, m.senas_particulares,
                       COALESCE(m.foto_url, '') AS foto_url,
                       m.id_alcaldia, m.colonia, m.direccion_ref, m.latitud, m.longitud,
                       m.fecha_extravio, m.estatus, m.id_albergue, m.notas, m.qr_token, m.activo, m.created_at,
                       DATEDIFF(CURDATE(), DATE(m.created_at)) AS antiguedad_dias,
                       COALESCE(r.nombre, '') AS raza_nombre,
                       COALESCE(a.nombre, '') AS alcaldia_nombre,
                       COALESCE(u.nombre, '') AS tutor_nombre,
                       COALESCE(u.telefono, '') AS tutor_telefono
                FROM mascotas m
                LEFT JOIN razas r ON r.id_raza = m.id_raza
                LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
                LEFT JOIN usuarios u ON u.id_usuario = m.id_tutor
                WHERE {$whereSql}
                ORDER BY m.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        try {
            $rows = $this->query($sql, $params)->fetchAll() ?: [];
        } catch (\Throwable $e) {
            // Fallback mínimo sin JOINs
            try {
                $sql2 = "SELECT m.id_mascota, m.folio, m.id_tutor, m.nombre, m.id_raza, m.especie, m.sexo,
                                m.color, m.edad_aprox, m.senas_particulares,
                                COALESCE(m.foto_url, '') AS foto_url,
                                m.id_alcaldia, m.colonia, m.direccion_ref, m.latitud, m.longitud,
                                m.fecha_extravio, m.estatus, m.id_albergue, m.notas, m.qr_token, m.activo, m.created_at,
                                0 AS antiguedad_dias,
                                '' AS raza_nombre, '' AS alcaldia_nombre,
                                '' AS tutor_nombre, '' AS tutor_telefono
                         FROM mascotas m
                         WHERE {$whereSql}
                         ORDER BY m.id_mascota DESC
                         LIMIT {$limit} OFFSET {$offset}";
                $rows = $this->query($sql2, $params)->fetchAll() ?: [];
            } catch (\Throwable $e2) {
                return [];
            }
        }

        // Enriquecer con tamano/collar/microchip si existen (sin romper)
        foreach ($rows as &$row) {
            if (!isset($row['tamano'])) {
                $row['tamano'] = '';
            }
            if (!isset($row['collar'])) {
                $row['collar'] = '';
            }
            if (!isset($row['microchip'])) {
                $row['microchip'] = '';
            }
        }
        unset($row);

        // Intentar rellenar tamano desde columnas si están en un SELECT ampliado
        try {
            $ids = array_filter(array_map(static fn($r) => (int) ($r['id_mascota'] ?? 0), $rows));
            if ($ids) {
                $in = implode(',', $ids);
                $extra = $this->query(
                    "SELECT id_mascota, tamano, collar, microchip FROM mascotas WHERE id_mascota IN ({$in})"
                )->fetchAll() ?: [];
                $map = [];
                foreach ($extra as $ex) {
                    $map[(int) $ex['id_mascota']] = $ex;
                }
                foreach ($rows as &$row) {
                    $id = (int) ($row['id_mascota'] ?? 0);
                    if (isset($map[$id])) {
                        $row['tamano'] = (string) ($map[$id]['tamano'] ?? $row['tamano'] ?? '');
                        $row['collar'] = (string) ($map[$id]['collar'] ?? $row['collar'] ?? '');
                        $row['microchip'] = (string) ($map[$id]['microchip'] ?? $row['microchip'] ?? '');
                    }
                }
                unset($row);
            }
        } catch (\Throwable $e) {
            // columnas opcionales no existen: ignorar
        }

        return $rows;
    }

    
    /** @var bool|null */
    private static $horaColExists = null;

    /** Garantiza columna hora_extravio. true si existe. */
    public function ensureHoraColumn(): bool
    {
        if (self::$horaColExists === true) {
            return true;
        }
        if (self::$horaColExists === false) {
            return false;
        }
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM mascotas LIKE 'hora_extravio'")->fetchAll();
            if ($cols) {
                self::$horaColExists = true;
                return true;
            }
            try {
                $this->db->exec('ALTER TABLE mascotas ADD COLUMN hora_extravio TIME NULL DEFAULT NULL AFTER fecha_extravio');
                self::$horaColExists = true;
                return true;
            } catch (\Throwable $eAlt) {
                self::$horaColExists = false;
                return false;
            }
        } catch (\Throwable $e) {
            self::$horaColExists = false;
            return false;
        }
    }

    public function create(array $data): int
    {
        $this->ensureHoraColumn();
        $folio = !empty($data['folio']) ? (string) $data['folio'] : generate_folio_unique();
        $qr    = !empty($data['qr_token']) ? (string) $data['qr_token'] : bin2hex(random_bytes(16));

        $idRaza = isset($data['id_raza']) ? (int) $data['id_raza'] : 0;
        if ($idRaza <= 0) {
            throw new \InvalidArgumentException('id_raza es obligatorio y debe ser > 0');
        }

        $idAlc = isset($data['id_alcaldia']) ? (int) $data['id_alcaldia'] : 0;
        if ($idAlc <= 0) {
            throw new \InvalidArgumentException('id_alcaldia es obligatorio y debe ser > 0');
        }

        $idAlb = $data['id_albergue'] ?? null;
        if ($idAlb === '' || $idAlb === 0 || $idAlb === '0') {
            $idAlb = null;
        } elseif ($idAlb !== null) {
            $idAlb = (int) $idAlb;
        }

        $fecha = $data['fecha_extravio'] ?? null;
        $horaExtravio = trim((string) ($data['hora_extravio'] ?? $data['hora'] ?? ''));
        if ($horaExtravio === '') {
            $horaExtravio = null;
        } elseif (preg_match('/^\d{1,2}:\d{2}/', $horaExtravio)) {
            $parts = explode(':', $horaExtravio);
            $hh = str_pad((string) ((int) $parts[0]), 2, '0', STR_PAD_LEFT);
            $mm = str_pad((string) ((int) ($parts[1] ?? 0)), 2, '0', STR_PAD_LEFT);
            $ss = isset($parts[2]) ? str_pad((string) ((int) $parts[2]), 2, '0', STR_PAD_LEFT) : '00';
            $horaExtravio = $hh . ':' . $mm . ':' . $ss;
        } else {
            $horaExtravio = null;
        }
        if ($fecha === '') {
            $fecha = null;
        }

        $nullIfEmpty = static function ($v) {
            if ($v === null || $v === '' || $v === false) {
                return null;
            }
            return $v;
        };

        $lat = $data['latitud'] ?? null;
        $lng = $data['longitud'] ?? null;
        if ($lat === '' || $lat === null) {
            $lat = null;
        }
        if ($lng === '' || $lng === null) {
            $lng = null;
        }

        $foto = $data['foto_url'] ?? null;
        if ($foto === '' || $foto === false) {
            $foto = null;
        }
        // Solo rutas locales de uploads
        if (is_string($foto) && $foto !== '') {
            if (!str_starts_with($foto, '/assets/uploads/') && !str_starts_with($foto, 'assets/uploads/')) {
                $foto = null;
            } elseif (!str_starts_with($foto, '/')) {
                $foto = '/' . $foto;
            }
        }

        // Normalizar ENUM a valores exactos de BD (sin NULL inválidos)
        $especieRaw = strtolower(trim((string) ($data['especie'] ?? 'perro')));
        if (str_contains($especieRaw, 'gato') || $especieRaw === 'felino') {
            $especie = 'gato';
        } elseif (str_contains($especieRaw, 'otro')) {
            $especie = 'otro';
        } else {
            $especie = 'perro';
        }
        $sexoRaw = strtolower(trim((string) ($data['sexo'] ?? 'desconocido')));
        if (str_contains($sexoRaw, 'hembra')) {
            $sexo = 'hembra';
        } elseif (str_contains($sexoRaw, 'macho')) {
            $sexo = 'macho';
        } else {
            $sexo = 'desconocido';
        }

        // Textos: nunca NULL (cadena vacía)
        $str = static function ($v): string {
            if ($v === null || $v === false) {
                return '';
            }
            return trim((string) $v);
        };

        $payload = [
            'folio'              => $folio,
            'id_tutor'           => (int) ($data['id_tutor'] ?? 0),
            'nombre'             => trim((string) ($data['nombre'] ?? '')),
            'id_raza'            => $idRaza,
            'especie'            => $especie,
            'sexo'               => $sexo,
            'color'              => $str($data['color'] ?? ''),
            'edad_aprox'         => $str($data['edad_aprox'] ?? ''),
            'senas_particulares' => $str($data['senas_particulares'] ?? ''),
            'collar'             => $str($data['collar'] ?? ''),
            'microchip'          => $str($data['microchip'] ?? ''),
            'tamano'             => $str($data['tamano'] ?? ''),
            'id_alcaldia'        => $idAlc,
            'colonia'            => isset($str) ? $str($data['colonia'] ?? '') : trim((string)($data['colonia'] ?? '')),
            'direccion_ref'      => isset($str) ? $str($data['direccion_ref'] ?? '') : trim((string)($data['direccion_ref'] ?? '')),
            'latitud'            => $lat,
            'longitud'           => $lng,
            'fecha_extravio'     => $fecha,
                        'estatus'            => (string) ($data['estatus'] ?? (defined('STATUS_BUSQUEDA') ? STATUS_BUSQUEDA : 'En Búsqueda')),
            'id_albergue'        => $idAlb,
            'notas'              => isset($str) ? $str($data['notas'] ?? '') : trim((string)($data['notas'] ?? '')),
            'qr_token'           => $qr,
            'activo'             => 1,
        ];
        // Solo incluir foto_url si hay ruta local válida (evita warning y guarda NULL por default)
        if (is_string($foto) && $foto !== '') {
            $payload['foto_url'] = $foto;
        } else {
            $payload['foto_url'] = null;
        }
        // Solo si la columna existe en BD (evita error 500)
        if ($this->ensureHoraColumn() && !empty($horaExtravio)) {
            $payload['hora_extravio'] = $horaExtravio;
        }

        try {
            return $this->insert($payload);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Unknown column') || str_contains($msg, 'collar') || str_contains($msg, 'microchip') || str_contains($msg, 'tamano')) {
                unset($payload['collar'], $payload['microchip'], $payload['tamano'], $payload['hora_extravio']);
                return $this->insert($payload);
            }
            throw $e;
        }
    }

    /** Crear y devolver fila completa (folio + qr_token desde BD) */
    public function createAndFetch(array $data): array
    {
        $id = $this->create($data);
        $row = $this->findWithJoins($id) ?: $this->find($id);
        if (!$row) {
            throw new \RuntimeException('No se pudo leer la mascota recién creada');
        }
        return $row;
    }

    /** Guarda GPS en mascotas (latitud/longitud) — sin NULL */
    public function updateGps(int $id, float|string $lat, float|string $lng): bool
    {
        if ($id <= 0) {
            return false;
        }
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return false;
        }
        $latF = (float) $lat;
        $lngF = (float) $lng;
        if ($latF < -90.0 || $latF > 90.0 || $lngF < -180.0 || $lngF > 180.0) {
            return false;
        }
        // DECIMAL como string con punto decimal (evitar locale)
        $latS = sprintf('%.7F', $latF);
        $lngS = sprintf('%.7F', $lngF);

        try {
            $this->query(
                'UPDATE mascotas SET latitud = :lat, longitud = :lng WHERE id_mascota = :id LIMIT 1',
                ['lat' => $latS, 'lng' => $lngS, 'id' => $id]
            );
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'nombre', 'id_raza', 'especie', 'sexo', 'color', 'edad_aprox',
            'senas_particulares', 'collar', 'microchip', 'tamano', 'foto_url', 'id_alcaldia', 'colonia',
            'direccion_ref', 'latitud', 'longitud', 'fecha_extravio', 'hora_extravio',
            'estatus', 'id_albergue', 'notas', 'activo',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        // Normalizar ENUM / FKs
        if (isset($filtered['especie'])) {
            $e = strtolower(trim((string) $filtered['especie']));
            $filtered['especie'] = str_contains($e, 'gato') ? 'gato' : (str_contains($e, 'otro') ? 'otro' : 'perro');
        }
        if (isset($filtered['sexo'])) {
            $s = strtolower(trim((string) $filtered['sexo']));
            $filtered['sexo'] = str_contains($s, 'hembra') ? 'hembra' : (str_contains($s, 'macho') ? 'macho' : 'desconocido');
        }
        foreach (['id_raza', 'id_alcaldia', 'id_albergue'] as $fk) {
            if (array_key_exists($fk, $filtered)) {
                if ($filtered[$fk] === '' || $filtered[$fk] === '0' || $filtered[$fk] === 0 || $filtered[$fk] === null) {
                    $filtered[$fk] = null;
                } else {
                    $filtered[$fk] = (int) $filtered[$fk];
                }
            }
        }
        if (array_key_exists('fecha_extravio', $filtered) && $filtered['fecha_extravio'] === '') {
            $filtered['fecha_extravio'] = null;
        }
        if (array_key_exists('hora_extravio', $filtered) && !$this->ensureHoraColumn()) {
            unset($filtered['hora_extravio']);
        }
        // Textos: nunca null
        foreach (['color', 'edad_aprox', 'senas_particulares', 'collar', 'microchip', 'tamano', 'colonia', 'direccion_ref', 'notas'] as $tf) {
            if (array_key_exists($tf, $filtered) && $filtered[$tf] === null) {
                $filtered[$tf] = '';
            }
        }
        return $this->updateById($id, $filtered);
    }

    public function statsByTutor(int $idTutor): array
    {
        $rows = $this->query(
            'SELECT estatus, COUNT(*) AS total FROM mascotas WHERE id_tutor = :id_tutor AND activo = 1 GROUP BY estatus',
            ['id_tutor' => $idTutor]
        )->fetchAll();
        $out = ['total' => 0, 'En Búsqueda' => 0, 'En Resguardo' => 0, 'Localizado' => 0];
        foreach ($rows as $r) {
            $out[$r['estatus']] = (int) $r['total'];
            $out['total'] += (int) $r['total'];
        }
        return $out;
    }

    public function statsByAlcaldia(): array
    {
        return $this->query(
            'SELECT COALESCE(a.nombre, \'Sin alcaldía\') AS alcaldia,
                    COUNT(m.id_mascota) AS total,
                    SUM(CASE WHEN m.estatus = \'En Búsqueda\' THEN 1 ELSE 0 END) AS busqueda,
                    SUM(CASE WHEN m.estatus = \'En Resguardo\' THEN 1 ELSE 0 END) AS resguardo,
                    SUM(CASE WHEN m.estatus = \'Localizado\' THEN 1 ELSE 0 END) AS localizado
             FROM mascotas m
             LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
             WHERE m.activo = 1
             GROUP BY a.id_alcaldia, a.nombre
             ORDER BY total DESC'
        )->fetchAll();
    }

    public function statsGlobal(): array
    {
        $rows = $this->query(
            'SELECT estatus, COUNT(*) AS total FROM mascotas WHERE activo = 1 GROUP BY estatus'
        )->fetchAll();
        $out = ['total' => 0, 'En Búsqueda' => 0, 'En Resguardo' => 0, 'Localizado' => 0];
        foreach ($rows as $r) {
            $out[$r['estatus']] = (int) $r['total'];
            $out['total'] += (int) $r['total'];
        }
        return $out;
    }

    public function softDelete(int $id): bool
    {
        return $this->updateById($id, ['activo' => 0]);
    }
}
