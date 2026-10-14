<?php
declare(strict_types=1);

namespace App\Models;

final class AyudaDAO extends BaseDAO
{
    protected string $table = 'ayuda_faqs';
    protected string $primaryKey = 'id_faq';

    public function listVisible(string $rol = 'todos'): array
    {
        return $this->query(
            'SELECT * FROM ayuda_faqs
             WHERE activo = 1 AND (rol_visible = \'todos\' OR rol_visible = :rol)
             ORDER BY categoria, orden, id_faq',
            ['rol' => $rol]
        )->fetchAll();
    }

    public function create(array $data): int
    {
        return $this->insert([
            'categoria'   => $data['categoria'] ?? 'general',
            'pregunta'    => trim($data['pregunta']),
            'respuesta'   => $data['respuesta'],
            'orden'       => (int) ($data['orden'] ?? 0),
            'activo'      => $data['activo'] ?? 1,
            'rol_visible' => $data['rol_visible'] ?? 'todos',
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['categoria', 'pregunta', 'respuesta', 'orden', 'activo', 'rol_visible'];
        return $this->updateById($id, array_intersect_key($data, array_flip($allowed)));
    }
}
