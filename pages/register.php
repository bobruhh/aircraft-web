<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

if (is_logged_in()) {
    header('Location: /index.php');
    exit;
}

$errors = [];
$name   = trim($_POST['name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$pass   = $_POST['password']  ?? '';
$pass2  = $_POST['password2'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') $errors[] = 'Įrašykite vardą.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Blogas pašto formatas.';
    if (strlen($pass) < 8) $errors[] = 'Slaptažodį turi sudaryti bent 8 simboliai.';
    if ($pass !== $pass2) $errors[] = 'Slaptažodžiai nesutampa.';

    if (!$errors) {
        $pdo = db();
        // Tikriname ar el. paštas neužimtas
        $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetch()) {
            $errors[] = 'Šis el. paštas jau užregistruotas.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $st = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'VADYBININKAS')");
            if ($st->execute([$name, $email, $hash])) {
                header('Location: /login.php?reg_ok=1');
                exit;
            } else {
                $errors[] = 'Registracijos klaida duomenų bazėje.';
            }
        }
    }
}

include __DIR__ . '/../header.php';
?>
<h2>Registracija</h2>
<?php if ($errors): ?>
  <div style="color:red;"><ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form method="post">
  <label>Vardas: <input type="text" name="name" value="<?=htmlspecialchars($name)?>" required></label><br>
  <label>El. paštas: <input type="email" name="email" value="<?=htmlspecialchars($email)?>" required></label><br>
  <label>Slaptažodis: <input type="password" name="password" required></label><br>
  <label>Pakartokite slaptažodį: <input type="password" name="password2" required></label><br>
  <button type="submit">Registruotis</button>
</form>
<?php include __DIR__ . '/../footer.php'; ?>