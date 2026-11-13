<nav class="app-nav container" aria-label="Debtlist">
  <a class="brand" href="<?php echo url('/'); ?>">debtlist<span>.</span></a>
  <?php if (auth_logged_in()) { ?>
  <div class="nav-links">
    <a href="<?php echo url('/'); ?>">Áttekintés</a>
    <a href="<?php echo url('/rates'); ?>">Árfolyamok</a>
    <span class="muted username"><?php echo h(auth_username()); ?></span>
    <form method="post" action="<?php echo url('/logout'); ?>">
      <input type="hidden" name="_csrf" value="<?php echo h(auth_csrf_token()); ?>">
      <button class="text-button" type="submit">Kilépés</button>
    </form>
  </div>
  <?php } ?>
</nav>
