<?php

require_once __DIR__.'/auth.php';     
if (is_logged_in()) { header('Location: /index.php'); exit; }

include __DIR__.'/header.php';        
?>

<style>
  div {
    place-items: center;
  }
</style>


<div>
  <h2>Prisijungimas</h2>

  <?php if (!empty($_GET['err'])): ?>
    <p style="color:red">Neteisingas el. paštas arba slaptažodis.</p>
  <?php endif; ?>

  <form method="post" action="/login_action.php" style="max-width:340px;display:grid;gap:10px">
    <label>El. paštas
      <input type="email" name="email" required autofocus>
    </label>
    <label>Slaptažodis
      <input type="password" name="password" required>
    </label>
    <button type="submit">Prisijungti</button>
  </form>

  <p style="margin-top:10px">
    Neturi paskyros? <a href="/pages/register.php">Registruotis</a>
  </p>
</div>

<?php 
include __DIR__.'/footer.php'; ?>

