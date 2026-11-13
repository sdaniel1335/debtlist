<?php
$group_items = array('B' => array(), 'P' => array(), 'W' => array());
$group_totals = array(); $overall = empty_totals(); $missing = array();
foreach ($items as $item) {
  $item['totals'] = item_totals($item, $rates, $start);
  $group_items[$item['group_code']][] = $item;
  if ($item['totals']['missing']) { $missing[$item['currency']] = true; }
}
foreach (groups() as $code => $label) {
  $total = empty_totals();
  foreach ($group_items[$code] as $item) { $total = add_totals($total, $item['totals']); }
  $group_totals[$code] = $total; $overall = add_totals($overall, $total);
}
$day = max(1, min(28, DEBT_PERIOD_DAY)); $freq_labels = frequencies();
?>
<div class="page-heading">
  <div><span class="eyebrow">Hitelek és rendszeres kiadások</span><h1>Minden egy helyen.</h1><p class="muted">Három csoport. Egy átlátható terv.</p></div>
  <a class="button" href="<?php echo url('/item'); ?>">+ Új tétel</a>
</div>
<section class="period-bar panel" aria-label="Időszakválasztó">
  <div><span class="eyebrow">Kiválasztott időszak</span><strong><?php echo h($start . ' → ' . $end); ?></strong></div>
  <div class="period-controls">
    <a class="button secondary" aria-label="Előző időszak" href="<?php echo url('/?month=' . substr(month_date($start, -1, $day), 0, 7)); ?>">←</a>
    <form method="get" action="<?php echo url('/'); ?>" class="inline-form"><label class="sr-only" for="month">Kezdő hónap</label><input id="month" type="month" name="month" value="<?php echo h(substr($start, 0, 7)); ?>" min="1900-01" max="2200-12" required><button class="secondary" type="submit">Mutat</button></form>
    <a class="button secondary" aria-label="Következő időszak" href="<?php echo url('/?month=' . substr($until, 0, 7)); ?>">→</a>
    <a href="<?php echo url('/'); ?>">Most</a>
  </div>
</section>
<?php if ($missing) { ?><div class="alert alert-danger">Hiányzó árfolyam: <?php echo h(implode(', ', array_keys($missing))); ?>. Az érintett HUF-összesítések nem számíthatók. <a href="<?php echo url('/rates'); ?>">Árfolyam megadása →</a></div><?php } ?>
<div class="summary-grid">
<?php foreach (groups() as $code => $label) { ?>
  <a class="summary-card panel group-<?php echo h($code); ?>" href="#group-<?php echo h($code); ?>">
    <span class="group-badge"><?php echo h($code); ?></span>
    <strong><?php echo h(display_total($group_totals[$code]['period']['base'])); ?></strong>
    <small class="muted">az időszakban · <?php echo count($group_items[$code]); ?> tétel</small>
  </a>
<?php } ?>
</div>
<?php foreach (groups() as $code => $label) { ?>
<section id="group-<?php echo h($code); ?>" class="panel group-section group-<?php echo h($code); ?>">
  <div class="section-heading"><h2><span class="group-badge"><?php echo h($code); ?></span></h2><span class="muted"><?php echo count($group_items[$code]); ?> tétel</span></div>
  <?php if (!$group_items[$code]) { ?><p class="empty-state">Még nincs tétel ebben a csoportban. <a href="<?php echo url('/item'); ?>">Új tétel felvétele</a></p><?php } else { ?>
  <div class="table-scroll"><table class="items-table"><thead><tr><th>Tétel</th><th>Gyakoriság / esedékesség</th><th class="numeric">Alkalmanként</th><th class="numeric">Időszak · alap</th><th class="numeric">Havi átlag · alap</th><th><span class="sr-only">Szerkesztés</span></th></tr></thead><tbody>
  <?php foreach (array('loan' => 'Hitelek', 'expense' => 'Rezsi és előfizetések') as $kind => $kind_label) { $has_kind = false; foreach ($group_items[$code] as $item) { if ($item['kind'] === $kind) { $has_kind = true; } } if (!$has_kind) { continue; } ?>
    <tr class="category-row"><th colspan="6"><?php echo h($kind_label); ?></th></tr>
    <?php foreach ($group_items[$code] as $item) { if ($item['kind'] !== $kind) { continue; } $item_end = item_end($item); $expired = $item_end && $item_end < $start; ?>
    <tr<?php echo $expired ? ' class="expired"' : ''; ?>>
      <td><strong><?php echo h($item['name']); ?></strong>
        <?php if ($expired) { ?><span class="tag">Lejárt</span><?php } ?>
        <?php if ($item['kind'] === 'loan') { $remaining = occurrence_count($item, max($start, date('Y-m-d')), month_date($item_end, 1, 1)); ?><small><?php echo h($item['installments']); ?> részlet · <?php echo h($item_end); ?>-ig<br>Hátralévő: <?php echo $remaining; ?> részlet<?php if ((float) $item['insurance'] > 0) { ?><br>+ <?php echo h(amount_text($item['insurance']) . ' ' . $item['currency']); ?> biztosítás / hó<?php } ?></small><?php } ?>
        <?php if ($item['notes'] !== '') { ?><small class="note"><?php echo nl2br(h($item['notes'])); ?></small><?php } ?>
      </td>
      <td><?php echo h($freq_labels[$item['frequency']]); ?><small><?php if ($item['frequency'] === 'weekly') { $weekdays = array(1 => 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat', 'vasárnap'); $first_date = new DateTime($item['first_due']); echo h($weekdays[(int) $first_date->format('N')]); } else { echo (int) substr($item['first_due'], 8, 2); ?>. nap<?php } ?> · <?php echo $item['totals']['count']; ?> alkalom itt<br>Első: <?php echo h($item['first_due']); ?><?php if ($item['kind'] !== 'loan' && $item_end) { ?><br>Vége: <?php echo h($item_end); ?><?php } ?></small></td>
      <td data-label="Alkalmanként" class="numeric"><?php echo h(amount_text($item['amount_base']) . ' ' . $item['currency']); ?><?php if ($item['amount_min'] != $item['amount_max']) { ?><small><?php echo h(amount_text($item['amount_min']) . ' – ' . amount_text($item['amount_max'])); ?></small><?php } ?></td>
      <td data-label="Időszak · alap" class="numeric"><strong><?php echo h(display_total($item['totals']['period']['base'])); ?></strong></td>
      <td data-label="Havi átlag · alap" class="numeric"><?php echo h(display_total($item['totals']['average']['base'])); ?></td>
      <td><a href="<?php echo url('/item?id=' . (int) $item['id']); ?>">Szerkesztés</a></td>
    </tr>
    <?php } ?>
  <?php } ?>
  </tbody></table></div>
  <?php } ?>
  <?php element('totals', array('totals' => $group_totals[$code])); ?>
</section>
<?php } ?>
<section class="panel overall"><div class="section-heading"><h2>Mindösszesen</h2><span>B + P + W</span></div><?php element('totals', array('totals' => $overall)); ?></section>
<p class="help-text">A havi átlag a kiválasztott időszaktól induló 12 időszak összegének tizenketted része. A kezdődő és lejáró tételeket is figyelembe veszi. A heti díjakat a tényleges alkalmak szerint számoljuk. Minden becslés a jelenleg megadott összegekkel és árfolyamokkal készül.</p>
<details class="panel forecast"><summary>12 időszak előretekintése</summary><div class="table-scroll"><table><thead><tr><th>Időszak</th><th class="numeric">B</th><th class="numeric">P</th><th class="numeric">W</th><th class="numeric">Összesen · alap</th></tr></thead><tbody>
<?php for ($index = 0; $index < 12; $index++) { $future_start = month_date($start, $index, $day); $future_totals = array('B' => empty_totals(), 'P' => empty_totals(), 'W' => empty_totals()); $future_overall = empty_totals(); foreach ($items as $item) { $future_totals[$item['group_code']] = add_totals($future_totals[$item['group_code']], item_totals($item, $rates, $future_start)); } ?>
<tr><td><a href="<?php echo url('/?month=' . substr($future_start, 0, 7)); ?>"><?php echo h($future_start); ?>-től</a></td><?php foreach ($future_totals as $future_total) { $future_overall = add_totals($future_overall, $future_total); ?><td class="numeric"><?php echo h(display_total($future_total['period']['base'])); ?></td><?php } ?><td class="numeric"><strong><?php echo h(display_total($future_overall['period']['base'])); ?></strong></td></tr>
<?php } ?>
</tbody></table></div></details>
