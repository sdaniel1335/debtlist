<?php
// Render real application views with isolated fixtures; never exposed via HTTP.
if (PHP_SAPI !== 'cli') { header('HTTP/1.1 404 Not Found'); exit; }
define('ROOT', dirname(__DIR__)); define('DS', DIRECTORY_SEPARATOR);
define('PRIV', 'private_html'); define('APP', 'app'); define('BASE_URL', '');
define('FW_NAME', 'VC Framework'); define('FW_VER', '0.1.0');
define('FW_PROJECT_URL', 'https://github.com/dsoos1290/vcframework');
require ROOT . '/private_html/app/config/app.php';
require ROOT . '/private_html/app/config/debtlist.php';
require ROOT . '/private_html/framework/v.php';
require ROOT . '/private_html/app/c/debt_helpers.php';
require ROOT . '/private_html/app/c/auth_helpers.php';
date_default_timezone_set(APP_TZ);
$_SESSION = array('auth_user_id' => 1, 'auth_username' => 'demo', '_auth_csrf' => str_repeat('a', 64));
$rates = array('HUF' => array('currency' => 'HUF', 'rate_min' => 1, 'rate_base' => 1, 'rate_max' => 1), 'USD' => array('currency' => 'USD', 'rate_min' => 350, 'rate_base' => 360, 'rate_max' => 380));
$default = array('id' => 1, 'name' => 'Lakáshitel', 'group_code' => 'P', 'kind' => 'loan', 'amount_min' => 50000, 'amount_base' => 50000, 'amount_max' => 50000, 'insurance' => 3000, 'currency' => 'HUF', 'frequency' => 'monthly', 'first_due' => '2024-01-15', 'end_date' => '', 'installments' => 84, 'notes' => '');
$items = array($default,
  array_merge($default, array('id' => 2, 'name' => 'Netflix', 'kind' => 'expense', 'amount_min' => 15, 'amount_base' => 15, 'amount_max' => 15, 'insurance' => 0, 'currency' => 'USD', 'first_due' => '2026-01-20')),
  array_merge($default, array('id' => 3, 'name' => 'Irodatakarítás', 'group_code' => 'B', 'kind' => 'expense', 'frequency' => 'weekly', 'amount_min' => 8000, 'amount_base' => 10000, 'amount_max' => 12000, 'insurance' => 0, 'first_due' => '2026-01-05')),
  array_merge($default, array('id' => 4, 'name' => 'Domain és tárhely', 'group_code' => 'W', 'kind' => 'expense', 'frequency' => 'yearly', 'amount_min' => 24000, 'amount_base' => 24000, 'amount_max' => 24000, 'insurance' => 0, 'first_due' => '2026-11-22')),
  array_merge($default, array('id' => 5, 'name' => 'Gáz', 'kind' => 'expense', 'amount_min' => 12000, 'amount_base' => 18000, 'amount_max' => 25000, 'insurance' => 0, 'first_due' => '2026-01-10'))
);
$mode = isset($argv[1]) ? $argv[1] : 'index'; $title = 'Debtlist'; $start = '2026-11-15'; $until = '2026-12-15'; $end = '2026-12-14'; $errors = array(); $input = array(); $item = $default;
if ($mode === 'missing') { unset($rates['USD']); $mode = 'index'; }
if ($mode === 'empty') { $items = array(); $mode = 'index'; }
if ($mode === 'expense') { $item = $items[1]; $mode = 'item'; }
if ($mode === 'login') { $_SESSION = array('_auth_csrf' => str_repeat('a', 64)); }
if (!in_array($mode, array('index', 'item', 'rates', 'login'), true)) { exit(1); }
ob_start(); require ROOT . '/private_html/app/v/' . ($mode === 'login' ? 'auth/login' : 'pages/' . $mode) . '.php'; $content = ob_get_clean();
require ROOT . '/private_html/app/v/layout.php';
