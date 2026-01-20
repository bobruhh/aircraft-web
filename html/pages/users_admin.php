<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_role('DIREKTORIUS');

$pdo = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['roles'])) {
    $st = $pdo->prepare("UPDATE users SET role = ? WHERE id = ? AND role != 'DIREKTORIUS'");
    foreach ($_POST['roles'] as $uid => $role) {
        if (in_array($role, ['VADYBININKAS', 'TIEKEJAS'])) {
            $st->execute([$role, (int)$uid]);
        }
    }
    $msg = "Rolės sėkmingai atnaujintos.";
}

$users = $pdo->query("SELECT id, name, email, role, is_active FROM users ORDER BY id ASC")->fetchAll();

include __DIR__.'/../header.php';
?>
<h2>Vartotojų valdymas</h2>
<?php if($msg): ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>
<form method="post">
<table border="1">
  <tr><th>Vardas</th><th>El. paštas</th><th>Rolė</th><th>Keisti</th></tr>
  <?php foreach($users as $u): ?>
  <tr>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= $u['role'] ?></td>
    <td>
      <?php if($u['role'] !== 'DIREKTORIUS'): ?>
      <select name="roles[<?= $u['id'] ?>]">
        <option value="VADYBININKAS" <?= $u['role']=='VADYBININKAS'?'selected':'' ?>>Vadybininkas</option>
        <option value="TIEKEJAS" <?= $u['role']=='TIEKEJAS'?'selected':'' ?>>Tiekėjas</option>
      </select>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<button type="submit">Išsaugoti pakeitimus</button>
</form>
<?php include __DIR__.'/../footer.php'; ?>