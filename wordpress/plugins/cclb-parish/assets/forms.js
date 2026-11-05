/* Join / Harvest forms: show the right section, add or remove children. No dependencies. */
(function () {
  document.querySelectorAll('form[data-pform]').forEach(function (form) {
    var sections = form.querySelectorAll('[data-show]');

    function setSection(el, on) {
      el.hidden = !on;
      el.querySelectorAll('input, select, textarea, button[type=submit]').forEach(function (f) { f.disabled = !on; });
    }
    function update() {
      var type = (form.querySelector('input[name=reg_type]:checked') || {}).value;
      sections.forEach(function (el) {
        var wants = el.getAttribute('data-show').split(' ');
        setSection(el, !!type && wants.indexOf(type) !== -1);
      });
    }
    function number() {
      form.querySelectorAll('.child').forEach(function (c, i) {
        var n = c.querySelector('[data-n]'); if (n) n.textContent = i + 1;
        var rm = c.querySelector('[data-remove]'); if (rm) rm.hidden = i === 0;
      });
    }
    if (sections.length) {
      form.addEventListener('change', function (e) { if (e.target.name === 'reg_type') update(); });
      update();
    }
    var rep = form.querySelector('[data-repeater]');
    if (rep) {
      var add = rep.querySelector('[data-add]');
      var rows = rep.querySelector('[data-rows]');
      var tpl = rep.querySelector('template[data-template]');
      add.hidden = false;
      add.addEventListener('click', function () {
        var wrap = document.createElement('div');
        wrap.innerHTML = tpl.innerHTML.split('__i__').join(Date.now().toString(36) + Math.floor(Math.random() * 1000));
        while (wrap.firstElementChild) rows.appendChild(wrap.firstElementChild);
        number();
        var last = rows.lastElementChild.querySelector('input'); if (last) last.focus();
      });
      rep.addEventListener('click', function (e) {
        var rm = e.target.closest('[data-remove]');
        if (rm) { rm.closest('[data-row]').remove(); number(); }
      });
      number();
    }
  });
  var top = document.getElementById('form-top');
  if (top && top.classList.contains('form-card--sent')) top.focus();
})();
