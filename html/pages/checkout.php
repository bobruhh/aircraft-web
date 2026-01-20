<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_role('VADYBININKAS');

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    header('Location: /pages/cart.php'); exit;
}

$ids = array_map('intval', array_keys($cart));
$id_list = implode(',', $ids);

$pdo = db();
$err = '';

// Get offer details for calculation
$sql = "SELECT o.id AS offer_id, o.price, p.id AS product_id, s.id AS supplier_id 
        FROM pasiulymai o 
        JOIN products p ON p.id = o.product_id 
        JOIN suppliers s ON s.id = o.supplier_id 
        WHERE o.id IN ($id_list)";
$pasiulymai = $pdo->query($sql)->fetchAll();

$lines = []; 
$total = 0.0;
foreach ($pasiulymai as $o) {
    $qty = (int)($cart[$o['offer_id']]['qty'] ?? 0);
    if ($qty <= 0) continue;
    $sum = $qty * (float)$o['price'];
    $total += $sum;
    $lines[] = array_merge($o, ['qty' => $qty, 'sum' => $sum]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $pdo->beginTransaction();
        
        // Create Order (Postgres uses RETURNING id instead of mysqli_insert_id)
        $st = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'PATEIKTAS') RETURNING id");
        $st->execute([current_user()['id'], $total]);
        $order_id = $st->fetchColumn();

        // Create Items
        $stItem = $pdo->prepare("INSERT INTO order_items (order_id, offer_id, product_id, supplier_id, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($lines as $ln) {
            $stItem->execute([$order_id, $ln['offer_id'], $ln['product_id'], $ln['supplier_id'], $ln['qty'], $ln['price'], $ln['sum']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];
        header('Location: /pages/checkout.php?ok=' . $order_id);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $err = "Klaida: " . $e->getMessage();
    }
}

include __DIR__.'/../header.php';
?>
<h2>Patvirtinti užsakymą</h2>

<?php if (!empty($_GET['ok'])): ?>
  <p style="color:green">Užsakymas #<?= (int)$_GET['ok'] ?> sėkmingai sukurtas.</p>
  <p><a href="/index.php">↩ Grįžti į pradžią</a></p>
  <?php include __DIR__.'/../footer.php'; exit; ?>
<?php endif; ?>

<?php if ($err): ?>
  <p style="color:#b00"><?= htmlspecialchars($err) ?></p>
<?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
  <tr><th>Pasiūlymas</th><th>Kiekis</th><th>Kaina</th><th>Suma</th></tr>
  <?php foreach ($lines as $ln): ?>
    <tr>
      <td>#<?= (int)$ln['offer_id'] ?></td>
      <td style="text-align:center"><?= (int)$ln['qty'] ?></td>
      <td style="text-align:right"><?= number_format($ln['price'],2,',',' ') ?> €</td>
      <td style="text-align:right"><?= number_format($ln['sum'],2,',',' ') ?> €</td>
    </tr>
  <?php endforeach; ?>
  <tr>
    <th colspan="3" style="text-align:right">Iš viso:</th>
    <th style="text-align:right"><?= number_format($total,2,',',' ') ?> €</th>
  </tr>
</table>

<form method="post" style="margin-top:20px;">
  <button type="submit" name="confirm" style="padding:10px 20px; background:green; color:white; border:0; cursor:pointer;">
    Patvirtinti ir užsakyti
  </button>
  <a href="/pages/cart.php" style="margin-left:15px;">Atgal į krepšelį</a>
</form>

<?php include __DIR__.'/../footer.php'; ?>