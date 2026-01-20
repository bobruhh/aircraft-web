<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_role('VADYBININKAS');

$cart = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update']) && is_array($_POST['q'] ?? null)) {
        foreach ($_POST['q'] as $oid => $q) {
            $oid = (int)$oid; $q = max(0, (int)$q);
            if ($q === 0) unset($cart[$oid]);
            else $cart[$oid]['qty'] = $q;
        }
        $_SESSION['cart'] = $cart;
    }
    if (isset($_POST['clear'])) { $_SESSION['cart'] = []; $cart = []; }
}
$rows = []; $total = 0.0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $sql = "SELECT o.id as offer_id, o.price, o.stock, o.delivery_days,
                   p.name as product, p.manufacturer, p.model,
                   s.name as supplier
            FROM pasiulymai o
            JOIN products p ON p.id = o.product_id
            JOIN suppliers s ON s.id = o.supplier_id
            WHERE o.id IN ($ids)";
    $rows = db()->query($sql)->fetchAll();
}

include __DIR__.'/../header.php';
?>
<h2>Krepšelis</h2>

<?php if (empty($rows)): ?>
  <p>Krepšelis tuščias.</p>
  <p><a href="/pages/catalog.php">← Grįžti į katalogą</a></p>
<?php else: ?>
<form method="post">
<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
  <tr>
    <th>Prekė</th><th>Tiekėjas</th><th>Kaina</th><th>Kiekis</th><th>Suma</th>
  </tr>
  <?php foreach ($rows as $r):
        $q = (int)($cart[$r['offer_id']]['qty'] ?? 0);
        $line = $q * (float)$r['price'];
        $total += $line;
  ?>
  <tr>
    <td><?= htmlspecialchars($r['product'].' / '.$r['manufacturer'].' '.$r['model']) ?></td>
    <td><?= htmlspecialchars($r['supplier']) ?></td>
    <td style="text-align:right"><?= number_format($r['price'],2,',',' ') ?> €</td>
    <td style="text-align:center">
      <input type="number" name="q[<?= (int)$r['offer_id'] ?>]" value="<?= $q ?>" min="0" style="width:70px">
    </td>
    <td style="text-align:right"><?= number_format($line,2,',',' ') ?> €</td>
  </tr>
  <?php endforeach; ?>
  <tr>
    <th colspan="4" style="text-align:right">Viso:</th>
    <th style="text-align:right"><?= number_format($total,2,',',' ') ?> €</th>
  </tr>
</table>

<div style="margin-top:10px; display:flex; gap:8px;">
  <button name="update" value="1">Atnaujinti kiekius</button>
  <button name="clear" value="1" onclick="return confirm('Ištuštinti krepšelį?')">Išvalyti</button>
  <a href="/pages/checkout.php" style="margin-left:auto">Tęsti → apmokėjimas</a>
</div>
</form>
<?php endif; ?>

<?php include __DIR__.'/../footer.php'; ?>

