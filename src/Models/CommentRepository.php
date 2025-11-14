<?php

namespace App\Models;

use App\Helpers\Logger;

/**
 * Comment repository for post comments
 */
class CommentRepository extends BaseRepository
{
    protected $table = 'comments';

    /**
     * Get comments for a post
     *
     * @param int $postId
     * @param string $status approved|pending|spam
     * @return array
     */
    public function getCommentsByPost($postId, $status = 'approved')
    {
        try {
            $sql = "SELECT c.*, u.username, u.avatar
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.user_id = u.id
                    WHERE c.post_id = :post_id AND c.status = :status
                    ORDER BY c.created_at ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'post_id' => $postId,
                'status' => $status
            ]);

            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            Logger::error("Error getting comments: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Count comments for a post
     *
     * @param int $postId
     * @param string $status
     * @return int
     */
    public function countCommentsByPost($postId, $status = 'approved')
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table}
                                        WHERE post_id = :post_id AND status = :status");
            $stmt->execute([
                'post_id' => $postId,
                'status' => $status
            ]);

            $result = $stmt->fetch();
            return (int)$result['count'];
        } catch (\PDOException $e) {
            Logger::error("Error counting comments: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get pending comments (for moderation)
     *
     * @param int $limit
     * @return array
     */
    public function getPendingComments($limit = 50)
    {
        try {
            $sql = "SELECT c.*, p.title as post_title, u.username
                    FROM {$this->table} c
                    LEFT JOIN posts p ON c.post_id = p.id
                    LEFT JOIN users u ON c.user_id = u.id
                    WHERE c.status = 'pending'
                    ORDER BY c.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            Logger::error("Error getting pending comments: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Approve comment
     *
     * @param int $commentId
     * @return bool
     */
    public function approve($commentId)
    {
        try {
            return $this->update($commentId, ['status' => 'approved']);
        } catch (\PDOException $e) {
            Logger::error("Error approving comment: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark comment as spam
     *
     * @param int $commentId
     * @return bool
     */
    public function markAsSpam($commentId)
    {
        try {
            return $this->update($commentId, ['status' => 'spam']);
        } catch (\PDOException $e) {
            Logger::error("Error marking comment as spam: " . $e->getMessage());
            throw $e;
        }
    }
}
