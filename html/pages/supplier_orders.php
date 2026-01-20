<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';

require_role('TIEKEJAS');

$pdo = db();
$user = current_user();

// Rasti, su kuriuo tiekėju susietas šis naudotojas
$st = $pdo->prepare("SELECT id FROM suppliers WHERE user_id = ? LIMIT 1");
$st->execute([$user['id']]);
$supplier_id = $st->fetchColumn();

if (!$supplier_id) {
    http_response_code(403);
    exit('Šis naudotojas nėra priskirtas jokiam tiekėjui.');
}

$msg = '';
$err = '';

$allowed_statuses = ['PATVIRTINTAS','VYKDOMAS','ISSIUSTAS','PRISTATYTAS','ATSAUKTAS'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id   = (int)$_POST['order_id'];
    $new_status = trim($_POST['status'] ?? '');

    if (!in_array($new_status, $allowed_statuses, true)) {
        $err = 'Neleistina nauja būsena.';
    } else {
        // Patikrinam, ar šis užsakymas tikrai turi eilučių šiam tiekėjui
        $chk = $pdo->prepare("
            SELECT o.status
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            WHERE o.id = ? AND oi.supplier_id = ?
            LIMIT 1
        ");
        $chk->execute([$order_id, $supplier_id]);
        $row = $chk->fetch();

        if (!$row) {
            $err = 'Neturite teisių šiam užsakymui.';
        } else {
            $current = $row['status'];
            // Pvz.: jau pristatyto arba atšaukto keisti nebeleidžiam
            if (in_array($current, ['ATSAUKTAS','PRISTATYTAS'], true)) {
                $err = 'Šios būsenos užsakymo keisti nebegalima.';
            } else {
                $upd = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $upd->execute([$new_status, $order_id]);
                $msg = "Užsakymo #{$order_id} būsena pakeista į {$new_status}.";
            }
        }
    }
}

// Užsakymai, kuriuose yra bent viena eilutė šio tiekėjo
$sql = "
    SELECT DISTINCT o.id, o.created_at, o.status, o.total
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    WHERE oi.supplier_id = ?
    ORDER BY o.id DESC
";
$st = $pdo->prepare($sql);
$st->execute([$supplier_id]);
$orders = $st->fetchAll();

include __DIR__.'/../header.php';
?>
<h2>Mano gauti užsakymai (tiekėjas)</h2>

<?php if ($msg): ?>
  <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>
<?php if ($err): ?>
  <p style="color:#b00;"><?= htmlspecialchars($err) ?></p>
<?php endif; ?>

<?php if (!$orders): ?>
  <p>Šiuo metu neturite užsakymų.</p>
<?php else: ?>
  <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
    <tr>
      <th>ID</th>
      <th>Data</th>
      <th>Būsena</th>
      <th>Suma</th>
      <th>Keisti būseną</th>
    </tr>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td>#<?= (int)$o['id'] ?></td>
        <td><?= htmlspecialchars($o['created_at']) ?></td>
        <td><?= htmlspecialchars($o['status']) ?></td>
        <td style="text-align:right;"><?= number_format((float)$o['total'], 2, ',', ' ') ?> €</td>
        <td>
          <form method="post" style="display:inline-block;">
            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
            <select name="status">
              <?php foreach ($allowed_statuses as $st): ?>
                <option value="<?= $st ?>" <?= $st === $o['status'] ? 'selected' : '' ?>>
                  <?= $st ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit">Išsaugoti</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<p style="margin-top:12px;"><a href="/index.php">← Grįžti</a></p>
<?php include __DIR__.'/../footer.php'; ?>
