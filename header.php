<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/auth.php';
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="lt">
<head>
  <meta charset="utf-8">
  <title>Lėktuvo dalių užsakymo sistema</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: system-ui, Segoe UI, Roboto, Arial;
      margin: 0;
      background: #f5f6fa;
    }
    header {
      background: #002f5f;
      color: white;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    header h1 {
      font-size: 18px;
      margin: 0;
    }
    nav a {
      color: white;
      text-decoration: none;
      margin-left: 15px;
      font-weight: 500;
      transition: opacity 0.2s;
    }
    nav a:hover {
      opacity: 0.8;
    }
    .content {
      padding: 20px;
    }
  </style>
</head>
<body>
<header>
  <h1>🛫 Lėktuvo dalių užsakymo sistema</h1>

  <nav>
    <?php if ($user): ?>
      <?php if ($user['role'] === 'VADYBININKAS'): ?>
        <a href="/index.php">Pradžia</a>
        <a href="/pages/catalog.php">Katalogas</a>
        <a href="/pages/cart.php">Krepšelis</a>
        <a href="/pages/my_orders.php">Mano užsakymai</a>

      <?php elseif ($user['role'] === 'TIEKEJAS'): ?>
        <a href="/index.php">Pradžia</a>
        <a href="/pages/offer_new.php">Pridėti prekę</a>
        <a href="/pages/my_offers.php">Mano skelbimai</a>
        <a href="/pages/supplier_orders.php">Gauti užsakymai</a>
        <a href="/pages/catalog.php">Katalogas</a>
      <?php elseif ($user['role'] === 'DIREKTORIUS'): ?>

        <a href="/index.php">Pradžia</a>
        <a href="/pages/stats.php">Statistika</a>
        <a href="/pages/users_admin.php">Naudotojų valdymas</a>
        <a href="/pages/catalog.php">Katalogas</a>
      <?php endif; ?>
      <a href="/logout.php">Atsijungti</a>
    <?php else: ?>
      <a href="/login.php">Prisijungti</a>
      <a href="/pages/register.php" style="margin-left:15px;">Registruotis</a>
    <?php endif; ?>
  </nav>
</header>

<div class="content">

