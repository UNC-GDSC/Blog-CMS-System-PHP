<?php

namespace App\Models;

use App\Helpers\Logger;

/**
 * User repository for user authentication and management
 */
class UserRepository extends BaseRepository
{
    protected $table = 'users';

    /**
     * Find user by username
     *
     * @param string $username
     * @return array|null
     */
    public function findByUsername($username)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            Logger::error("Error finding user by username: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            Logger::error("Error finding user by email: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create new user with hashed password
     *
     * @param array $data User data (username, email, password)
     * @return int User ID
     */
    public function createUser(array $data)
    {
        try {
            // Hash password before storing
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            return $this->create($data);
        } catch (\PDOException $e) {
            Logger::error("Error creating user: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify user password
     *
     * @param string $username
     * @param string $password
     * @return array|null User data if valid, null otherwise
     */
    public function verifyCredentials($username, $password)
    {
        try {
            $user = $this->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                Logger::info("User logged in successfully", ['username' => $username]);
                return $user;
            }

            Logger::warning("Failed login attempt", ['username' => $username]);
            return null;
        } catch (\PDOException $e) {
            Logger::error("Error verifying credentials: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user password
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($userId, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            return $this->update($userId, ['password' => $hashedPassword]);
        } catch (\PDOException $e) {
            Logger::error("Error updating password: " . $e->getMessage());
            throw $e;
        }
    }
}
