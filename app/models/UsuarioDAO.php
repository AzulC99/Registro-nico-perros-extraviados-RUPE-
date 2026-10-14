<?php
declare(strict_types=1);

namespace App\Models;

final class UsuarioDAO extends BaseDAO
{
    protected string $table = 'usuarios';
    protected string $primaryKey = 'id_usuario';

    public function findByEmail(string $email): ?array
    {
        $row = $this->query(
            'SELECT * FROM usuarios WHERE correo = :correo LIMIT 1',
            ['correo' => strtolower(trim($email))]
        )->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $payload = [
            'nombre'            => $data['nombre'],
            'correo'            => strtolower(trim($data['correo'])),
            'telefono'          => $data['telefono'] ?? null,
            'password_hash'     => $data['password_hash'],
            'rol'               => $data['rol'] ?? ROLE_TUTOR,
            'activo'            => $data['activo'] ?? 1,
            'acepta_privacidad' => $data['acepta_privacidad'] ?? 0,
        ];
        return $this->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['nombre', 'telefono', 'correo', 'password_hash', 'activo', 'acepta_privacidad', 'ultimo_acceso', 'rol'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        if (isset($filtered['correo'])) {
            $filtered['correo'] = strtolower(trim($filtered['correo']));
        }
        return $this->updateById($id, $filtered);
    }

    public function listByRole(?string $rol = null, int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, (int) $limit);
        $offset = max(0, (int) $offset);
        if ($rol) {
            return $this->query(
                'SELECT id_usuario, nombre, correo, telefono, rol, activo, ultimo_acceso, created_at
                 FROM usuarios WHERE rol = :rol ORDER BY nombre LIMIT ' . $limit . ' OFFSET ' . $offset,
            ['rol' => $rol]
            )->fetchAll();
        }
        return $this->query(
            'SELECT id_usuario, nombre, correo, telefono, rol, activo, ultimo_acceso, created_at
             FROM usuarios ORDER BY nombre LIMIT ' . $limit . ' OFFSET ' . $offset
        )->fetchAll();
    }

    public function touchLastAccess(int $id): void
    {
        $this->updateById($id, ['ultimo_acceso' => date('Y-m-d H:i:s')]);
    }

    public function toPublic(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }
}
