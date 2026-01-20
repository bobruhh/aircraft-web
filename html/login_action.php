<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /login.php'); exit;
}

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if ($email === '' || $pass === '') {
  header('Location: /login.php?err=1'); exit;
}

// PostgreSQL works perfectly with this standard PDO prepared statement
$stmt = db()->prepare('SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// In Postgres, is_active (SMALLINT) 1 is truthy, so this logic stays the same
if (!$user || !$user['is_active'] || !password_verify($pass, $user['password_hash'])) {
  header('Location: /login.php?err=1'); exit;
}

$_SESSION['user'] = [
  'id' => (int)$user['id'],
  'name' => $user['name'],
  'email' => $user['email'],
  'role' => (string)$user['role'], // Ensure role is treated as string (it's an ENUM in DB)
];

header('Location: /index.php');