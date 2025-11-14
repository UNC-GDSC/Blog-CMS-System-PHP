<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\Logger;

/**
 * Base repository class with common CRUD operations
 * All model repositories should extend this class
 */
abstract class BaseRepository
{
    protected $db;
    protected $table;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            Logger::error("Error finding record by ID in {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find all records
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll($limit = null, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM {$this->table}";

            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            Logger::error("Error finding all records in {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Count total records
     *
     * @return int
     */
    public function count()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
            $result = $stmt->fetch();
            return (int)$result['count'];
        } catch (\PDOException $e) {
            Logger::error("Error counting records in {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create new record
     *
     * @param array $data
     * @return int Last insert ID
     */
    public function create(array $data)
    {
        try {
            $fields = array_keys($data);
            $placeholders = array_map(fn($field) => ":{$field}", $fields);

            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);

            Logger::info("Record created in {$this->table}", ['id' => $this->db->lastInsertId()]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            Logger::error("Error creating record in {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update record by ID
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        try {
            $fields = array_keys($data);
            $setParts = array_map(fn($field) => "{$field} = :{$field}", $fields);

            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";

            $data['id'] = $id;
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($data);

            Logger::info("Record updated in {$this->table}", ['id' => $id]);
            return $result;
        } catch (\PDOException $e) {
            Logger::error("Error updating record in {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete record by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);

            Logger::info("Record deleted from {$this->table}", ['id' => $id]);
            return $result;
        } catch (\PDOException $e) {
            Logger::error("Error deleting record from {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute custom query
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    protected function query($sql, array $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            Logger::error("Query error: " . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }
}
