<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_role('DIREKTORIUS');

$pdo = db();

$tot_suppliers = (int)$pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$tot_products  = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$tot_offers    = (int)$pdo->query("SELECT COUNT(*) FROM pasiulymai")->fetchColumn();

// PostgreSQL naudoja TO_CHAR vietoje DATE_FORMAT
$sql = "SELECT TO_CHAR(created_at, 'YYYY-MM') AS period, 
               COUNT(id) AS orders, 
               COALESCE(SUM(total), 0) AS revenue 
        FROM orders 
        GROUP BY 1 ORDER BY 1 DESC LIMIT 12";
$sales_rows = $pdo->query($sql)->fetchAll();

include __DIR__ . '/../header.php';
?>
<h2>Sistemos statistika</h2>
<ul>
    <li>Tiekėjų skaičius: <?= $tot_suppliers ?></li>
    <li>Prekių kataloge: <?= $tot_products ?></li>
    <li>Aktyvių pasiūlymų: <?= $tot_offers ?></li>
</ul>
<?php include __DIR__ . '/../footer.php'; ?>