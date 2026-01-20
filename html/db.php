<?php
require_once __DIR__.'/config.php';

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        if (getenv('DATABASE_URL')) {
            // Render PostgreSQL Connection
            $dsn = getenv('DATABASE_URL');
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Render requires SSL for external connections
                PDO::NULL_EMPTY_STRING       => true,
            ];
            // Note: PostgreSQL DSNs can be the full URL starting with postgresql://
            $pdo = new PDO($dsn, null, null, $opt);
        } else {
            // Local MySQL Connection
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opt);
        }
    }
    return $pdo;
}