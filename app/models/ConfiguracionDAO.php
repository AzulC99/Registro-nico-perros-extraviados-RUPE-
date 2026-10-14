<?php
declare(strict_types=1);

namespace App\Models;

final class ConfiguracionDAO extends BaseDAO
{
    protected string $table = 'configuraciones';
    protected string $primaryKey = 'id_config';

    public function get(string $clave, mixed $default = null): mixed
    {
        $row = $this->query(
            'SELECT valor, tipo FROM configuraciones WHERE clave = :clave LIMIT 1',
            ['clave' => $clave]
        )->fetch();
        if (!$row) {
            return $default;
        }
        return match ($row['tipo']) {
            'int'  => (int) $row['valor'],
            'bool' => (bool) (int) $row['valor'],
            'json' => json_decode((string) $row['valor'], true),
            default => $row['valor'],
        };
    }

    public function set(string $clave, mixed $valor, string $tipo = 'string'): void
    {
        $store = match ($tipo) {
            'bool' => $valor ? '1' : '0',
            'json' => json_encode($valor, JSON_UNESCAPED_UNICODE),
            default => (string) $valor,
        };

        $existing = $this->query(
            'SELECT id_config FROM configuraciones WHERE clave = :clave LIMIT 1',
            ['clave' => $clave]
        )->fetch();

        if ($existing) {
            $this->updateById((int) $existing['id_config'], ['valor' => $store, 'tipo' => $tipo]);
        } else {
            $this->insert(['clave' => $clave, 'valor' => $store, 'tipo' => $tipo]);
        }
    }

    public function allAsMap(): array
    {
        $rows = $this->query('SELECT clave, valor, tipo FROM configuraciones')->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['clave']] = match ($r['tipo']) {
                'int'  => (int) $r['valor'],
                'bool' => (bool) (int) $r['valor'],
                'json' => json_decode((string) $r['valor'], true),
                default => $r['valor'],
            };
        }
        return $map;
    }
}
