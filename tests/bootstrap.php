<?php
/**
 * PHPUnit Bootstrap File
 * 
 * Sets up the testing environment for the 8800SX application
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Set testing environment
$_ENV['APP_ENV'] = 'testing';

// Define test constants
define('TEST_DATA_DIR', __DIR__ . '/data');
define('TEST_UPLOADS_DIR', __DIR__ . '/data/uploads');

// Create test directories if they don't exist
if (!is_dir(TEST_DATA_DIR)) {
    mkdir(TEST_DATA_DIR, 0755, true);
}

if (!is_dir(TEST_UPLOADS_DIR)) {
    mkdir(TEST_UPLOADS_DIR, 0755, true);
}

// Mock functions for testing environment (only if not already defined)
if (!function_exists('translate')) {
    function translate($key, $return = false, ...$args) {
        $message = sprintf($key, ...$args);
        if ($return) {
            return $message;
        }
        echo $message;
    }
}

// Helper function to create test database connection
function createTestDbConnection() {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $database = $_ENV['DB_DATABASE'] ?? 'viavi_test';
    $username = $_ENV['DB_USERNAME'] ?? 'test';
    $password = $_ENV['DB_PASSWORD'] ?? 'test';
    
    try {
        $connection = new mysqli($host, $username, $password, $database);
        if ($connection->connect_error) {
            throw new Exception("Connection failed: " . $connection->connect_error);
        }
        return $connection;
    } catch (Exception $e) {
        // Return null if test database is not available
        return null;
    }
}

// Clean up function for tests
function cleanupTestData() {
    $files = glob(TEST_UPLOADS_DIR . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

// Register cleanup function
register_shutdown_function('cleanupTestData');