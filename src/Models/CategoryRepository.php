<?php

namespace App\Models;

use App\Helpers\Logger;

/**
 * Category repository for post organization
 */
class CategoryRepository extends BaseRepository
{
    protected $table = 'categories';

    /**
     * Find category by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function findBySlug($slug)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
            $stmt->execute(['slug' => $slug]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            Logger::error("Error finding category by slug: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get posts by category
     *
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPostsByCategory($categoryId, $limit = null, $offset = 0)
    {
        try {
            $sql = "SELECT p.* FROM posts p
                    WHERE p.category_id = :category_id
                    ORDER BY p.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);

            if ($limit !== null) {
                $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            Logger::error("Error getting posts by category: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Count posts in category
     *
     * @param int $categoryId
     * @return int
     */
    public function countPostsInCategory($categoryId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM posts WHERE category_id = :category_id");
            $stmt->execute(['category_id' => $categoryId]);
            $result = $stmt->fetch();
            return (int)$result['count'];
        } catch (\PDOException $e) {
            Logger::error("Error counting posts in category: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create slug from name
     *
     * @param string $name
     * @return string
     */
    public function createSlug($name)
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
