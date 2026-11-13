<section class="login-card panel">
  <span class="eyebrow">DEBTLIST</span><h1>Belépés</h1>
  <p class="muted">A többi appnál használt felhasználóneveddel és jelszavaddal.</p>
  <form method="post" action="<?php echo url('/login'); ?>" class="stack">
    <input type="hidden" name="_csrf" value="<?php echo h(auth_csrf_token()); ?>">
    <label>Felhasználónév<input name="username" required autocomplete="username" autofocus></label>
    <label>Jelszó<input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit">Belépés</button>
  </form>
</section>
