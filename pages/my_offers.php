<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_role('TIEKEJAS');

$user = current_user();
$pdo = db();
$msg = '';

// 1. Identify Supplier ID
$st = $pdo->prepare("SELECT id FROM suppliers WHERE user_id = ? LIMIT 1");
$st->execute([$user['id']]);
$supplier_id = $st->fetchColumn();

// 2. Handle Deletion (Set as Inactive)
if ($supplier_id && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $st = $pdo->prepare("UPDATE pasiulymai SET is_active = 0 WHERE id = ? AND supplier_id = ?");
    $st->execute([(int)$_POST['delete_id'], $supplier_id]);
    $msg = "Skelbimas pašalintas.";
}

// 3. Fetch Active Offers
$st = $pdo->prepare("SELECT o.id as offer_id, p.name as product, p.manufacturer, p.model, o.price, o.stock, o.delivery_days, o.created_at as created 
                     FROM pasiulymai o 
                     JOIN products p ON p.id = o.product_id 
                     WHERE o.supplier_id = ? AND o.is_active = 1 
                     ORDER BY o.id DESC");
$st->execute([$supplier_id]);
$pasiulymai = $st->fetchAll();

include __DIR__ . '/../header.php';
?>
<h2>Mano skelbimai</h2>
<?php if ($msg): ?><p style="color:green;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
    <tr>
      <th>ID</th><th>Prekė</th><th>Gamintojas</th><th>Modelis</th><th>Kaina (€)</th><th>Likutis</th><th>Pristatymas</th><th>Veiksmai</th>
    </tr>
    <?php foreach ($pasiulymai as $o): ?>
      <tr>
        <td><?= (int)$o['offer_id'] ?></td>
        <td><?= htmlspecialchars($o['product']) ?></td>
        <td><?= htmlspecialchars($o['manufacturer']) ?></td>
        <td><?= htmlspecialchars($o['model']) ?></td>
        <td style="text-align:right;"><?= number_format($o['price'],2,',',' ') ?></td>
        <td style="text-align:center;"><?= (int)$o['stock'] ?></td>
        <td style="text-align:center;"><?= (int)$o['delivery_days'] ?> d.d.</td>
        <td style="text-align:center;">
          <form method="post" style="margin:0;" onsubmit="return confirm('Ar tikrai norite pašalinti šį skelbimą?');">
            <input type="hidden" name="delete_id" value="<?= (int)$o['offer_id'] ?>">
            <button type="submit" style="background:#b30000;color:white;border:0;padding:5px 8px;border-radius:4px;cursor:pointer;">Pašalinti</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
</table>

<?php include __DIR__ . '/../footer.php'; ?>