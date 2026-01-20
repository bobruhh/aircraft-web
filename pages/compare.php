<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_login();

$pdo = db();

$leftId   = (int)($_GET['left']   ?? 0);
$rightId  = (int)($_GET['right']  ?? 0);
$thirdId  = (int)($_GET['third']  ?? 0);
$fourthId = (int)($_GET['fourth'] ?? 0);
$q2       = trim($_GET['q2'] ?? '');  

if ($leftId <= 0) { header('Location: /pages/catalog.php'); exit; }

function fetch_offer($id, PDO $pdo) {
  $sql = "SELECT o.id as offer_id, o.price, o.stock, o.delivery_days,
                 p.id as product_id, p.code, p.name as product, p.manufacturer, p.model, p.spec,
                 s.id as supplier_id, s.name as supplier
          FROM pasiulymai o
          JOIN products p ON p.id = o.product_id
          JOIN suppliers s ON s.id = o.supplier_id
          WHERE o.id = ?";
  $st = $pdo->prepare($sql);
  $st->execute([$id]);
  return $st->fetch();
}

$left   = fetch_offer($leftId,  $pdo);
$right  = $rightId  ? fetch_offer($rightId,  $pdo) : null;
$third  = $thirdId  ? fetch_offer($thirdId,  $pdo) : null;
$fourth = $fourthId ? fetch_offer($fourthId, $pdo) : null;

// skirtingiems pazymet
$diff = function($a,$b){ return (string)$a !== (string)$b ? 'diff' : ''; };

$candidates = [];
if (!$right) {
  $args = [];
  $where = "1";
  if ($left) {
    $where .= " AND (o.product_id = ?)";
    $args[] = $left['product_id'];
    $where_same = $where . " AND o.id <> ?";
    $args_same = array_merge($args, [$left['offer_id']]);
    $sqlSame = "SELECT o.id as offer_id, s.name as supplier, o.price, o.stock, o.delivery_days
                FROM pasiulymai o JOIN suppliers s ON s.id=o.supplier_id
                WHERE $where_same ORDER BY o.price ASC LIMIT 20";
    $st = $pdo->prepare($sqlSame); $st->execute($args_same);
    $sameProduct = $st->fetchAll();
  } else { $sameProduct = []; }

  $args2 = [];
  $where2 = "1";
  if ($q2 !== '') {
    $where2 .= " AND (p.name LIKE ? OR p.manufacturer LIKE ? OR p.model LIKE ? OR s.name LIKE ?)";
    $like = "%$q2%"; $args2 = [$like,$like,$like,$like];
  }
  $sqlAny = "SELECT o.id as offer_id, p.name as product, p.manufacturer, p.model, s.name as supplier,
                    o.price, o.stock, o.delivery_days
             FROM pasiulymai o
             JOIN products p ON p.id = o.product_id
             JOIN suppliers s ON s.id = o.supplier_id
             WHERE $where2
             ORDER BY o.price ASC LIMIT 50";
  $st = $pdo->prepare($sqlAny); $st->execute($args2);
  $anyOffers = $st->fetchAll();
}

include __DIR__.'/../header.php';
?>
<h2>Pasiūlymų palyginimas</h2>

<p><a href="/pages/catalog.php">← Grįžti į katalogą</a></p>

<?php if (!$left): ?>
  <p style="color:#b00">Nerastas kairysis pasiūlymas.</p>
  <?php include __DIR__.'/../footer.php'; exit; ?>
<?php endif; ?>

<style>
  .cmp-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:16px;
    align-items:start
  }
  .cmp-card{
    border:1px solid #ddd;border-radius:8px;padding:12px
  }
  .cmp-row{
    display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:4px 0;border-bottom:1px dashed #eee
  }
  .cmp-row:last-child{
    border-bottom:0
  }
  .title{
    font-weight:600;margin:0 0 8px
  }
  .muted{
    color:#666;font-size:18px
  }
  .cmp-row.diff{
    background:lightgoldenrodyellow;font-weight:500;
    }
</style>

<div class="cmp-grid">
  <!-- KAIRĖ -->
  <div class="cmp-card">
    <div class="title">Kairė (pasiūlymas #<?= (int)$left['offer_id'] ?>)</div>
    <div class="muted"><?= htmlspecialchars($left['product']) ?> ·
      <?= htmlspecialchars($left['manufacturer'].' '.$left['model']) ?></div>
    <div class="cmp-row"><div>Tiekėjas</div><div><?= htmlspecialchars($left['supplier']) ?></div></div>
    <div class="cmp-row"><div>Kaina</div><div><?= number_format($left['price'],2,',',' ') ?> €</div></div>
    <div class="cmp-row"><div>Likutis</div><div><?= (int)$left['stock'] ?></div></div>
    <div class="cmp-row"><div>Pristatymo dienos</div><div><?= (int)$left['delivery_days'] ?></div></div>
    <div class="cmp-row"><div>Aprašymas</div><div><?= nl2br(htmlspecialchars($left['spec'])) ?></div></div>
    <p style="margin-top:10px">
        <a href="/pages/add_to_cart.php?offer_id=<?= (int)$left['offer_id'] ?>">Į krepšelį (kairį)</a>
    </p>
  </div>

  <!-- DEŠINĖ-->
  <div class="cmp-card">
    <?php if (!$right): ?>
      <div class="title">Pasirink dešinį pasiūlymą</div>

      <?php if (!empty($sameProduct)): ?>
        <p class="muted">To paties produkto pasiūlymai:</p>
        <ul>
          <?php foreach ($sameProduct as $o): ?>
            <li>
              <a href="/pages/compare.php?left=<?= (int)$left['offer_id'] ?>&right=<?= (int)$o['offer_id'] ?>">
                #<?= (int)$o['offer_id'] ?> · <?= htmlspecialchars($o['supplier']) ?> —
                <?= number_format($o['price'],2,',',' ') ?> € (likutis: <?= (int)$o['stock'] ?>, prist.: <?= (int)$o['delivery_days'] ?> d.)
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form method="get" style="margin-top:8px">
        <input type="hidden" name="left" value="<?= (int)$left['offer_id'] ?>">
        <label>Paieška kitiems pasiūlymams:</label><br>
        <input type="text" name="q2" value="<?= htmlspecialchars($q2) ?>" placeholder="prekė / gamintojas / modelis / tiekėjas" style="width:80%">
        <button>Ieškoti</button>
      </form>

      <?php if (!empty($anyOffers)): ?>
        <p class="muted" style="margin-top:10px">Visi pasiūlymai:</p>
        <ul>
          <?php foreach ($anyOffers as $o): ?>
            <li>
              <a href="/pages/compare.php?left=<?= (int)$left['offer_id'] ?>&right=<?= (int)$o['offer_id'] ?>">
                #<?= (int)$o['offer_id'] ?> · <?= htmlspecialchars($o['product']) ?> / <?= htmlspecialchars($o['manufacturer'].' '.$o['model']) ?> —
                <?= htmlspecialchars($o['supplier']) ?> · <?= number_format($o['price'],2,',',' ') ?> €
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    <?php else: ?>
      <div class="title">Dešinė (pasiūlymas #<?= (int)$right['offer_id'] ?>)</div>
      <div class="muted"><?= htmlspecialchars($right['product']) ?> ·
        <?= htmlspecialchars($right['manufacturer'].' '.$right['model']) ?></div>

      <div class="cmp-row <?= $diff($left['supplier'],$right['supplier']) ?>">
        <div>Tiekėjas</div>
        <div><?= htmlspecialchars($right['supplier']) ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['price'],$right['price']) ?>">
        <div>Kaina</div>
        <div><?= number_format($right['price'],2,',',' ') ?> €</div>
      </div>
      <div class="cmp-row <?= $diff($left['stock'],$right['stock']) ?>">
        <div>Likutis</div>
        <div><?= (int)$right['stock'] ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['delivery_days'],$right['delivery_days']) ?>">
        <div>Pristatymo dienos</div>
        <div><?= (int)$right['delivery_days'] ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['manufacturer'],$right['manufacturer']) || $diff($left['model'],$right['model']) ? 'diff':'' ?>">
        <div>Gamintojas / modelis</div>
        <div><?= htmlspecialchars($right['manufacturer'].' '.$right['model']) ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['spec'],$right['spec']) ?>">
        <div>Aprašymas</div>
        <div><?= nl2br(htmlspecialchars($right['spec'])) ?></div>
      </div>

      <p style="margin-top:10px">
        <a href="/pages/add_to_cart.php?offer_id=<?= (int)$right['offer_id'] ?>">Į krepšelį (dešinį)</a>
      </p>
    <?php endif; ?>
  </div>

  <!-- TREČIAS-->
  <?php if ($third): ?>
    <div class="cmp-card">
      <div class="title">Trečias (pasiūlymas #<?= (int)$third['offer_id'] ?>)</div>
      <div class="muted"><?= htmlspecialchars($third['product']) ?> ·
        <?= htmlspecialchars($third['manufacturer'].' '.$third['model']) ?></div>

      <div class="cmp-row <?= $diff($left['supplier'],$third['supplier']) ?>">
        <div>Tiekėjas</div>
        <div><?= htmlspecialchars($third['supplier']) ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['price'],$third['price']) ?>">
        <div>Kaina</div>
        <div><?= number_format($third['price'],2,',',' ') ?> €</div>
      </div>
      <div class="cmp-row <?= $diff($left['stock'],$third['stock']) ?>">
        <div>Likutis</div>
        <div><?= (int)$third['stock'] ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['delivery_days'],$third['delivery_days']) ?>">
        <div>Pristatymo dienos</div>
        <div><?= (int)$third['delivery_days'] ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['manufacturer'],$third['manufacturer']) || $diff($left['model'],$third['model']) ? 'diff':'' ?>">
        <div>Gamintojas / modelis</div>
        <div><?= htmlspecialchars($third['manufacturer'].' '.$third['model']) ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['spec'],$third['spec']) ?>">
        <div>Aprašymas</div>
        <div><?= nl2br(htmlspecialchars($third['spec'])) ?></div>
      </div>

      <p style="margin-top:10px">
        <a href="/pages/add_to_cart.php?offer_id=<?= (int)$third['offer_id'] ?>">Į krepšelį (trečią)</a>
      </p>
    </div>
  <?php endif; ?>

  <!-- KETVIRTAS -->
  <?php if ($fourth): ?>
    <div class="cmp-card">
      <div class="title">Ketvirtas (pasiūlymas #<?= (int)$fourth['offer_id'] ?>)</div>
      <div class="muted"><?= htmlspecialchars($fourth['product']) ?> ·
        <?= htmlspecialchars($fourth['manufacturer'].' '.$fourth['model']) ?></div>

      <div class="cmp-row <?= $diff($left['supplier'],$fourth['supplier']) ?>">
        <div>Tiekėjas</div>
        <div><?= htmlspecialchars($fourth['supplier']) ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['price'],$fourth['price']) ?>">
        <div>Kaina</div>
        <div><?= number_format($fourth['price'],2,',',' ') ?> €</div>
      </div>
      <div class="cmp-row <?= $diff($left['stock'],$fourth['stock']) ?>">
        <div>Likutis</div>
        <div><?= (int)$fourth['stock'] ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['delivery_days'],$fourth['delivery_days']) ?>">
        <div>Pristatymo dienos</div>
        <div><?= (int)$fourth['delivery_days'] ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['manufacturer'],$fourth['manufacturer']) || $diff($left['model'],$fourth['model']) ? 'diff':'' ?>">
        <div>Gamintojas / modelis</div>
        <div><?= htmlspecialchars($fourth['manufacturer'].' '.$fourth['model']) ?></div>
      </div>
      <div class="cmp-row <?= $diff($left['spec'],$fourth['spec']) ?>">
        <div>Aprašymas</div>
        <div><?= nl2br(htmlspecialchars($fourth['spec'])) ?></div>
      </div>

      <p style="margin-top:10px">
        <a href="/pages/add_to_cart.php?offer_id=<?= (int)$fourth['offer_id'] ?>">Į krepšelį (ketvirtą)</a>
      </p>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__.'/../footer.php'; ?>
