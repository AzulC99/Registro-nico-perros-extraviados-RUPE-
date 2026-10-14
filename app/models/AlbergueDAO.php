<?php
declare(strict_types=1);

namespace App\Models;

final class AlbergueDAO extends BaseDAO
{
    protected string $table = 'albergues';
    protected string $primaryKey = 'id_albergue';

    public function ensureExtraColumns(): void
    {
        try {
            $cols = $this->db->query('SHOW COLUMNS FROM albergues')->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $names = [];
            foreach ($cols as $c) {
                $names[strtolower((string) ($c['Field'] ?? ''))] = true;
            }
            if (!isset($names['tipo'])) {
                $this->db->exec("ALTER TABLE albergues ADD COLUMN tipo VARCHAR(60) NULL DEFAULT NULL AFTER nombre");
            }
            if (!isset($names['correo'])) {
                $this->db->exec("ALTER TABLE albergues ADD COLUMN correo VARCHAR(180) NULL DEFAULT NULL AFTER telefono");
            }
        } catch (\Throwable $e) {
            // sin privilegios ALTER
        }
    }

    private function mapRow(array $row): array
    {
        $id = (int) ($row['id_albergue'] ?? $row['id'] ?? 0);
        $idAlc = isset($row['id_alcaldia']) && $row['id_alcaldia'] !== null && $row['id_alcaldia'] !== ''
            ? (int) $row['id_alcaldia'] : 0;
        $cap = isset($row['capacidad']) && $row['capacidad'] !== null && $row['capacidad'] !== ''
            ? (int) $row['capacidad'] : 0;
        $ocup = isset($row['ocupacion']) ? (int) $row['ocupacion'] : 0;
        $lat = $row['latitud'] ?? null;
        $lng = $row['longitud'] ?? null;
        $correo = trim((string) ($row['correo'] ?? $row['email'] ?? ''));
        $tel = trim((string) ($row['telefono'] ?? ''));
        $alcNom = trim((string) ($row['alcaldia_nombre'] ?? $row['alcaldia'] ?? ''));
        $tipo = trim((string) ($row['tipo'] ?? ''));
        if ($tipo === '') {
            $tipo = 'Albergue';
        }

        return [
            'id_albergue'     => $id,
            'id'              => $id,
            'nombre'          => (string) ($row['nombre'] ?? ''),
            'tipo'            => $tipo,
            'direccion'       => (string) ($row['direccion'] ?? ''),
            'id_alcaldia'     => $idAlc,
            'alcaldia'        => $alcNom,
            'alcaldia_nombre' => $alcNom,
            'telefono'        => $tel,
            'correo'          => $correo,
            'email'           => $correo,
            'latitud'         => ($lat !== null && $lat !== '') ? (float) $lat : null,
            'longitud'        => ($lng !== null && $lng !== '') ? (float) $lng : null,
            'capacidad'       => $cap,
            'ocupacion'       => $ocup,
            'activo'          => (int) ($row['activo'] ?? 1),
        ];
    }

    private function baseSelect(): string
    {
        return 'SELECT a.id_albergue, a.nombre, a.direccion, a.id_alcaldia, a.telefono, a.correo,
                       a.latitud, a.longitud, a.capacidad, a.activo,
                       al.nombre AS alcaldia_nombre,
                       (SELECT COUNT(*) FROM mascotas m
                         WHERE m.id_albergue = a.id_albergue AND COALESCE(m.activo,1) = 1) AS ocupacion
                FROM albergues a
                LEFT JOIN alcaldias al ON al.id_alcaldia = a.id_alcaldia';
    }

    private function selectWithTipo(): string
    {
        // Intenta incluir tipo; si falla el caller usa baseSelect
        return 'SELECT a.id_albergue, a.nombre, a.tipo, a.direccion, a.id_alcaldia, a.telefono, a.correo,
                       a.latitud, a.longitud, a.capacidad, a.activo,
                       al.nombre AS alcaldia_nombre,
                       (SELECT COUNT(*) FROM mascotas m
                         WHERE m.id_albergue = a.id_albergue AND COALESCE(m.activo,1) = 1) AS ocupacion
                FROM albergues a
                LEFT JOIN alcaldias al ON al.id_alcaldia = a.id_alcaldia';
    }

    private function fetchList(string $whereSql, array $params): array
    {
        $this->ensureExtraColumns();
        try {
            $sql = $this->selectWithTipo() . ' ' . $whereSql;
            $rows = $this->query($sql, $params)->fetchAll() ?: [];
        } catch (\Throwable $e) {
            $sql = $this->baseSelect() . ' ' . $whereSql;
            $rows = $this->query($sql, $params)->fetchAll() ?: [];
        }
        return array_map(fn(array $r) => $this->mapRow($r), $rows);
    }

    public function listActive(?int $idAlcaldia = null): array
    {
        $where = 'WHERE a.activo = 1';
        $params = [];
        if ($idAlcaldia !== null && $idAlcaldia > 0) {
            $where .= ' AND a.id_alcaldia = :id_alcaldia';
            $params['id_alcaldia'] = $idAlcaldia;
        }
        $where .= ' ORDER BY a.id_albergue ASC';
        return $this->fetchList($where, $params);
    }

    public function listAll(?int $idAlcaldia = null, bool $soloActivos = true): array
    {
        $where = 'WHERE 1=1';
        $params = [];
        if ($soloActivos) {
            $where .= ' AND a.activo = 1';
        }
        if ($idAlcaldia !== null && $idAlcaldia > 0) {
            $where .= ' AND a.id_alcaldia = :id_alcaldia';
            $params['id_alcaldia'] = $idAlcaldia;
        }
        $where .= ' ORDER BY a.id_albergue ASC';
        return $this->fetchList($where, $params);
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $this->ensureExtraColumns();
        try {
            $sql = $this->selectWithTipo() . ' WHERE a.id_albergue = :id LIMIT 1';
            $row = $this->query($sql, ['id' => $id])->fetch();
        } catch (\Throwable $e) {
            $sql = $this->baseSelect() . ' WHERE a.id_albergue = :id LIMIT 1';
            $row = $this->query($sql, ['id' => $id])->fetch();
        }
        return $row ? $this->mapRow($row) : null;
    }

    public function create(array $data): int
    {
        $this->ensureExtraColumns();
        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($nombre === '') {
            throw new \InvalidArgumentException('El nombre del albergue es obligatorio.');
        }

        $tipo = trim((string) ($data['tipo'] ?? ''));
        if ($tipo === '') {
            $tipo = 'Albergue';
        }

        $idAlc = isset($data['id_alcaldia']) && (int) $data['id_alcaldia'] > 0
            ? (int) $data['id_alcaldia'] : null;
        $cap = isset($data['capacidad']) && $data['capacidad'] !== '' && $data['capacidad'] !== null
            ? max(1, (int) $data['capacidad']) : null;

        $tel = preg_replace('/\D+/', '', (string) ($data['telefono'] ?? ''));
        $tel = $tel !== '' ? substr($tel, 0, 20) : null;

        $correo = trim((string) ($data['correo'] ?? $data['email'] ?? ''));
        $correo = $correo !== '' ? mb_substr($correo, 0, 180) : null;

        $dir = trim((string) ($data['direccion'] ?? ''));
        $dir = $dir !== '' ? mb_substr($dir, 0, 255) : null;

        $lat = isset($data['latitud']) && $data['latitud'] !== '' && $data['latitud'] !== null
            ? (float) $data['latitud'] : null;
        $lng = isset($data['longitud']) && $data['longitud'] !== '' && $data['longitud'] !== null
            ? (float) $data['longitud'] : null;

        $payload = [
            'nombre'      => mb_substr($nombre, 0, 150),
            'direccion'   => $dir,
            'id_alcaldia' => $idAlc,
            'telefono'    => $tel,
            'correo'      => $correo,
            'latitud'     => $lat,
            'longitud'    => $lng,
            'capacidad'   => $cap,
            'activo'      => isset($data['activo']) ? ((int) $data['activo'] ? 1 : 0) : 1,
        ];

        // Intentar con tipo
        try {
            $payload['tipo'] = mb_substr($tipo, 0, 60);
            return $this->insert($payload);
        } catch (\Throwable $e) {
            unset($payload['tipo']);
            return $this->insert($payload);
        }
    }

    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }
        $this->ensureExtraColumns();
        $payload = [];

        if (array_key_exists('nombre', $data)) {
            $n = trim((string) $data['nombre']);
            if ($n === '') {
                throw new \InvalidArgumentException('El nombre del albergue es obligatorio.');
            }
            $payload['nombre'] = mb_substr($n, 0, 150);
        }
        if (array_key_exists('tipo', $data)) {
            $tp = trim((string) ($data['tipo'] ?? ''));
            $payload['tipo'] = $tp !== '' ? mb_substr($tp, 0, 60) : null;
        }
        if (array_key_exists('direccion', $data)) {
            $d = trim((string) ($data['direccion'] ?? ''));
            $payload['direccion'] = $d !== '' ? mb_substr($d, 0, 255) : null;
        }
        if (array_key_exists('id_alcaldia', $data)) {
            $ia = (int) ($data['id_alcaldia'] ?? 0);
            $payload['id_alcaldia'] = $ia > 0 ? $ia : null;
        }
        if (array_key_exists('telefono', $data)) {
            $t = preg_replace('/\D+/', '', (string) ($data['telefono'] ?? ''));
            $payload['telefono'] = $t !== '' ? substr($t, 0, 20) : null;
        }
        if (array_key_exists('correo', $data) || array_key_exists('email', $data)) {
            $c = trim((string) ($data['correo'] ?? $data['email'] ?? ''));
            $payload['correo'] = $c !== '' ? mb_substr($c, 0, 180) : null;
        }
        if (array_key_exists('latitud', $data)) {
            $payload['latitud'] = ($data['latitud'] === '' || $data['latitud'] === null)
                ? null : (float) $data['latitud'];
        }
        if (array_key_exists('longitud', $data)) {
            $payload['longitud'] = ($data['longitud'] === '' || $data['longitud'] === null)
                ? null : (float) $data['longitud'];
        }
        if (array_key_exists('capacidad', $data)) {
            $payload['capacidad'] = ($data['capacidad'] === '' || $data['capacidad'] === null)
                ? null : max(1, (int) $data['capacidad']);
        }
        if (array_key_exists('activo', $data)) {
            $payload['activo'] = (int) $data['activo'] ? 1 : 0;
        }
        if ($payload === []) {
            return true;
        }
        try {
            return $this->updateById($id, $payload);
        } catch (\Throwable $e) {
            // Si falla por columna tipo, reintentar sin ella
            if (isset($payload['tipo'])) {
                unset($payload['tipo']);
                if ($payload === []) {
                    return true;
                }
                return $this->updateById($id, $payload);
            }
            throw $e;
        }
    }

    /** Baja lógica */
    public function softDelete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        return $this->updateById($id, ['activo' => 0]);
    }
}
