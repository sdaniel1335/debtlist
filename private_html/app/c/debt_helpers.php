<?php
function h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function text_length($value) { return preg_match_all('/./us', $value, $matches); }
function money($value) { return number_format($value, 0, ',', ' '); }
function groups() { return array('B' => 'B', 'P' => 'P', 'W' => 'W'); }
function frequencies() { return array('weekly' => 'Hetente', 'monthly' => 'Havonta', 'quarterly' => 'Negyedévente', 'half_yearly' => 'Félévente', 'yearly' => 'Évente'); }
function valid_date($value) {
  if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) { return false; }
  $parts = explode('-', $value);
  return $parts[0] >= 1900 && $parts[0] <= 2200 && checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}
function month_date($date, $offset, $day) {
  $target = new DateTime(substr($date, 0, 7) . '-01');
  $target->modify(sprintf('%+d months', $offset));
  return $target->format('Y-m-') . sprintf('%02d', min($day, (int) $target->format('t')));
}
function period_start($today) {
  $day = max(1, min(28, DEBT_PERIOD_DAY));
  $start = substr($today, 0, 7) . '-' . sprintf('%02d', $day);
  return $today < $start ? month_date($start, -1, $day) : $start;
}
function item_end($item) {
  if ($item['kind'] === 'loan') { return month_date($item['first_due'], (int) $item['installments'] - 1, (int) substr($item['first_due'], 8, 2)); }
  return $item['end_date'];
}
// Half-open intervals count every due date in exactly one period.
function occurrence_count($item, $start, $until) {
  $end = item_end($item); $first = $item['first_due'];
  if ($first >= $until || ($end !== '' && $end !== null && $end < $start)) { return 0; }
  if ($item['frequency'] === 'weekly') {
    $due = new DateTime($first);
    if ($first < $start) {
      $distance = $due->diff(new DateTime($start));
      $weeks = (int) ceil($distance->days / 7);
      $due->modify('+' . ($weeks * 7) . ' days');
    }
    $count = 0;
    while ($due->format('Y-m-d') < $until) {
      if ($end !== '' && $end !== null && $due->format('Y-m-d') > $end) { break; }
      $count++; $due->modify('+7 days');
    }
    return $count;
  }
  $steps = array('monthly' => 1, 'quarterly' => 3, 'half_yearly' => 6, 'yearly' => 12);
  $step = $steps[$item['frequency']];
  $month_gap = ((int) substr($start, 0, 4) - (int) substr($first, 0, 4)) * 12 + (int) substr($start, 5, 2) - (int) substr($first, 5, 2);
  $offset = max(0, (int) floor($month_gap / $step) * $step);
  $count = 0; $day = (int) substr($first, 8, 2);
  while (true) {
    $due = month_date($first, $offset, $day);
    if ($due >= $until || ($end !== '' && $end !== null && $due > $end)) { break; }
    if ($due >= $start) { $count++; }
    $offset += $step;
  }
  return $count;
}
function item_totals($item, $rates, $start) {
  $day = max(1, min(28, DEBT_PERIOD_DAY));
  $count = occurrence_count($item, $start, month_date($start, 1, $day));
  $annual_count = occurrence_count($item, $start, month_date($start, 12, $day));
  $rate = isset($rates[$item['currency']]) ? $rates[$item['currency']] : null;
  $result = array('count' => $count, 'period' => array(), 'average' => array(), 'missing' => $rate === null);
  foreach (array('min', 'base', 'max') as $level) {
    $amount = (float) $item['amount_' . $level] + (float) $item['insurance'];
    $converted = $rate === null ? null : $amount * (float) $rate['rate_' . $level];
    $result['period'][$level] = $count === 0 ? 0 : ($converted === null ? null : $converted * $count);
    $result['average'][$level] = $annual_count === 0 ? 0 : ($converted === null ? null : $converted * $annual_count / 12);
  }
  return $result;
}
function post_text($key) { return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : ''; }
function decimal_value($value) {
  $value = str_replace(',', '.', $value);
  return preg_match('/^\d{1,9}(\.\d{1,4})?$/', $value) ? (float) $value : false;
}
function empty_totals() {
  return array('period' => array('min' => 0, 'base' => 0, 'max' => 0), 'average' => array('min' => 0, 'base' => 0, 'max' => 0));
}
function add_totals($total, $part) {
  foreach (array('period', 'average') as $type) {
    foreach (array('min', 'base', 'max') as $level) {
      $total[$type][$level] = $total[$type][$level] === null || $part[$type][$level] === null ? null : $total[$type][$level] + $part[$type][$level];
    }
  }
  return $total;
}
function display_total($value) { return $value === null ? 'Hiányzó árfolyam' : money($value) . ' Ft'; }
function amount_text($value) { return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.'); }
