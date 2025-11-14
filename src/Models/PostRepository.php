<?php

namespace App\Models;

use App\Helpers\Logger;

/**
 * Post repository for blog post operations
 */
class PostRepository extends BaseRepository
{
    protected $table = 'posts';

    /**
     * Find all posts ordered by creation date (newest first) with pagination
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAllOrdered($limit = null, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";

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
            Logger::error("Error finding ordered posts: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search posts by title or content
     *
     * @param string $query Search query
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function search($query, $limit = null, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE title LIKE :query OR content LIKE :query
                    ORDER BY created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($sql);
            $searchTerm = "%{$query}%";
            $stmt->bindValue(':query', $searchTerm, \PDO::PARAM_STR);

            if ($limit !== null) {
                $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            Logger::error("Error searching posts: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Count search results
     *
     * @param string $query Search query
     * @return int
     */
    public function countSearch($query)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}
                    WHERE title LIKE :query OR content LIKE :query";

            $stmt = $this->db->prepare($sql);
            $searchTerm = "%{$query}%";
            $stmt->bindValue(':query', $searchTerm, \PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch();
            return (int)$result['count'];
        } catch (\PDOException $e) {
            Logger::error("Error counting search results: " . $e->getMessage());
            throw $e;
        }
    }
}
