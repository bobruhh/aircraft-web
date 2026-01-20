<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

require_login();
include __DIR__ . '/../header.php';

$q = trim($_GET['q'] ?? '');
$order = $_GET['order'] ?? 'price';
$dir = (strtolower($_GET['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

$sql = "SELECT o.id as offer_id, p.id AS product_id, p.name as product, p.manufacturer, p.model, p.spec,
               s.id AS supplier_id, s.name as supplier, o.price, o.stock, o.delivery_days
        FROM pasiulymai o
        JOIN products p ON p.id = o.product_id
        JOIN suppliers s ON s.id = o.supplier_id
        WHERE o.is_active = 1";
$args = [];

if ($q !== '') {
    $sql .= " AND (p.name LIKE ? OR p.manufacturer LIKE ? OR p.model LIKE ? OR s.name LIKE ?)";
    $like = "%$q%";
    $args[] = $like; $args[] = $like; $args[] = $like; $args[] = $like;
}

$allowed = ['price','delivery_days'];
if (!in_array($order, $allowed, true)) $order = 'price';
$sql .= " ORDER BY $order $dir LIMIT 200";

$stmt = db()->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

?>
<h2>Prekių katalogas</h2>

<form method="get" style="margin-bottom:12px;">
  <input type="text" name="q" placeholder="Paieška (prekė / gamintojas / modelis / tiekėjas)" value="<?= htmlspecialchars($q) ?>" />
  <label>Rūšiuoti pagal</label>
  <select name="order">
    <option value="price" <?= $order==='price' ? 'selected' : '' ?>>Kaina</option>
    <option value="delivery_days" <?= $order==='delivery_days' ? 'selected' : '' ?>>Pristatymo dienos</option>
  </select>
  <select name="dir">
    <option value="asc" <?= $dir==='ASC' ? 'selected' : '' ?>>↑</option>
    <option value="desc" <?= $dir==='DESC' ? 'selected' : '' ?>>↓</option>
  </select>
  <button type="submit">Ieškoti</button>
</form>

<!-- Mygtukas palyginti pažymėtus -->

<div>
  <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
    <thead>
      <tr>
        <th>Prekė</th>
        <th>Gamintojas</th>
        <th>Modelis</th>
        <th>Tiekėjas</th>
        <th>Kaina</th>
        <th>Likutis</th>
        <th>Pristatymas (d.d.)</th>
        <th>Veiksmai</th>
        <th>Palyginti</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr><td colspan="9" style="text-align:center">Nerasta pasiūlymų</td></tr>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          
          <td><?= htmlspecialchars($r['product']) ?></td>
          <td><?= htmlspecialchars($r['manufacturer']) ?></td>
          <td><?= htmlspecialchars($r['model']) ?></td>
          <td><?= htmlspecialchars($r['supplier']) ?></td>
          <td style="text-align:right;"><?= number_format($r['price'],2,',',' ') ?> €</td>
          <td style="text-align:center;"><?= (int)$r['stock'] ?></td>
          <td style="text-align:center;"><?= (int)$r['delivery_days'] ?></td>
          <td align="center">
          <a href="#popup-<?= $r['offer_id'] ?>">Peržiūrėti</a>
          <?php if ($user['role'] === 'VADYBININKAS'): ?>
            &nbsp;|&nbsp;
            <a href="/pages/add_to_cart.php?offer_id=<?= urlencode($r['offer_id']) ?>">Į krepšelį</a>
          <?php endif; ?>
          </td>
          
          <td style="text-align:center;">
            <input type="checkbox" class="cmp-select" value="<?= (int)$r['offer_id'] ?>">
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
  <form onsubmit="return compareSelected();" 
      style="display:flex; justify-content:flex-end; margin-bottom:12px;">
    <button type="submit" style="font-size: 18px; background-color:cornflowerblue; width: 129px;">
      Palyginti pažymėtus (iki 4)
    </button>
  </form>
</div>


<!-- perziuros popup stylingas -->
<style>
.popup-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.popup-overlay:target {
  display: flex;
}

.popup-box {
  background: white;
  padding: 16px;
  border-radius: 6px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 0 15px rgba(0,0,0,0.3);
  position: relative;
}

.popup-close {
  position: absolute;
  right: 10px;
  top: 6px;
  font-size: 20px;
  text-decoration: none;
  color: #333;
}
</style>

<?php foreach ($rows as $r): ?>
<div id="popup-<?= $r['offer_id'] ?>" class="popup-overlay">
  <div class="popup-box">
    <a href="#" class="popup-close">×</a>

    <h3><?= htmlspecialchars($r['product']) ?></h3>
    <p><strong>Gamintojas:</strong> <?= htmlspecialchars($r['manufacturer']) ?></p>
    <p><strong>Modelis:</strong> <?= htmlspecialchars($r['model']) ?></p>
    <p><strong>Tiekėjas:</strong> <?= htmlspecialchars($r['supplier']) ?></p>
    <p><strong>Kaina:</strong> <?= number_format($r['price'],2,',',' ') ?> €</p>
    <p><strong>Aprašymas:</strong> <?= htmlspecialchars($r['spec']) ?></p>

    <p style="margin-top:10px;">
        <?php if ($user['role'] === 'VADYBININKAS'): ?>
          &nbsp;|&nbsp;
          <a href="/pages/add_to_cart.php?offer_id=<?= urlencode($r['offer_id']) ?>">Į krepšelį</a> |
        <?php endif; ?> 
      <a href="/pages/compare.php?left=<?= $r['offer_id'] ?>">Palyginti</a>
    </p>
  </div>
</div>
<?php endforeach; ?>



<script>
function compareSelected(){
  const boxes = Array.from(document.querySelectorAll('.cmp-select:checked'));
  if (boxes.length < 2){
    alert('Pasirink bent 2 pasiūlymus palyginimui.');
    return false;
  }
  if (boxes.length > 4){
    alert('Galima palyginti daugiausia 4 pasiūlymus.');
    return false;
  }
  const ids = boxes.map(b => b.value);
  const params = new URLSearchParams();
  params.set('left', ids[0]);
  if (ids[1]) params.set('right', ids[1]);
  if (ids[2]) params.set('third', ids[2]);
  if (ids[3]) params.set('fourth', ids[3]);
  window.location.href = '/pages/compare.php?' + params.toString();
  return false;
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>
