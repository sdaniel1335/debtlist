<?php
// 1–28: each period starts on this day and ends before the next start.
define('DEBT_PERIOD_DAY', 15);
// Match sibling apps: same cookie name/path, session storage and host.
if (PHP_SAPI !== 'cli' && session_id() === '') {
  session_name('PHPSESSID');
  ini_set('session.cookie_path', '/');
  ini_set('session.cookie_httponly', '1');
}
