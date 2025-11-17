<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected Database $database;
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $useSoftDeletes = false;
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function all(array $columns = ['*']): array
    {
        $sql = sprintf(
            "SELECT %s FROM `%s` %s",
            implode(', ', $columns),
            $this->table,
            $this->useSoftDeletes ? "WHERE deleted_at IS NULL" : ""
        );
        
        return $this->database->fetchAll($sql);
    }

    public function find($id, array $columns = ['*'])
    {
        $sql = sprintf(
            "SELECT %s FROM `%s` WHERE `%s` = ? %s LIMIT 1",
            implode(', ', $columns),
            $this->table,
            $this->primaryKey,
            $this->useSoftDeletes ? "AND deleted_at IS NULL" : ""
        );
        
        return $this->database->fetchOne($sql, [$id]);
    }

    public function where(array $conditions, array $columns = ['*']): array
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $whereString = implode(' AND ', $whereClauses);
        
        if ($this->useSoftDeletes) {
            $whereString .= " AND deleted_at IS NULL";
        }
        
        $sql = sprintf(
            "SELECT %s FROM `%s` WHERE %s",
            implode(', ', $columns),
            $this->table,
            $whereString
        );
        
        return $this->database->fetchAll($sql, $params);
    }

    public function first(array $conditions, array $columns = ['*'])
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $whereString = implode(' AND ', $whereClauses);
        
        if ($this->useSoftDeletes) {
            $whereString .= " AND deleted_at IS NULL";
        }
        
        $sql = sprintf(
            "SELECT %s FROM `%s` WHERE %s LIMIT 1",
            implode(', ', $columns),
            $this->table,
            $whereString
        );
        
        return $this->database->fetchOne($sql, $params);
    }

    public function create(array $data): string
    {
        $data = $this->filterFillable($data);
        $data = $this->addTimestamps($data, true);
        
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
        $data = $this->filterFillable($data);
        $data = $this->addTimestamps($data, false);
        
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
        if ($this->useSoftDeletes) {
            $sql = sprintf(
                "UPDATE `%s` SET deleted_at = CURRENT_TIMESTAMP WHERE `%s` = ?",
                $this->table,
                $this->primaryKey
            );
        } else {
            $sql = sprintf(
                "DELETE FROM `%s` WHERE `%s` = ?",
                $this->table,
                $this->primaryKey
            );
        }
        
        return $this->database->execute($sql, [$id]);
    }

    public function paginate(int $page = 1, int $perPage = 20, array $conditions = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        if ($this->useSoftDeletes) {
            $whereClauses[] = "deleted_at IS NULL";
        }
        
        $whereString = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        
        $countSql = sprintf("SELECT COUNT(*) as total FROM `%s` %s", $this->table, $whereString);
        $totalResult = $this->database->fetchOne($countSql, $params);
        $total = (int)$totalResult['total'];
        
        $dataSql = sprintf(
            "SELECT * FROM `%s` %s LIMIT ? OFFSET ?",
            $this->table,
            $whereString
        );
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $data = $this->database->fetchAll($dataSql, $params);
        
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

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function addTimestamps(array $data, bool $isNew): array
    {
        if ($isNew) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $data;
    }

    protected function hideFields(array $record): array
    {
        if (empty($this->hidden)) {
            return $record;
        }
        
        return array_diff_key($record, array_flip($this->hidden));
    }
}
