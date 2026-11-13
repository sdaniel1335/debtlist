<?php
// Run with PHP 5.3+; no database or dependencies required.
define('DEBT_PERIOD_DAY', 15);
date_default_timezone_set('Europe/Budapest');
require dirname(__DIR__) . '/private_html/app/c/debt_helpers.php';
require dirname(__DIR__) . '/private_html/app/c/auth_helpers.php';
$checks = 0;
function check($actual, $expected, $label) {
  global $checks;
  $checks++;
  if ($actual !== $expected) {
    fwrite(STDERR, 'FAIL: ' . $label . ' got ' . var_export($actual, true) . ', expected ' . var_export($expected, true) . "\n");
    exit(1);
  }
}
$item = array('kind' => 'expense', 'first_due' => '2026-01-15', 'end_date' => null, 'frequency' => 'monthly', 'amount_min' => 90, 'amount_base' => 100, 'amount_max' => 110, 'insurance' => 0, 'currency' => 'HUF');
$rates = array('HUF' => array('rate_min' => 1, 'rate_base' => 1, 'rate_max' => 1), 'USD' => array('rate_min' => 350, 'rate_base' => 360, 'rate_max' => 370));
check(period_start('2026-11-14'), '2026-10-15', 'day before boundary');
check(period_start('2026-11-15'), '2026-11-15', 'day of boundary');
check(period_start('2026-01-01'), '2025-12-15', 'year rollover');
check(month_date('2024-01-31', 1, 31), '2024-02-29', 'leap February');
check(month_date('2025-01-31', 1, 31), '2025-02-28', 'February clamp');
check(month_date('2025-01-31', 2, 31), '2025-03-31', 'original day restored');
check(occurrence_count($item, '2026-02-15', '2026-03-15'), 1, 'boundary counted once');
check(occurrence_count($item, '2026-02-14', '2026-02-15'), 0, 'exclusive upper boundary');
check(occurrence_count($item, '2026-02-15', '2027-02-15'), 12, '12 monthly payments');
foreach (array('quarterly' => 4, 'half_yearly' => 2, 'yearly' => 1) as $frequency => $expected) {
  $item['frequency'] = $frequency;
  check(occurrence_count($item, '2026-01-15', '2027-01-15'), $expected, $frequency);
}
$item['frequency'] = 'quarterly'; $item['first_due'] = '2025-12-31';
check(occurrence_count($item, '2026-03-15', '2026-04-15'), 1, 'quarterly anchor month');
check(occurrence_count($item, '2026-02-15', '2026-03-15'), 0, 'quarterly off month');
$item['frequency'] = 'weekly'; $item['first_due'] = '2026-01-05';
check(occurrence_count($item, '2026-02-15', '2026-03-15'), 4, 'four Mondays');
check(occurrence_count($item, '2026-03-15', '2026-04-15'), 5, 'five Mondays across DST');
check(occurrence_count($item, '2026-10-15', '2026-11-15'), 4, 'weekly across fall DST');
$item['end_date'] = '2026-03-23';
check(occurrence_count($item, '2026-03-15', '2026-04-15'), 2, 'inclusive end date');
$item['end_date'] = null; $item['first_due'] = '2027-01-05';
check(occurrence_count($item, '2026-02-15', '2026-03-15'), 0, 'future start');
$item['kind'] = 'loan'; $item['frequency'] = 'monthly'; $item['first_due'] = '2024-01-31'; $item['installments'] = 3; $item['insurance'] = 10;
check(item_end($item), '2024-03-31', 'loan final due date');
check(occurrence_count($item, '2024-01-01', '2025-01-01'), 3, 'loan installment cap');
check(occurrence_count($item, '2024-04-15', '2024-05-15'), 0, 'expired loan');
$totals = item_totals($item, $rates, '2024-01-15');
check($totals['period']['base'], 110.0, 'insurance added');
check($totals['average']['base'], 27.5, 'expiring loan averaged over 12 periods');
$item['currency'] = 'USD'; $totals = item_totals($item, $rates, '2024-01-15');
check($totals['period']['min'], 35000.0, 'minimum amount times minimum FX including insurance');
check($totals['period']['base'], 39600.0, 'base amount times base FX');
check($totals['period']['max'], 44400.0, 'maximum amount times maximum FX');
$item['currency'] = 'EUR'; $totals = item_totals($item, $rates, '2024-01-15');
check($totals['period']['base'], null, 'missing FX is not zero');
$combined = add_totals(empty_totals(), $totals);
check($combined['period']['base'], null, 'missing FX propagates to total');
$totals = item_totals($item, $rates, '2025-01-15');
check($totals['period']['base'], 0, 'expired item needs no FX');
check(decimal_value('12,34'), 12.34, 'decimal comma');
check(decimal_value('-1'), false, 'negative amount rejected');
check(decimal_value('1e6'), false, 'scientific notation rejected');
check(decimal_value('1000000000'), false, 'database decimal bound');
check(valid_date('2026-02-29'), false, 'invalid date');
check(valid_date('2024-02-29'), true, 'valid leap date');
check(valid_date(array()), false, 'array input');
check(h('<script>"'), '&lt;script&gt;&quot;', 'HTML escaping');
check(auth_password_check('admin', '$2y$12$EOg47Nwzf6dRaggt3cx6YuD9v6p.pa.vJy96i1x/ZvsQGBr8uYkNe'), true, 'shared existing bcrypt account');
check(auth_password_check('wrong', '$2y$12$EOg47Nwzf6dRaggt3cx6YuD9v6p.pa.vJy96i1x/ZvsQGBr8uYkNe'), false, 'wrong password');
$legacy_hash = 'pbkdf2_sha256$1000$test$' . bin2hex(auth_pbkdf2('sample', 'test', 1000, 32));
check(auth_password_check('sample', $legacy_hash), true, 'shared legacy password format');
$_SESSION = array(); $token = auth_csrf_token();
check(strlen($token), 64, 'CSRF random token length');
check(auth_csrf_valid($token), true, 'valid CSRF');
check(auth_csrf_valid('invalid'), false, 'invalid CSRF');
check(auth_csrf_valid(array()), false, 'malformed CSRF');
echo 'OK: ' . $checks . " checks\n";
