<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected Database $database;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function findById($id, array $columns = ['*'])
    {
        $sql = sprintf(
            "SELECT %s FROM `%s` WHERE `%s` = ? LIMIT 1",
            implode(', ', $columns),
            $this->table,
            $this->primaryKey
        );
        
        return $this->database->fetchOne($sql, [$id]);
    }

    public function findAll(array $conditions = [], array $orderBy = [], int $limit = 0): array
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $whereClauses[] = "`{$key}` IN ({$placeholders})";
                $params = array_merge($params, $value);
            } else {
                $whereClauses[] = "`{$key}` = ?";
                $params[] = $value;
            }
        }
        
        $sql = "SELECT * FROM `{$this->table}`";
        
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $column => $direction) {
                $orderClauses[] = "`{$column}` {$direction}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->database->fetchAll($sql, $params);
    }

    public function create(array $data): string
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $this->table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );
        
        $this->database->query($sql, array_values($data));
        return $this->database->lastInsertId();
    }

    public function update($id, array $data): bool
    {
        $setClauses = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = ?",
            $this->table,
            implode(', ', $setClauses),
            $this->primaryKey
        );
        
        return $this->database->execute($sql, $params);
    }

    public function delete($id): bool
    {
        $sql = sprintf(
            "DELETE FROM `%s` WHERE `%s` = ?",
            $this->table,
            $this->primaryKey
        );
        
        return $this->database->execute($sql, [$id]);
    }

    public function count(array $conditions = []): int
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $result = $this->database->fetchOne($sql, $params);
        return (int)$result['total'];
    }

    public function paginate(int $page = 1, int $perPage = 20, array $conditions = [], array $orderBy = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $whereString = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        
        $countSql = sprintf("SELECT COUNT(*) as total FROM `%s` %s", $this->table, $whereString);
        $totalResult = $this->database->fetchOne($countSql, $params);
        $total = (int)$totalResult['total'];
        
        $dataSql = "SELECT * FROM `{$this->table}` {$whereString}";
        
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $column => $direction) {
                $orderClauses[] = "`{$column}` {$direction}";
            }
            $dataSql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        $dataSql .= " LIMIT ? OFFSET ?";
        
        $dataParams = array_merge($params, [$perPage, $offset]);
        $data = $this->database->fetchAll($dataSql, $dataParams);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ],
        ];
    }

    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }

    public function search(string $column, string $term, array $additionalConditions = [], int $limit = 20): array
    {
        $whereClauses = ["`{$column}` LIKE ?"];
        $params = ['%' . $term . '%'];
        
        foreach ($additionalConditions as $key => $value) {
            $whereClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            "SELECT * FROM `%s` WHERE %s LIMIT ?",
            $this->table,
            implode(' AND ', $whereClauses)
        );
        
        $params[] = $limit;
        
        return $this->database->fetchAll($sql, $params);
    }
}
