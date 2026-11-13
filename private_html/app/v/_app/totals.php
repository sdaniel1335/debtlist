<div class="table-scroll totals"><table><thead><tr><th>Összesítés · HUF</th><th class="numeric">Minimum</th><th class="numeric">Alap</th><th class="numeric">Maximum</th></tr></thead><tbody>
<?php foreach (array('period' => 'Ebben az időszakban', 'average' => 'Havi átlag · 12 időszak') as $type => $label) { ?>
  <tr><th><?php echo h($label); ?></th><?php foreach (array('min' => 'Minimum', 'base' => 'Alap', 'max' => 'Maximum') as $level => $level_label) { ?><td data-label="<?php echo h($level_label); ?>" class="numeric<?php echo $level === 'base' ? ' base-total' : ''; ?>"><?php echo h(display_total($totals[$type][$level])); ?></td><?php } ?></tr>
<?php } ?>
</tbody></table></div>
