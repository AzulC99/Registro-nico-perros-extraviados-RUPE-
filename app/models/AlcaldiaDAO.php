<?php
declare(strict_types=1);

namespace App\Models;

final class AlcaldiaDAO extends BaseDAO
{
    protected string $table = 'alcaldias';
    protected string $primaryKey = 'id_alcaldia';

    /** Solo referencia para rellenar zona/cp vacíos en BD (no inventa filas) */
    public static function metaCdmx(): array
    {
        return [
            'Álvaro Obregón' => ['zona' => 'Poniente', 'cp' => '01000'],
            'Azcapotzalco' => ['zona' => 'Norte', 'cp' => '02000'],
            'Benito Juárez' => ['zona' => 'Centro-Sur', 'cp' => '03000'],
            'Coyoacán' => ['zona' => 'Sur', 'cp' => '04000'],
            'Cuajimalpa de Morelos' => ['zona' => 'Poniente', 'cp' => '05000'],
            'Cuauhtémoc' => ['zona' => 'Centro', 'cp' => '06000'],
            'Gustavo A. Madero' => ['zona' => 'Norte', 'cp' => '07000'],
            'Iztacalco' => ['zona' => 'Oriente', 'cp' => '08000'],
            'Iztapalapa' => ['zona' => 'Oriente', 'cp' => '09000'],
            'La Magdalena Contreras' => ['zona' => 'Sur', 'cp' => '10000'],
            'Miguel Hidalgo' => ['zona' => 'Poniente', 'cp' => '11000'],
            'Milpa Alta' => ['zona' => 'Sur', 'cp' => '12000'],
            'Tláhuac' => ['zona' => 'Oriente', 'cp' => '13000'],
            'Tlalpan' => ['zona' => 'Sur', 'cp' => '14000'],
            'Venustiano Carranza' => ['zona' => 'Oriente', 'cp' => '15000'],
            'Xochimilco' => ['zona' => 'Sur', 'cp' => '16000'],
        ];
    }

    public function ensureExtraColumns(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        foreach (['zona VARCHAR(40) NULL', 'cp_base VARCHAR(10) NULL'] as $col) {
            try {
                $this->query('ALTER TABLE alcaldias ADD COLUMN ' . $col);
            } catch (\Throwable $e) {
            }
        }
        // Rellenar zona/cp solo donde estén vacíos y el nombre coincida
        foreach (self::metaCdmx() as $nombre => $meta) {
            try {
                $this->query(
                    'UPDATE alcaldias SET zona = COALESCE(NULLIF(TRIM(zona), \'\'), :z),
                                        cp_base = COALESCE(NULLIF(TRIM(cp_base), \'\'), :c)
                     WHERE nombre = :n',
                    ['z' => $meta['zona'], 'c' => $meta['cp'], 'n' => $nombre]
                );
            } catch (\Throwable $e) {
            }
        }
    }

    public function listActive(): array
    {
        return $this->query(
            'SELECT * FROM alcaldias WHERE activo = 1 ORDER BY nombre'
        )->fetchAll() ?: [];
    }

    /**
     * SELECT real de alcaldias + conteo de mascotas (casos de tutores).
     * Sin filas inventadas.
     */
    public function listAllAdmin(): array
    {
        $this->ensureExtraColumns();

        $sql = "
            SELECT
                a.id_alcaldia,
                a.nombre,
                a.clave,
                a.activo,
                a.created_at,
                a.zona,
                a.cp_base,
                COALESCE(cnt.casos, 0) AS casos_registrados
            FROM alcaldias a
            LEFT JOIN (
                SELECT id_alcaldia, COUNT(*) AS casos
                FROM mascotas
                WHERE id_alcaldia IS NOT NULL AND id_alcaldia > 0
                  AND (activo = 1 OR activo IS NULL OR activo = '1')
                GROUP BY id_alcaldia
            ) cnt ON cnt.id_alcaldia = a.id_alcaldia
            ORDER BY a.nombre ASC, a.id_alcaldia ASC
        ";

        try {
            $rows = $this->query($sql)->fetchAll() ?: [];
        } catch (\Throwable $e) {
            try {
                $rows = $this->query(
                    'SELECT id_alcaldia, nombre, clave, activo, created_at
                     FROM alcaldias ORDER BY nombre'
                )->fetchAll() ?: [];
                foreach ($rows as &$r) {
                    $id = (int) ($r['id_alcaldia'] ?? 0);
                    $r['zona'] = $r['zona'] ?? '';
                    $r['cp_base'] = $r['cp_base'] ?? '';
                    try {
                        $c = $this->query(
                            'SELECT COUNT(*) AS c FROM mascotas
                             WHERE id_alcaldia = :id AND (activo = 1 OR activo IS NULL)',
                            ['id' => $id]
                        )->fetch();
                        $r['casos_registrados'] = (int) ($c['c'] ?? 0);
                    } catch (\Throwable $e2) {
                        $r['casos_registrados'] = 0;
                    }
                }
                unset($r);
            } catch (\Throwable $e3) {
                $rows = [];
            }
        }

        $meta = self::metaCdmx();
        $seen = [];
        $out = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id_alcaldia'] ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $nombre = trim((string) ($r['nombre'] ?? ''));
            $zona = trim((string) ($r['zona'] ?? ''));
            $cp = trim((string) ($r['cp_base'] ?? ''));
            if (($zona === '' || $cp === '') && isset($meta[$nombre])) {
                if ($zona === '') {
                    $zona = $meta[$nombre]['zona'];
                }
                if ($cp === '') {
                    $cp = $meta[$nombre]['cp'];
                }
            }
            $clave = trim((string) ($r['clave'] ?? ''));
            if ($clave === '') {
                $clave = 'CDMX-' . str_pad((string) $id, 2, '0', STR_PAD_LEFT);
            }
            $out[] = [
                'id_alcaldia'        => $id,
                'nombre'             => $nombre,
                'clave'              => $clave,
                'zona'               => $zona,
                'cp_base'            => $cp,
                'activo'             => (int) ($r['activo'] ?? 1),
                'created_at'         => (string) ($r['created_at'] ?? ''),
                'casos_registrados'  => (int) ($r['casos_registrados'] ?? 0),
                'registros'          => (int) ($r['casos_registrados'] ?? 0),
            ];
        }
        return $out;
    }

    /** Siguiente ID CDMX automático: CDMX-01, CDMX-02, … */
    public function nextClaveCDMX(): string
    {
        $this->ensureExtraColumns();
        $max = 0;
        try {
            $rows = $this->db->query(
                "SELECT id_alcaldia, clave FROM alcaldias"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $r) {
                $id = (int) ($r['id_alcaldia'] ?? 0);
                if ($id > $max) {
                    $max = $id;
                }
                $c = strtoupper(trim((string) ($r['clave'] ?? '')));
                if (preg_match('/(\d+)/', $c, $m)) {
                    $n = (int) $m[1];
                    if ($n > $max) {
                        $max = $n;
                    }
                }
            }
        } catch (\Throwable $e) {
            try {
                $max = (int) $this->db->query('SELECT COALESCE(MAX(id_alcaldia),0) FROM alcaldias')->fetchColumn();
            } catch (\Throwable $e2) {
                $max = 0;
            }
        }
        $next = $max + 1;
        return 'CDMX-' . str_pad((string) $next, 2, '0', STR_PAD_LEFT);
    }

    public function create(array $data): int
    {
        $this->ensureExtraColumns();
        $clave = trim((string) ($data['clave'] ?? ''));
        if ($clave === '' || $clave === '(automático)' || strcasecmp($clave, 'automatico') === 0) {
            $clave = $this->nextClaveCDMX();
        }
        $payload = [
            'nombre' => trim((string) $data['nombre']),
            'clave'  => $clave,
            'activo' => $data['activo'] ?? 1,
        ];
        if (isset($data['zona'])) {
            $payload['zona'] = (string) $data['zona'];
        }
        if (isset($data['cp_base']) || isset($data['cp'])) {
            $payload['cp_base'] = (string) ($data['cp_base'] ?? $data['cp'] ?? '');
        }
        return $this->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $this->ensureExtraColumns();
        $allowed = ['nombre', 'clave', 'activo', 'zona', 'cp_base'];
        if (isset($data['cp']) && !isset($data['cp_base'])) {
            $data['cp_base'] = $data['cp'];
        }
        return $this->updateById($id, array_intersect_key($data, array_flip($allowed)));
    }
}
