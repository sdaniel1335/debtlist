<?php
class auth extends app {
  public function __construct() { parent::__construct(true); }
  public function login() {
    if (auth_logged_in()) { $this->redirect('/'); }
    $this->set('title', 'Belépés · Debtlist'); $this->render('auth/login');
  }
  public function login_post() {
    $this->require_csrf();
    $username = post_text('username'); $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $stmt = $this->db->prepare('SELECT id, username, password_hash FROM ' . $this->table('users') . ' WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username); $stmt->execute(); $stmt->bind_result($user_id, $db_username, $password_hash);
    $valid = $stmt->fetch() && auth_password_check($password, $password_hash); $stmt->close();
    if (!$valid) { $this->flash('error', 'Hibás felhasználónév vagy jelszó.'); $this->redirect('/login'); }
    session_regenerate_id(true);
    $_SESSION['auth_user_id'] = (int) $user_id; $_SESSION['auth_username'] = $db_username;
    $this->redirect('/');
  }
  public function logout() {
    $this->require_csrf();
    unset($_SESSION['auth_user_id'], $_SESSION['auth_username'], $_SESSION['_auth_csrf']);
    session_regenerate_id(true); $this->redirect('/login');
  }
}
