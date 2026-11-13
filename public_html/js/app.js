(function () {
  var kind = document.getElementById('kind');
  if (!kind) { return; }
  function update_fields() {
    var loan = kind.value === 'loan';
    var fields = document.querySelectorAll('[data-loan], [data-expense]');
    for (var index = 0; index < fields.length; index++) {
      var visible = fields[index].hasAttribute('data-loan') ? loan : !loan;
      fields[index].hidden = !visible;
      var controls = fields[index].querySelectorAll('input, select');
      for (var control_index = 0; control_index < controls.length; control_index++) {
        controls[control_index].disabled = !visible;
        if (controls[control_index].name === 'installments') { controls[control_index].required = loan; }
      }
    }
  }
  kind.addEventListener('change', update_fields);
  update_fields();
}());
