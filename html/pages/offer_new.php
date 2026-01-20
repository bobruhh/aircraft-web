<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_role('TIEKEJAS');

$msg = '';
$err = '';
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name  = trim($_POST['product_name'] ?? '');
    $manufacturer  = trim($_POST['manufacturer'] ?? '');
    $model         = trim($_POST['model'] ?? '');
    $spec          = trim($_POST['spec'] ?? '');
    $price         = (float)($_POST['price'] ?? 0);
    $stock         = (int)($_POST['stock'] ?? 0);
    $delivery_days     = (int)($_POST['delivery_days'] ?? 0);

    if ($product_name === '' || $price <= 0) {
        $err = 'Užpildykite Prekės pavadinimą ir teisingą kainą.';
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            //Tiekejas pagal prisijungusi vartotoja (user_id)
            $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE user_id = ? LIMIT 1");
            $stmt->execute([$user['id']]);
            $supplier_id = $stmt->fetchColumn();

            // jeigu pirma karta, sukurti tiekeja su vartotojo vardu
            if (!$supplier_id) {
                $ins = $pdo->prepare("INSERT INTO suppliers (name, user_id) VALUES (?, ?)");
                $ins->execute([$user['name'], $user['id']]);
                $supplier_id = $pdo->lastInsertId();
            }

            //Preke: rasti arba sukurti pagal (name, manufacturer, model)
            $stmt = $pdo->prepare(
                "SELECT id FROM products WHERE name = ? AND manufacturer = ? AND model = ? LIMIT 1"
            );
            $stmt->execute([$product_name, $manufacturer, $model]);
            $product_id = $stmt->fetchColumn();

            if (!$product_id) {
                $ins = $pdo->prepare(
                    "INSERT INTO products (name, manufacturer, model, spec) VALUES (?,?,?,?)"
                );
                $ins->execute([$product_name, $manufacturer, $model, $spec]);
                $product_id = $pdo->lastInsertId();
            }

            //Pasiulymas
            $ins = $pdo->prepare(
                "INSERT INTO pasiulymai (supplier_id, product_id, price, stock, delivery_days)
                 VALUES (?,?,?,?,?)"
            );
            $ins->execute([$supplier_id, $product_id, $price, $stock, $delivery_days]);

            $pdo->commit();
            $msg = 'Pasiūlymas sėkmingai pridėtas!';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();

            if ($e instanceof PDOException && $e->getCode() === '23000') {
                $err = 'Toks pasiūlymas jau egzistuoja.';
            } else {
                $err = 'Klaida: '.$e->getMessage();
            }
        }

    }
}

include __DIR__ . '/../header.php';
?>

<style>
  div {
    place-items: center;
  }
</style>


<div>
  <h2>Pridėti prekę</h2>

  <?php if ($msg): ?><p style="color:green"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
  <?php if ($err): ?><p style="color:#b00"><?= htmlspecialchars($err) ?></p><?php endif; ?>

  <form method="post" >
    <fieldset>
      <legend>Apie prekę</legend>
      <label>Pavadinimas *
        <input type="text" name="product_name" required>
      </label>
      <label>Gamintojas
        <input type="text" name="manufacturer">
      </label>
      <label>Modelis
        <input type="text" name="model">
      </label>
      <label>Aprašymas
        <textarea name="spec" rows="1"></textarea>
      </label>
    </fieldset>

    <fieldset>
      <legend>Pasiūlymas</legend>
      <label>Kaina (€) *
        <input type="number" step="0.01" min="0" name="price" required>
      </label>
      <label>Likutis (vnt.)
        <input type="number" min="0" name="stock" value="0">
      </label>
      <label>Pristatymo dienos
        <input type="number" min="0" name="delivery_days" value="0">
      </label>
    </fieldset>

    <button type="submit">Išsaugoti</button>
  </form>
</div>

<p style="margin-top:12px;">
  <a href="/pages/my_offers.php">← Mano skelbimai</a> |
  <a href="/index.php">Grįžti į pradžią</a>
</p>
<?php include __DIR__ . '/../footer.php'; ?>

