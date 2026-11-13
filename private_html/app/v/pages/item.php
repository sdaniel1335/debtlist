<div class="page-heading"><div><a href="<?php echo url('/'); ?>">← Vissza a listához</a><h1><?php echo h($title); ?></h1></div></div>
<?php foreach ($errors as $error) { ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php } ?>
<form method="post" action="<?php echo url('/item'); ?>" class="panel editor stack" id="item-form">
  <input type="hidden" name="_csrf" value="<?php echo h(auth_csrf_token()); ?>"><input type="hidden" name="id" value="<?php echo h($item['id']); ?>">
  <div class="form-grid">
    <label class="span-2">Megnevezés<input name="name" value="<?php echo h($item['name']); ?>" required maxlength="200" placeholder="Pl. Lakáshitel, Netflix, gáz" autofocus></label>
    <label>Csoport<select name="group_code"><?php foreach (groups() as $code => $label) { ?><option value="<?php echo h($code); ?>"<?php echo $item['group_code'] === $code ? ' selected' : ''; ?>><?php echo h($code); ?></option><?php } ?></select></label>
    <label>Típus<select name="kind" id="kind"><option value="expense"<?php echo $item['kind'] === 'expense' ? ' selected' : ''; ?>>Rezsi / előfizetés</option><option value="loan"<?php echo $item['kind'] === 'loan' ? ' selected' : ''; ?>>Hitel</option></select></label>
  </div>
  <fieldset><legend>Alkalmanként fizetendő összeg</legend>
    <div class="form-grid four-cols">
      <label>Minimum<input name="amount_min" inputmode="decimal" value="<?php echo h($item['amount_min']); ?>" placeholder="Alapösszeg"></label>
      <label>Alapösszeg<input name="amount_base" inputmode="decimal" value="<?php echo h($item['amount_base']); ?>" required placeholder="0"></label>
      <label>Maximum<input name="amount_max" inputmode="decimal" value="<?php echo h($item['amount_max']); ?>" placeholder="Alapösszeg"></label>
      <label>Deviza<input name="currency" value="<?php echo h($item['currency']); ?>" required pattern="[A-Za-z]{3}" maxlength="3" list="currencies"><datalist id="currencies"><option value="HUF"><option value="EUR"><option value="USD"></datalist></label>
    </div>
    <p class="help-text">Fix összegnél elég az alapösszeg. A minimum és maximum üresen ugyanaz lesz. Devizás tételekhez az Árfolyamok oldalon adhatsz meg váltási sávot.</p>
  </fieldset>
  <fieldset><legend>Ütemezés</legend><div class="form-grid">
    <label data-expense>Gyakoriság<select name="frequency"><?php foreach (frequencies() as $key => $label) { ?><option value="<?php echo h($key); ?>"<?php echo $item['frequency'] === $key ? ' selected' : ''; ?>><?php echo h($label); ?></option><?php } ?></select></label>
    <label>Első esedékesség<input type="date" name="first_due" min="1900-01-01" max="2200-12-31" value="<?php echo h($item['first_due']); ?>" required></label>
    <label data-expense>Utolsó esedékesség / befejezés<input type="date" name="end_date" min="1900-01-01" max="2200-12-31" value="<?php echo h($item['end_date']); ?>"><small>Opcionális, a megadott nap még beleszámít.</small></label>
    <label data-loan>Törlesztőrészletek száma<input type="number" name="installments" min="1" max="1200" step="1" value="<?php echo h($item['installments']); ?>" placeholder="Pl. 84"></label>
    <label data-loan>Havi biztosítás<input name="insurance" inputmode="decimal" value="<?php echo h($item['insurance']); ?>" placeholder="0"><small>A hitelével azonos devizában; minden részlethez hozzáadjuk.</small></label>
  </div>
  <p class="help-text">Heti tételnél az első dátum a hét napját is kijelöli. Havi és ritkább díjnál ugyanazt a napot használjuk; ha nincs ilyen nap, a hónap utolsó napját. A hitel mindig havi, a végét a részletek számából számoljuk.</p>
  </fieldset>
  <label>Megjegyzés<textarea name="notes" rows="3" maxlength="4000"><?php echo h($item['notes']); ?></textarea></label>
  <div class="form-actions"><button type="submit">Tétel mentése</button><a class="button secondary" href="<?php echo url('/'); ?>">Mégse</a></div>
  <p class="help-text">A módosítás minden megtekintett időszakra érvényes. Az app nem őriz összeg- vagy befizetéstörténetet.</p>
</form>
<?php if ($item['id']) { ?>
<details class="panel delete-panel"><summary>Tétel törlése</summary><form method="post" action="<?php echo url('/delete'); ?>" class="stack">
  <input type="hidden" name="_csrf" value="<?php echo h(auth_csrf_token()); ?>"><input type="hidden" name="id" value="<?php echo h($item['id']); ?>">
  <label class="checkbox"><input type="checkbox" name="confirm" value="yes" required> Végleg törlöm ezt a tételt: <?php echo h($item['name']); ?></label><button class="danger" type="submit">Tétel törlése</button>
</form></details>
<?php } ?>
