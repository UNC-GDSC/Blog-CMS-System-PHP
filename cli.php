#!/usr/bin/env php
<?php

/**
 * CLI Tool for Blog CMS
 * Administrative commands for maintenance and management
 */

require_once __DIR__ . '/bootstrap.php';

use App\Helpers\Cache;
use App\Helpers\Logger;
use App\Middleware\RateLimiter;
use App\Models\UserRepository;

// Ensure running from CLI
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Parse command line arguments
$command = $argv[1] ?? 'help';
$args = array_slice($argv, 2);

// Execute command
switch ($command) {
    case 'cache:clear':
        echo "Clearing cache...\n";
        $count = Cache::clear();
        echo "✓ Cache cleared! ($count files deleted)\n";
        break;

    case 'cache:clean':
        echo "Cleaning expired cache entries...\n";
        $count = Cache::cleanExpired();
        echo "✓ Expired cache cleaned! ($count files deleted)\n";
        break;

    case 'rate-limit:clear':
        echo "Clearing rate limit data...\n";
        $count = RateLimiter::cleanOld();
        echo "✓ Rate limit data cleared! ($count files deleted)\n";
        break;

    case 'user:create':
        if (count($args) < 3) {
            echo "Usage: php cli.php user:create <username> <email> <password> [role]\n";
            exit(1);
        }

        $username = $args[0];
        $email = $args[1];
        $password = $args[2];
        $role = $args[3] ?? 'subscriber';

        try {
            $userRepo = new UserRepository();

            $userId = $userRepo->createUser([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);

            echo "✓ User created successfully! ID: $userId\n";
            echo "  Username: $username\n";
            echo "  Email: $email\n";
            echo "  Role: $role\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case 'user:list':
        try {
            $userRepo = new UserRepository();
            $users = $userRepo->findAll();

            echo "Total users: " . count($users) . "\n\n";
            echo str_pad("ID", 5) . str_pad("Username", 20) . str_pad("Email", 30) . str_pad("Role", 15) . "Created\n";
            echo str_repeat("-", 90) . "\n";

            foreach ($users as $user) {
                echo str_pad($user['id'], 5);
                echo str_pad($user['username'], 20);
                echo str_pad($user['email'], 30);
                echo str_pad($user['role'] ?? 'subscriber', 15);
                echo date('Y-m-d', strtotime($user['created_at'])) . "\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case 'logs:clear':
        $logPath = App\Helpers\Env::get('LOG_PATH', 'logs/app.log');

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
            echo "✓ Logs cleared!\n";
        } else {
            echo "✗ Log file not found: $logPath\n";
        }
        break;

    case 'logs:tail':
        $lines = isset($args[0]) && is_numeric($args[0]) ? (int)$args[0] : 20;
        $logPath = App\Helpers\Env::get('LOG_PATH', 'logs/app.log');

        if (file_exists($logPath)) {
            echo "Last $lines lines of log file:\n";
            echo str_repeat("-", 80) . "\n";
            passthru("tail -n $lines $logPath");
        } else {
            echo "✗ Log file not found: $logPath\n";
        }
        break;

    case 'db:status':
        try {
            $db = App\Config\Database::getInstance()->getConnection();
            echo "✓ Database connection successful!\n\n";

            // Get table info
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            echo "Tables (" . count($tables) . "):\n";
            foreach ($tables as $table) {
                $stmt = $db->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $stmt->fetch()['count'];
                echo "  - $table: $count rows\n";
            }
        } catch (Exception $e) {
            echo "✗ Database connection failed: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case 'version':
        echo "Blog CMS System v2.0.0\n";
        echo "PHP Version: " . PHP_VERSION . "\n";
        break;

    case 'help':
    default:
        echo "Blog CMS CLI Tool\n\n";
        echo "Available commands:\n\n";
        echo "  cache:clear          Clear all cached data\n";
        echo "  cache:clean          Clean expired cache entries\n";
        echo "  rate-limit:clear     Clear old rate limit data\n";
        echo "  user:create          Create a new user\n";
        echo "  user:list            List all users\n";
        echo "  logs:clear           Clear log file\n";
        echo "  logs:tail [n]        Show last n lines of logs (default: 20)\n";
        echo "  db:status            Check database connection and table status\n";
        echo "  version              Show version information\n";
        echo "  help                 Show this help message\n\n";
        echo "Examples:\n";
        echo "  php cli.php cache:clear\n";
        echo "  php cli.php user:create john john@example.com password123 admin\n";
        echo "  php cli.php logs:tail 50\n\n";
        break;
}

echo "\n";
