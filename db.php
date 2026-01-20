<?php
require_once __DIR__.'/config.php';

/**
 * Pagrindinė duomenų bazės jungtis.
 * Ši funkcija automatiškai nustato ryšio tipą pagal aplinką.
 */
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        // Tikriname, ar egzistuoja Render DATABASE_URL kintamasis
        $dbUrl = getenv('DATABASE_URL');
        
        if ($dbUrl) {
            // --- KONFIGŪRACIJA RENDER PLATFORMAI (PostgreSQL) ---
            // Išskaidome URL (pvz. postgresql://user:pass@host:port/dbname)
            $p = parse_url($dbUrl);
            
            $host = $p['host'];
            $port = $p['port'] ?? 5432;
            $db   = ltrim($p['path'], '/');
            $user = $p['user'];
            $pass = $p['pass'];
            
            // Labai svarbu: PDO reikalauja "pgsql:" prefikso, o ne "postgresql://"
            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            
            try {
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                // Jei nepavyksta prisijungti, išvedame klaidą
                die("Nepavyko prisijungti prie Render DB: " . $e->getMessage());
            }
            
        } else {
            // --- KONFIGŪRACIJA LOKALIAM KOMPIUTERIUI (MySQL) ---
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
            
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Nepavyko prisijungti prie vietinės DB: " . $e->getMessage());
            }
        }
    }
    return $pdo;
}
