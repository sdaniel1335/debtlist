<?php
class pages extends app {
  protected function items() {
    return $this->rows('SELECT * FROM ' . $this->table('debt_items') . ' WHERE user_id = ? ORDER BY group_code, kind, name, id', auth_user_id());
  }
  protected function exchange_rates() {
    $rates = array('HUF' => array('currency' => 'HUF', 'rate_min' => 1, 'rate_base' => 1, 'rate_max' => 1));
    foreach ($this->rows('SELECT * FROM ' . $this->table('debt_rates') . ' WHERE user_id = ? ORDER BY currency', auth_user_id()) as $rate) { $rates[$rate['currency']] = $rate; }
    return $rates;
  }
  protected function find_item($id) {
    foreach ($this->items() as $item) { if ((string) $item['id'] === (string) $id) { return $item; } }
    header('HTTP/1.1 404 Not Found'); exit('A tétel nem található.');
  }
  public function index() {
    $requested = isset($_GET['month']) && is_string($_GET['month']) ? $_GET['month'] : '';
    $start = preg_match('/^\d{4}-\d{2}$/', $requested) && valid_date($requested . '-01')
      ? $requested . '-' . sprintf('%02d', max(1, min(28, DEBT_PERIOD_DAY))) : period_start(date('Y-m-d'));
    $until = month_date($start, 1, max(1, min(28, DEBT_PERIOD_DAY)));
    $end = new DateTime($until); $end->modify('-1 day');
    $this->set(array('title' => APP_TITLE, 'items' => $this->items(), 'rates' => $this->exchange_rates(), 'start' => $start, 'until' => $until, 'end' => $end->format('Y-m-d')));
    $this->render('pages/index');
  }
  public function edit() {
    $id = isset($_GET['id']) && is_string($_GET['id']) ? $_GET['id'] : '';
    $item = $id !== '' ? $this->find_item($id) : array('id' => '', 'name' => '', 'group_code' => 'P', 'kind' => 'expense', 'amount_min' => '', 'amount_base' => '', 'amount_max' => '', 'insurance' => '0', 'currency' => 'HUF', 'frequency' => 'monthly', 'first_due' => date('Y-m-d'), 'end_date' => '', 'installments' => '', 'notes' => '');
    $this->item_form($item, array());
  }
  protected function item_form($item, $errors) {
    $this->set(array('title' => $item['id'] ? 'Tétel szerkesztése' : 'Új tétel', 'item' => $item, 'errors' => $errors));
    $this->render('pages/item');
  }
  public function save() {
    $this->require_csrf(); $id = post_text('id');
    if ($id !== '') { $this->find_item($id); }
    $item = array('id' => $id);
    foreach (array('name', 'group_code', 'kind', 'amount_min', 'amount_base', 'amount_max', 'insurance', 'currency', 'frequency', 'first_due', 'end_date', 'installments', 'notes') as $key) { $item[$key] = post_text($key); }
    $item['currency'] = strtoupper($item['currency']); $errors = array();
    if ($item['name'] === '' || text_length($item['name']) === false || text_length($item['name']) > 200) { $errors[] = 'Adj meg egy legfeljebb 200 karakter hosszú nevet.'; }
    $groups = groups(); $frequencies = frequencies();
    if (!isset($groups[$item['group_code']])) { $errors[] = 'Válassz csoportot.'; }
    if (!in_array($item['kind'], array('loan', 'expense'), true)) { $errors[] = 'Válassz típust.'; }
    if (!preg_match('/^[A-Z]{3}$/', $item['currency'])) { $errors[] = 'A deviza három betűből álljon, például HUF, EUR vagy USD.'; }
    if ($item['kind'] === 'loan') { $item['frequency'] = 'monthly'; $item['end_date'] = ''; }
    else { $item['installments'] = ''; $item['insurance'] = '0'; }
    if (!isset($frequencies[$item['frequency']])) { $errors[] = 'Válassz gyakoriságot.'; }
    if (!valid_date($item['first_due'])) { $errors[] = 'Érvénytelen első esedékesség (1900–2200).'; }
    if ($item['end_date'] !== '' && (!valid_date($item['end_date']) || $item['end_date'] < $item['first_due'])) { $errors[] = 'A befejezés nem lehet korábbi az első esedékességnél.'; }
    if ($item['kind'] === 'loan' && (!preg_match('/^[1-9]\d{0,3}$/', $item['installments']) || (int) $item['installments'] > 1200)) { $errors[] = 'A törlesztőrészletek száma 1–1200 lehet.'; }
    foreach (array('amount_min', 'amount_max') as $key) { if ($item[$key] === '') { $item[$key] = $item['amount_base']; } }
    if ($item['insurance'] === '') { $item['insurance'] = '0'; }
    $amounts = array();
    foreach (array('amount_min', 'amount_base', 'amount_max', 'insurance') as $key) { $amounts[$key] = decimal_value($item[$key]); if ($amounts[$key] === false) { $errors[] = 'Az összegek nemnegatív számok legyenek, legfeljebb 4 tizedesjeggyel.'; break; } }
    if (count($amounts) === 4 && ($amounts['amount_min'] > $amounts['amount_base'] || $amounts['amount_base'] > $amounts['amount_max'])) { $errors[] = 'Az összegeknél minimum ≤ alap ≤ maximum szükséges.'; }
    if (text_length($item['notes']) === false || text_length($item['notes']) > 4000) { $errors[] = 'A megjegyzés legfeljebb 4000 karakter hosszú lehet.'; }
    if ($errors) { $this->item_form($item, array_unique($errors)); return; }
    $user_id = auth_user_id(); $installments = $item['installments'] === '' ? null : (int) $item['installments']; $end_date = $item['end_date'] === '' ? null : $item['end_date'];
    $sql = ($id === '' ? 'INSERT INTO ' : 'UPDATE ') . $this->table('debt_items') . ' SET name=?, group_code=?, kind=?, amount_min=?, amount_base=?, amount_max=?, insurance=?, currency=?, frequency=?, first_due=?, end_date=?, installments=?, notes=?, user_id=?';
    if ($id !== '') { $sql .= ' WHERE id=? AND user_id=?'; }
    $stmt = $this->db->prepare($sql);
    $args = array('sssddddssssisi', &$item['name'], &$item['group_code'], &$item['kind'], &$amounts['amount_min'], &$amounts['amount_base'], &$amounts['amount_max'], &$amounts['insurance'], &$item['currency'], &$item['frequency'], &$item['first_due'], &$end_date, &$installments, &$item['notes'], &$user_id);
    if ($id !== '') { $args[0] .= 'ii'; $args[] =& $id; $args[] =& $user_id; }
    call_user_func_array(array($stmt, 'bind_param'), $args); $stmt->execute(); $stmt->close();
    $this->flash('success', 'Tétel mentve.'); $this->redirect('/');
  }
  public function delete() {
    $this->require_csrf(); $id = post_text('id'); $this->find_item($id);
    if (post_text('confirm') !== 'yes') { $this->flash('error', 'A törléshez jelöld be a megerősítést.'); $this->redirect('/item?id=' . rawurlencode($id)); }
    $user_id = auth_user_id(); $stmt = $this->db->prepare('DELETE FROM ' . $this->table('debt_items') . ' WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $id, $user_id); $stmt->execute(); $stmt->close();
    $this->flash('success', 'Tétel törölve.'); $this->redirect('/');
  }
  public function rates() { $this->rate_form(array(), array()); }
  protected function rate_form($errors, $input) {
    $this->set(array('title' => 'Árfolyamok', 'rates' => $this->exchange_rates(), 'errors' => $errors, 'input' => $input)); $this->render('pages/rates');
  }
  public function save_rates() {
    $this->require_csrf(); $currency = strtoupper(post_text('currency')); $base = post_text('rate_base');
    $min = post_text('rate_min'); $max = post_text('rate_max');
    $input = array('currency' => $currency, 'rate_base' => $base, 'rate_min' => $min, 'rate_max' => $max);
    $base = decimal_value($base); $min = $min === '' ? $base : decimal_value($min); $max = $max === '' ? $base : decimal_value($max);
    if (!preg_match('/^[A-Z]{3}$/', $currency) || $currency === 'HUF' || $base === false || $min === false || $max === false || $min <= 0 || $min > $base || $base > $max) {
      $this->rate_form(array('Hárombetűs devizát és pozitív árfolyamokat adj meg: minimum ≤ alap ≤ maximum. A HUF árfolyama rögzített: 1.'), $input); return;
    }
    $user_id = auth_user_id();
    $stmt = $this->db->prepare('INSERT INTO ' . $this->table('debt_rates') . ' (user_id,currency,rate_min,rate_base,rate_max,updated_at) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE rate_min=VALUES(rate_min),rate_base=VALUES(rate_base),rate_max=VALUES(rate_max),updated_at=VALUES(updated_at)');
    $updated_at = date('Y-m-d H:i:s'); $stmt->bind_param('isddds', $user_id, $currency, $min, $base, $max, $updated_at); $stmt->execute(); $stmt->close();
    $this->flash('success', 'Árfolyam mentve.'); $this->redirect('/rates');
  }
}
