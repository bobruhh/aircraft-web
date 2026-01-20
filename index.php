<?php
require_once __DIR__.'/auth.php';
require_login();
include __DIR__.'/header.php';

$u = current_user();
?>
<h2>Sveiki, <?= htmlspecialchars($u['name']) ?>!</h2>
<h3>Jūsų rolė: <?= htmlspecialchars($u['role']) ?></h3>

<style>
  img {
    width: 100%;
  }

  li {
    font-size: 35px;
  }
  .menu-boxes {
    display: flex;
    justify-content: space-evenly; 
    flex-wrap: wrap;              
    list-style: none;
    padding: 0;
    gap: 20px;
}

  .menu-boxes a {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 260px;
      height: 120px; 
      background: lightcyan;
      border-radius: 12px;
      text-decoration: none;
      color: darkblue;
      font-weight: bold;
      text-align: center;
      border: 2px solid gray;
  }

  .menu-boxes a:hover {
      background: lightblue;
      border-color: blueviolet;
  }
</style>
<img src="plane.jpg" alt="plane">

<ul class="menu-boxes">

  <?php if ($u['role'] === 'VADYBININKAS'): ?>
    <li><a href="/pages/catalog.php">🗐 Prekių katalogas</a></li>
    <li><a href="/pages/cart.php">🗖 Mano krepšelis</a></li>
    <li><a href="/pages/my_orders.php">⛟ Mano užsakymai</a></li>
  <?php endif; ?>

  <?php if ($u['role'] === 'TIEKEJAS'): ?>
    <li><a href="/pages/offer_new.php">𓄲 Pridėti prekę</a></li>
    <li><a href="/pages/my_offers.php">⛁ Mano skelbimai</a></li>
    <li><a href="/pages/supplier_orders.php">➢ Gauti užsakymai</a></li>
    <li><a href="/pages/catalog.php">🗐 Prekių katalogas</a></li>
  <?php endif; ?>

  <?php if ($u['role'] === 'DIREKTORIUS'): ?>
    <li><a href="/pages/stats.php">⁰⁄₀  Pardavimų statistika</a></li>
    <li><a href="/pages/users_admin.php">ጸ Naudotojai</a></li>
    <li><a href="/pages/catalog.php">🗐 Prekių katalogas</a></li>
  <?php endif; ?>

  <li><a href="/logout.php">𓉞 Atsijungti</a></li>

</ul>


<?php include __DIR__.'/footer.php'; ?>

