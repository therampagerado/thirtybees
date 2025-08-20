(function () {
  if (typeof window.tbAutoLoad === 'undefined') {
    return;
  }
  var cfg = window.tbAutoLoad;
  var container = document.querySelector(cfg.container || '#product_list');
  if (!container) {
    return;
  }
  var chunk = cfg.chunk;
  var minChunk = cfg.minChunk || 4;
  var threshold = cfg.threshold || 1000;
  var total = cfg.total || 0;
  var loaded = cfg.loaded || 0;

  function load() {
    if (loaded >= total) {
      return;
    }
    var page = Math.floor(loaded / chunk) + 1;
    var url = cfg.url.replace(/p=\d+/, 'p=' + page).replace(/n=\d+/, 'n=' + chunk);
    var start = performance.now();
    fetch(url, {credentials: 'same-origin'})
      .then(function (r) { return r.text(); })
      .then(function (html) {
        var tmp = document.createElement('div');
        tmp.innerHTML = html;
        var items = Array.prototype.slice.call(tmp.children);
        items.forEach(function (el) { container.appendChild(el); });
        loaded += items.length;
        var duration = performance.now() - start;
        if (duration > threshold && chunk > minChunk) {
          chunk = Math.max(minChunk, Math.floor(chunk / 2));
        }
        load();
      });
  }

  load();
})();
