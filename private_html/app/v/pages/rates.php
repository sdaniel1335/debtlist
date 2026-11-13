<div class="page-heading"><div><span class="eyebrow">Devizák</span><h1>Árfolyamok</h1><p class="muted">Egy egység deviza értéke forintban. Kézzel állítható, saját becslés.</p></div><a class="button secondary" href="<?php echo url('/'); ?>">← Áttekintés</a></div>
<?php foreach ($errors as $error) { ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php } ?>
<section class="panel editor stack">
  <h2>Új deviza vagy árfolyam módosítása</h2>
  <form method="post" action="<?php echo url('/rates'); ?>" class="stack">
    <input type="hidden" name="_csrf" value="<?php echo h(auth_csrf_token()); ?>">
    <div class="form-grid four-cols">
      <label>Deviza<input name="currency" pattern="[A-Za-z]{3}" maxlength="3" placeholder="EUR" value="<?php echo h(isset($input['currency']) ? $input['currency'] : ''); ?>" required></label>
      <label>Minimum · Ft<input name="rate_min" inputmode="decimal" placeholder="Alapárfolyam" value="<?php echo h(isset($input['rate_min']) ? $input['rate_min'] : ''); ?>"></label>
      <label>Alap · Ft<input name="rate_base" inputmode="decimal" placeholder="Pl. 400" value="<?php echo h(isset($input['rate_base']) ? $input['rate_base'] : ''); ?>" required></label>
      <label>Maximum · Ft<input name="rate_max" inputmode="decimal" placeholder="Alapárfolyam" value="<?php echo h(isset($input['rate_max']) ? $input['rate_max'] : ''); ?>"></label>
    </div><div><button type="submit">Árfolyam mentése</button></div>
  </form>
  <p class="help-text">Egy meglévő devizakód megadásával felülírod annak árfolyamát. A változás rögtön minden hozzá tartozó tételre érvényes. A HUF értéke mindig 1.</p>
</section>
<section class="panel"><div class="table-scroll"><table><thead><tr><th>Deviza</th><th class="numeric">Minimum</th><th class="numeric">Alap</th><th class="numeric">Maximum</th><th>Frissítve</th></tr></thead><tbody>
<?php foreach ($rates as $rate) { ?><tr><th><?php echo h($rate['currency']); ?></th><?php foreach (array('min', 'base', 'max') as $level) { ?><td class="numeric"><?php echo h(amount_text($rate['rate_' . $level])); ?> Ft</td><?php } ?><td class="muted"><?php echo h(isset($rate['updated_at']) ? $rate['updated_at'] : 'Rögzített'); ?></td></tr><?php } ?>
</tbody></table></div></section>
<p class="help-text">Minimum = legkisebb tételösszeg × legkisebb árfolyam, maximum = legnagyobb tételösszeg × legnagyobb árfolyam. A hitel biztosítása ugyanazzal az árfolyammal számolódik. Az árfolyamok felhasználónként külön tárolódnak.</p>
