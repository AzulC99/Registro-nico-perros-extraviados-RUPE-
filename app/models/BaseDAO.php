<?php
/**
 * RUPE CDMX — Base DAO (parámetros con nombre :param)
 * app/models/BaseDAO.php
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOStatement;

abstract class BaseDAO
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        if ($params === []) {
            $stmt->execute();
            return $stmt;
        }

        $hasNumeric = false;
        foreach (array_keys($params) as $k) {
            if (is_int($k)) {
                $hasNumeric = true;
                break;
            }
        }

        if ($hasNumeric) {
            $stmt->execute(array_values($params));
            return $stmt;
        }

        foreach ($params as $key => $value) {
            $name = is_string($key) && str_starts_with($key, ':') ? $key : ':' . (string) $key;
            if (is_int($value)) {
                $stmt->bindValue($name, $value, PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $stmt->bindValue($name, $value ? 1 : 0, PDO::PARAM_INT);
            } elseif ($value === null) {
                $stmt->bindValue($name, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($name, (string) $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt;
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $row = $this->query($sql, ['id' => $id])->fetch();
        return $row ?: null;
    }

    public function all(?string $orderBy = null, int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy && preg_match('/^[a-zA-Z0-9_,\s]+$/', $orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        }
        return $this->query($sql)->fetchAll();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->query($sql, ['id' => $id])->rowCount() > 0;
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where}";
        return (int) $this->query($sql, $params)->fetchColumn();
    }

    protected function insert(array $data): int
    {
        $cols = array_keys($data);
        $placeholders = [];
        $params = [];
        foreach ($cols as $col) {
            $placeholders[] = ':' . $col;
            $params[$col] = $data[$col];
        }
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $cols),
            implode(', ', $placeholders)
        );
        $this->query($sql, $params);
        return (int) $this->db->lastInsertId();
    }

    protected function updateById(int $id, array $data): bool
    {
        if ($data === []) {
            return false;
        }
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $paramName = $col === 'id' ? 'set_id' : $col;
            $sets[] = "{$col} = :{$paramName}";
            $params[$paramName] = $val;
        }
        $params['pk_id'] = $id;
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :pk_id',
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );
        return $this->query($sql, $params)->rowCount() >= 0;
    }
}
