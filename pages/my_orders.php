<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_login();

$user = current_user();
$pdo = db();
$msg = ''; 
$err = '';

// Cancel Order Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $st = $pdo->prepare("UPDATE orders SET status='ATSAUKTAS' WHERE id = ? AND user_id = ? AND status IN ('PATEIKTAS','LAUKIAMA')");
    $st->execute([(int)$_POST['cancel_id'], $user['id']]);
    if ($st->rowCount()) {
        $msg = "Užsakymas atšauktas.";
    } else {
        $err = "Užsakymo atšaukti nepavyko (galbūt jis jau vykdomas).";
    }
}

// Fetch list
$st = $pdo->prepare("SELECT id, created_at, status, total FROM orders WHERE user_id = ? ORDER BY id DESC");
$st->execute([$user['id']]);
$orders = $st->fetchAll();

include __DIR__.'/../header.php';
?>
<h2>Mano užsakymai</h2>

<?php if ($msg): ?><p style="color:green;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<?php if ($err): ?><p style="color:red;"><?= htmlspecialchars($err) ?></p><?php endif; ?>

<?php if (empty($orders)): ?>
  <p>Dar neturite užsakymų.</p>
<?php else: ?>
  <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
    <tr>
      <th>ID</th><th>Sukurta</th><th>Būsena</th><th>Suma (€)</th><th>Veiksmai</th>
    </tr>
    <?php foreach ($orders as $r): ?>
      <tr>
        <td>#<?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
        <td style="text-align:right;"><?= number_format((float)$r['total'], 2, ',', ' ') ?></td>
        <td style="text-align:center;">
          <a href="/pages/order_view.php?id=<?= (int)$r['id'] ?>">Peržiūrėti</a>
          <?php if (in_array($r['status'], ['PATEIKTAS','LAUKIAMA'], true)): ?>
            |
            <form method="post" style="display:inline;" onsubmit="return confirm('Atšaukti šį užsakymą?');">
              <input type="hidden" name="cancel_id" value="<?= (int)$r['id'] ?>">
              <button type="submit" style="background:none; border:none; color:red; cursor:pointer; text-decoration:underline; padding:0;">Atšaukti</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<?php include __DIR__.'/../footer.php'; ?>