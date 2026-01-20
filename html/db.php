<?php
require_once __DIR__.'/config.php';

/**
 * Pagrindinė duomenų bazės jungtis.
 * Automatiškai nustato ar naudoti PostgreSQL (Render), ar MySQL (Local).
 */
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        // Tikriname, ar yra Render aplinkos kintamasis
        $dbUrl = getenv('DATABASE_URL');
        
        if ($dbUrl) {
            // --- KONFIGŪRACIJA RENDER (PostgreSQL) ---
            // Išskaidome URL: postgresql://user:pass@host:port/dbname
            $p = parse_url($dbUrl);
            
            $host = $p['host'];
            $port = $p['port'] ?? 5432;
            $db   = ltrim($p['path'], '/');
            $user = $p['user'];
            $pass = $p['pass'];
            
            // PostgreSQL DSN formatas
            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            
            try {
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("Klaida jungiantis prie PostgreSQL: " . $e->getMessage());
            }
            
        } else {
            // --- KONFIGŪRACIJA LOKALIAI (MySQL) ---
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
            
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("Klaida jungiantis prie MySQL: " . $e->getMessage());
            }
        }
    }
    return $pdo;
}
