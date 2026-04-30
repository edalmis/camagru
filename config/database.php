<?php
/**
 * Database Configuration
 * 
 * Database connection settings for the Camagru application
 */

define('DB_HOST', getenv('DB_HOST') ?: 'camagru_db');
define('DB_USER', getenv('DB_USER') ?: 'camagru');
define('DB_PASS', getenv('DB_PASS') ?: 'camagru');
define('DB_NAME', getenv('DB_NAME') ?: 'camagru');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

/**
 * Get database connection
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die('Database Connection Error: ' . $e->getMessage());
    }
}
