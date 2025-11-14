<?php

namespace App\Config;

use App\Helpers\Env;
use App\Helpers\Logger;

/**
 * Database connection manager (Singleton pattern)
 * Handles PDO connection with error handling
 */
class Database
{
    private static $instance = null;
    private $pdo = null;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        try {
            $host = Env::get('DB_HOST', 'localhost');
            $port = Env::get('DB_PORT', '3306');
            $dbname = Env::get('DB_NAME', 'blog_cms');
            $username = Env::get('DB_USER', 'root');
            $password = Env::get('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new \PDO($dsn, $username, $password, $options);

            Logger::info('Database connection established');
        } catch (\PDOException $e) {
            Logger::critical('Database connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get database instance (Singleton)
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     *
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize singleton');
    }
}
