<?php
require_once __DIR__ . '/auth_helpers.php';
require_once __DIR__ . '/debt_helpers.php';
class app extends C {
  public function __construct($public = false) {
    parent::__construct();
    if (!$public && !auth_logged_in()) { $this->redirect('/login'); }
  }
  protected function require_csrf() {
    if (!auth_csrf_valid(isset($_POST['_csrf']) ? $_POST['_csrf'] : null)) {
      header('HTTP/1.1 400 Bad Request'); exit('Érvénytelen űrlap. Frissítsd az oldalt.');
    }
  }
  protected function rows($sql, $user_id) {
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('i', $user_id); $stmt->execute();
    $meta = $stmt->result_metadata(); $values = array(); $refs = array();
    while ($field = $meta->fetch_field()) { $values[$field->name] = null; $refs[] =& $values[$field->name]; }
    call_user_func_array(array($stmt, 'bind_result'), $refs);
    $rows = array();
    while ($stmt->fetch()) { $row = array(); foreach ($values as $key => $value) { $row[$key] = $value; } $rows[] = $row; }
    $stmt->close(); return $rows;
  }
}
