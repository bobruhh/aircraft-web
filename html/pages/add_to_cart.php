<?php
// pages/add_to_cart.php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_role('VADYBININKAS');

$offer_id = (int)($_GET['offer_id'] ?? 0);
$qty      = max(1, (int)($_GET['qty'] ?? 1));

$stmt = db()->prepare("SELECT o.id, o.price, o.stock, o.delivery_days,
                              p.id AS product_id, p.name, p.manufacturer, p.model,
                              s.id AS supplier_id, s.name AS supplier
                       FROM pasiulymai o
                       JOIN products p ON p.id = o.product_id
                       JOIN suppliers s ON s.id = o.supplier_id
                       WHERE o.id = ?");
$stmt->execute([$offer_id]);
$offer = $stmt->fetch();

if (!$offer) { header('Location: /pages/catalog.php?err=norow'); exit; }

// krepselis laikomas sesijoje
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['cart'][$offer_id])) $_SESSION['cart'][$offer_id] = ['qty'=>0];
$_SESSION['cart'][$offer_id]['qty'] += $qty;

header('Location: /pages/cart.php');

