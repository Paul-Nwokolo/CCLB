/* Parish Details: add/remove rows, pick photos. No dependencies. */
(function () {
  document.addEventListener('click', function (e) {
    var t = e.target;
    if (t.matches('[data-add]')) {
      var rep = t.closest('[data-repeater]');
      var tpl = rep.querySelector('template[data-template]');
      var rows = rep.querySelector('[data-rows]');
      var idx = Date.now().toString(36) + Math.floor(Math.random() * 1000);
      var holder = document.createElement('div');
      holder.innerHTML = tpl.innerHTML.split('__i__').join(idx);
      while (holder.firstElementChild) rows.appendChild(holder.firstElementChild);
    } else if (t.matches('[data-remove]')) {
      var row = t.closest('[data-row]');
      if (row) row.remove();
    } else if (t.matches('[data-pick]')) {
      var input = t.closest('[data-row]').querySelector('[data-image]');
      var frame = wp.media({ title: 'Choose a parish photo', multiple: false, library: { type: 'image' } });
      frame.on('select', function () {
        var a = frame.state().get('selection').first().toJSON();
        input.value = (a.sizes && a.sizes.large ? a.sizes.large.url : a.url);
      });
      frame.open();
    }
  });
})();
