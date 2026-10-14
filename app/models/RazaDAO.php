<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Catálogo razas — SELECT con JOIN a mascotas para tamaño y conteo.
 * Sin duplicados (por id_raza y por nombre).
 */
final class RazaDAO extends BaseDAO
{
    protected string $table = 'razas';
    protected string $primaryKey = 'id_raza';

    public function ensureExtraColumns(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $cols = $this->db->query('SHOW COLUMNS FROM razas')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $names = [];
            foreach ($cols as $c) {
                $names[strtolower((string) ($c['Field'] ?? ''))] = true;
            }
            if (!isset($names['tamano'])) {
                $this->db->exec("ALTER TABLE razas ADD COLUMN tamano VARCHAR(40) NULL DEFAULT NULL");
            }
            if (!isset($names['especie'])) {
                $this->db->exec("ALTER TABLE razas ADD COLUMN especie VARCHAR(40) NULL DEFAULT 'perro'");
            }
            if (!isset($names['activo'])) {
                $this->db->exec('ALTER TABLE razas ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1');
            }
        } catch (\Throwable $e) {
            // no bloquear
        }
    }

    private function normalizeTamano(string $tam): string
    {
        $tam = trim($tam);
        if ($tam === '') {
            return '';
        }
        $t = mb_strtolower($tam);
        if (str_contains($t, 'mini')) {
            return 'Miniatura';
        }
        if (str_contains($t, 'peque') || str_contains($t, 'chico')) {
            return 'Pequeño';
        }
        if (str_contains($t, 'median')) {
            return 'Mediano';
        }
        if (str_contains($t, 'gigant')) {
            return 'Gigante';
        }
        if (str_contains($t, 'grand')) {
            return 'Grande';
        }
        return $tam;
    }

    /**
     * Listado admin: razas + tamaño desde razas o el más frecuente en mascotas.
     * Sin filas duplicadas.
     */
    public function listAllAdmin(): array
    {
        $this->ensureExtraColumns();
        $pdo = $this->db;

        // 1) Todas las razas (perro)
        try {
            $rows = $pdo->query(
                "SELECT id_raza, nombre,
                        COALESCE(especie, 'perro') AS especie,
                        COALESCE(activo, 1) AS activo,
                        created_at,
                        COALESCE(NULLIF(TRIM(tamano), ''), '') AS raza_tamano
                 FROM razas
                 ORDER BY nombre ASC, id_raza ASC"
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            try {
                $rows = $pdo->query(
                    'SELECT id_raza, nombre, especie, activo, created_at, tamano AS raza_tamano FROM razas ORDER BY nombre'
                )->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e2) {
                return [];
            }
        }

        // 2) SELECT desde mascotas: conteo + tamaños por id_raza (PHP, sin subquery anidada)
        $agg = []; // id_raza => ['registros' => int, 'tamanos' => [tam => count]]
        try {
            $mRows = $pdo->query(
                "SELECT id_raza, COALESCE(NULLIF(TRIM(tamano), ''), '') AS tamano
                 FROM mascotas
                 WHERE id_raza IS NOT NULL AND id_raza > 0"
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($mRows as $mr) {
                $idR = (int) ($mr['id_raza'] ?? 0);
                if ($idR <= 0) {
                    continue;
                }
                if (!isset($agg[$idR])) {
                    $agg[$idR] = ['registros' => 0, 'tamanos' => []];
                }
                $agg[$idR]['registros']++;
                $t = $this->normalizeTamano((string) ($mr['tamano'] ?? ''));
                if ($t !== '') {
                    if (!isset($agg[$idR]['tamanos'][$t])) {
                        $agg[$idR]['tamanos'][$t] = 0;
                    }
                    $agg[$idR]['tamanos'][$t]++;
                }
            }
        } catch (\Throwable $e) {
            // tabla mascotas sin columna tamano u otro error
            try {
                $mRows = $pdo->query(
                    'SELECT id_raza FROM mascotas WHERE id_raza IS NOT NULL AND id_raza > 0'
                )->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($mRows as $mr) {
                    $idR = (int) ($mr['id_raza'] ?? 0);
                    if ($idR <= 0) {
                        continue;
                    }
                    if (!isset($agg[$idR])) {
                        $agg[$idR] = ['registros' => 0, 'tamanos' => []];
                    }
                    $agg[$idR]['registros']++;
                }
            } catch (\Throwable $e2) {
            }
        }

        $out = [];
        $seen = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id_raza'] ?? 0);
            $nombre = trim((string) ($r['nombre'] ?? ''));
            if ($id <= 0 || $nombre === '') {
                continue;
            }
            // solo perro
            $esp = mb_strtolower(trim((string) ($r['especie'] ?? 'perro')));
            if ($esp !== '' && $esp !== 'perro' && $esp !== 'canino') {
                continue;
            }
            $key = mb_strtolower($nombre);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $tamRaza = $this->normalizeTamano((string) ($r['raza_tamano'] ?? $r['tamano'] ?? ''));
            $tamMasc = '';
            $regs = 0;
            if (isset($agg[$id])) {
                $regs = (int) ($agg[$id]['registros'] ?? 0);
                if (!empty($agg[$id]['tamanos'])) {
                    arsort($agg[$id]['tamanos']);
                    $tamMasc = (string) array_key_first($agg[$id]['tamanos']);
                    $tamMasc = $this->normalizeTamano($tamMasc);
                }
            }

            // Preferir razas.tamano; si vacío, el de mascotas
            $tam = $tamRaza !== '' ? $tamRaza : $tamMasc;

            // Sincronizar a razas.tamano si estaba vacío y hay dato en mascotas
            if ($tamRaza === '' && $tamMasc !== '') {
                try {
                    $pdo->prepare('UPDATE razas SET tamano = :t WHERE id_raza = :id AND (tamano IS NULL OR TRIM(tamano) = \'\')')
                        ->execute(['t' => $tamMasc, 'id' => $id]);
                } catch (\Throwable $eSync) {
                }
            }

            $out[] = [
                'id'             => $id,
                'id_raza'        => $id,
                'codigo'         => 'RZ-' . str_pad((string) $id, 2, '0', STR_PAD_LEFT),
                'nombre'         => $nombre,
                'especie'        => 'perro',
                'especie_label'  => 'perro',
                'tamano'         => $tam !== '' ? $tam : '—',
                'tamano_raza'    => $tamRaza !== '' ? $tamRaza : '',
                'tamano_mascota' => $tamMasc !== '' ? $tamMasc : '',
                'activo'         => (int) ($r['activo'] ?? 1) === 1 ? 1 : 0,
                'estado'         => ((int) ($r['activo'] ?? 1) === 1) ? 'Activo' : 'Inactivo',
                'created_at'     => (string) (($r['created_at'] ?? '') !== '' && ($r['created_at'] ?? '') !== null ? $r['created_at'] : ''),
                'registros'      => max(0, (int) $regs),
            ];
        }

        return $out;
    }

    /** Tamaños distintos presentes en mascotas (para el select del modal) */
    public function listTamanosFromMascotas(): array
    {
        $base = ['Miniatura', 'Pequeño', 'Mediano', 'Grande', 'Gigante'];
        $fromDb = [];
        try {
            $rows = $this->db->query(
                "SELECT DISTINCT COALESCE(NULLIF(TRIM(tamano), ''), '') AS t
                 FROM mascotas
                 WHERE tamano IS NOT NULL AND TRIM(tamano) <> ''
                 ORDER BY t ASC"
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $t = $this->normalizeTamano((string) ($row['t'] ?? ''));
                if ($t !== '' && !in_array($t, $fromDb, true)) {
                    $fromDb[] = $t;
                }
            }
        } catch (\Throwable $e) {
        }
        $out = [];
        foreach (array_merge($base, $fromDb) as $t) {
            if ($t !== '' && !in_array($t, $out, true)) {
                $out[] = $t;
            }
        }
        return $out;
    }

    public function listActive(?string $especie = null): array
    {
        $this->ensureExtraColumns();
        $esp = $especie ?: 'perro';
        try {
            $stmt = $this->db->prepare(
                "SELECT id_raza, nombre, COALESCE(especie,'perro') AS especie, COALESCE(tamano,'') AS tamano
                 FROM razas
                 WHERE COALESCE(activo,1) = 1
                   AND (especie = :e OR especie IS NULL OR especie = '' OR LOWER(especie) = 'canino')
                 ORDER BY nombre"
            );
            $stmt->execute(['e' => $esp]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        $seen = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id_raza'] ?? 0);
            $n = mb_strtolower(trim((string) ($r['nombre'] ?? '')));
            if ($id <= 0 || $n === '' || isset($seen[$n])) {
                continue;
            }
            $seen[$n] = true;
            $r['especie'] = 'perro';
            $r['tamano'] = $this->normalizeTamano((string) ($r['tamano'] ?? ''));
            $out[] = $r;
        }
        return $out;
    }

    public function create(array $data): int
    {
        $this->ensureExtraColumns();
        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($nombre === '') {
            throw new \RuntimeException('Nombre de raza vacío');
        }
        $tam = $this->normalizeTamano((string) ($data['tamano'] ?? $data['tamaño'] ?? ''));
        $activo = isset($data['activo']) ? (int) (bool) $data['activo'] : 1;
        $pdo = $this->db;

        // Si ya existe por nombre → actualizar y devolver id
        try {
            $chk = $pdo->prepare(
                "SELECT id_raza FROM razas WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:n)) LIMIT 1"
            );
            $chk->execute(['n' => $nombre]);
            $exist = $chk->fetch(PDO::FETCH_ASSOC);
            if ($exist && (int) ($exist['id_raza'] ?? 0) > 0) {
                $idExist = (int) $exist['id_raza'];
                $this->update($idExist, [
                    'nombre' => $nombre,
                    'tamano' => $tam,
                    'activo' => $activo,
                    'especie' => 'perro',
                ]);
                return $idExist;
            }
        } catch (\Throwable $e) {
            // continuar a INSERT
        }

        $id = 0;
        $lastError = null;

        // INSERT completo con tamaño
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO razas (nombre, especie, tamano, activo) VALUES (:nombre, 'perro', :tamano, :activo)"
            );
            $stmt->execute([
                'nombre' => $nombre,
                'tamano' => $tam !== '' ? $tam : null,
                'activo' => $activo,
            ]);
            $id = (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            $lastError = $e;
            if (str_contains($e->getMessage(), '1062') || str_contains(strtolower($e->getMessage()), 'duplicate')) {
                try {
                    $chk = $pdo->prepare(
                        "SELECT id_raza FROM razas WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:n)) LIMIT 1"
                    );
                    $chk->execute(['n' => $nombre]);
                    $exist = $chk->fetch(PDO::FETCH_ASSOC);
                    $id = (int) ($exist['id_raza'] ?? 0);
                    if ($id > 0) {
                        $this->update($id, ['tamano' => $tam, 'activo' => $activo, 'especie' => 'perro']);
                        return $id;
                    }
                } catch (\Throwable $e2) {
                }
            }
        }

        // Fallback: solo nombre + especie
        if ($id <= 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO razas (nombre, especie) VALUES (:nombre, 'perro')");
                $stmt->execute(['nombre' => $nombre]);
                $id = (int) $pdo->lastInsertId();
            } catch (\Throwable $e) {
                $lastError = $e;
            }
        }

        // Fallback: solo nombre
        if ($id <= 0) {
            try {
                $stmt = $pdo->prepare('INSERT INTO razas (nombre) VALUES (:nombre)');
                $stmt->execute(['nombre' => $nombre]);
                $id = (int) $pdo->lastInsertId();
            } catch (\Throwable $e) {
                $lastError = $e;
            }
        }

        // Recuperar id por nombre
        if ($id <= 0) {
            try {
                $chk = $pdo->prepare(
                    "SELECT id_raza FROM razas WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:n)) ORDER BY id_raza DESC LIMIT 1"
                );
                $chk->execute(['n' => $nombre]);
                $exist = $chk->fetch(PDO::FETCH_ASSOC);
                $id = (int) ($exist['id_raza'] ?? 0);
            } catch (\Throwable $e) {
            }
        }

        if ($id <= 0) {
            $msg = $lastError ? $lastError->getMessage() : 'lastInsertId=0';
            throw new \RuntimeException('No se pudo insertar en razas: ' . $msg);
        }

        // Forzar tamano / especie / activo
        try {
            $this->update($id, [
                'nombre' => $nombre,
                'tamano' => $tam,
                'activo' => $activo,
                'especie' => 'perro',
            ]);
        } catch (\Throwable $e) {
            if ($tam !== '') {
                try {
                    $pdo->prepare('UPDATE razas SET tamano = :t WHERE id_raza = :id')
                        ->execute(['t' => $tam, 'id' => $id]);
                } catch (\Throwable $e2) {
                }
            }
        }

        return $id;
    }

    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }
        $this->ensureExtraColumns();
        $sets = [];
        $params = ['id' => $id];

        if (isset($data['nombre']) && trim((string) $data['nombre']) !== '') {
            $sets[] = 'nombre = :nombre';
            $params['nombre'] = trim((string) $data['nombre']);
        }
        if (array_key_exists('activo', $data)) {
            $sets[] = 'activo = :activo';
            $params['activo'] = (int) (bool) $data['activo'];
        }
        if (array_key_exists('tamano', $data) || array_key_exists('tamaño', $data)) {
            $raw = (string) ($data['tamano'] ?? $data['tamaño'] ?? '');
            $tam = $this->normalizeTamano($raw);
            $sets[] = 'tamano = :tamano';
            $params['tamano'] = $tam !== '' ? $tam : null;
        }
        $sets[] = "especie = 'perro'";

        if (count($sets) <= 1 && !isset($params['nombre']) && !isset($params['activo']) && !array_key_exists('tamano', $params)) {
            // solo especie forzada sin otros campos
            return true;
        }

        $sql = 'UPDATE razas SET ' . implode(', ', $sets) . ' WHERE id_raza = :id LIMIT 1';
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            // Fallback sin LIMIT
            try {
                $sql2 = 'UPDATE razas SET ' . implode(', ', $sets) . ' WHERE id_raza = :id';
                $stmt = $this->db->prepare($sql2);
                return $stmt->execute($params);
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }

    public function find(int $id): ?array
    {
        $this->ensureExtraColumns();
        $stmt = $this->db->prepare(
            'SELECT id_raza, nombre, COALESCE(especie,\'perro\') AS especie,
                    COALESCE(tamano, \'\') AS tamano, COALESCE(activo,1) AS activo, created_at
             FROM razas WHERE id_raza = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['especie'] = 'perro';
        $row['especie_label'] = 'perro';
        $row['tamano'] = $this->normalizeTamano((string) ($row['tamano'] ?? ''));
        $row['codigo'] = 'RZ-' . str_pad((string) $row['id_raza'], 2, '0', STR_PAD_LEFT);
        return $row;
    }
}
