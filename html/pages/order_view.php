<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_role('VADYBININKAS');

$pdo = db();
$user = current_user();

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) { header('Location: /pages/my_orders.php'); exit; }

$head = $pdo->prepare("SELECT id, created_at, status, total FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$head->execute([$order_id, $user['id']]);
$order = $head->fetch();
if (!$order) { header('Location: /pages/my_orders.php'); exit; }

$items = $pdo->prepare("
    SELECT oi.quantity, oi.unit_price, oi.line_total,
           p.name AS product, p.manufacturer, p.model,
           s.name AS supplier
    FROM order_items oi
    JOIN products p  ON p.id  = oi.product_id
    JOIN suppliers s ON s.id = oi.supplier_id
    WHERE oi.order_id = ?
    ORDER BY oi.id
");
$items->execute([$order_id]);
$lines = $items->fetchAll();

include __DIR__.'/../header.php';
?>
<h2>Užsakymas #<?= (int)$order['id'] ?></h2>

<ul>
  <li>Sukurta: <b><?= htmlspecialchars($order['created_at']) ?></b></li>
  <li>Būsena: <b><?= htmlspecialchars($order['status']) ?></b></li>
  <li>Suma: <b><?= number_format((float)$order['total'], 2, ',', ' ') ?> €</b></li>
</ul>

<?php if (!$lines): ?>
  <p>Šiame užsakyme nėra eilučių.</p>
<?php else: ?>
  <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
    <tr style="background:#003366;color:#fff;">
      <th>Prekė</th>
      <th>Tiekėjas</th>
      <th>Kiekis</th>
      <th>Kaina (€)</th>
      <th>Suma (€)</th>
    </tr>
    <?php foreach ($lines as $ln): ?>
      <tr>
        <td><?= htmlspecialchars($ln['product'].' / '.$ln['manufacturer'].' '.$ln['model']) ?></td>
        <td><?= htmlspecialchars($ln['supplier']) ?></td>
        <td style="text-align:center;"><?= (int)$ln['quantity'] ?></td>
        <td style="text-align:right;"><?= number_format((float)$ln['unit_price'], 2, ',', ' ') ?></td>
        <td style="text-align:right;"><?= number_format((float)$ln['line_total'], 2, ',', ' ') ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<p style="margin-top:12px;">
  <a href="/pages/my_orders.php">← Atgal į „Mano užsakymai“</a>
</p>
<?php include __DIR__.'/../footer.php'; ?>

