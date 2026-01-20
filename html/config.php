<?php
// Check if we are on Render by looking for the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    // We are on Render - we will parse the connection string automatically in db.php
    define('DB_IS_REMOTE', true);
} else {
    // Local development settings (Keep these for your local XAMPP/WAMP if needed)
    define('DB_IS_REMOTE', false);
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'guszab');
    define('DB_USER', 'root');
    define('DB_PASS', 'labas');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.save_path', sys_get_temp_dir());
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}