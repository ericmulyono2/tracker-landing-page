<?php
// Stargroup Tracker Link — file JS yang di-embed di landing page.
// URL contoh: <script src="https://situsmu.com/stargroup-tracker/t.php?id=1" async></script>
// `id` = ID tracker (1..10) sesuai container di dashboard.

define('SG_TRACKER_INTERNAL', 1);
require __DIR__ . '/bootstrap.php';

header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=60');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { echo "/* Stargroup Tracker: missing ?id */"; exit; }

$base = sg_base_url();
$endpoint = $base . '/track.php';
$idJs   = (int)$id;
$endJs  = json_encode($endpoint, JSON_UNESCAPED_SLASHES);
?>
/* Stargroup Tracker Link — id=<?php echo $idJs; ?> */
(function () {
  if (window.__SG_TRACKER_LOADED__ && window.__SG_TRACKER_LOADED__[<?php echo $idJs; ?>]) return;
  window.__SG_TRACKER_LOADED__ = window.__SG_TRACKER_LOADED__ || {};
  window.__SG_TRACKER_LOADED__[<?php echo $idJs; ?>] = true;

  var TRACKER_ID = <?php echo $idJs; ?>;
  var ENDPOINT   = <?php echo $endJs; ?>;
  var STORE_KEY  = 'sg_vid';

  function uid() {
    try {
      var a = new Uint8Array(8);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var s = '';
      for (var i = 0; i < a.length; i++) s += ('0' + a[i].toString(16)).slice(-2);
      return s;
    } catch (e) {
      return Math.random().toString(36).slice(2, 10) + Date.now().toString(36);
    }
  }

  function visitorId() {
    try {
      var v = localStorage.getItem(STORE_KEY);
      if (!v) {
        v = uid();
        localStorage.setItem(STORE_KEY, v);
      }
      return v;
    } catch (e) {
      return uid();
    }
  }

  function send(type, extra) {
    var data = {
      id: TRACKER_ID,
      t:  type,
      v:  visitorId(),
      r:  document.referrer || '',
      u:  location.href,
      l:  (extra && extra.label) || ''
    };
    var payload = JSON.stringify(data);
    try {
      if (navigator.sendBeacon) {
        // text/plain = "simple" Content-Type → no CORS preflight
        var blob = new Blob([payload], { type: 'text/plain' });
        if (navigator.sendBeacon(ENDPOINT, blob)) return;
      }
    } catch (e) {}
    try {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', ENDPOINT, true);
      xhr.setRequestHeader('Content-Type', 'text/plain');
      xhr.send(payload);
      return;
    } catch (e) {}
    try {
      var img = new Image();
      var qs = '?p=1&id=' + encodeURIComponent(TRACKER_ID)
             + '&t=' + encodeURIComponent(type)
             + '&v=' + encodeURIComponent(data.v)
             + '&r=' + encodeURIComponent(data.r)
             + '&u=' + encodeURIComponent(data.u)
             + '&l=' + encodeURIComponent(data.l)
             + '&_=' + Date.now();
      img.src = ENDPOINT + qs;
    } catch (e) {}
  }

  function isCTA(el) {
    while (el && el !== document.body && el.nodeType === 1) {
      if (el.hasAttribute && el.hasAttribute('data-sg-no-track')) return null;
      var tag = (el.tagName || '').toLowerCase();
      if (tag === 'a' || tag === 'button') return el;
      if (el.hasAttribute && el.hasAttribute('data-sg-cta')) return el;
      if (el.classList && (el.classList.contains('sg-cta') || el.classList.contains('cta'))) return el;
      if (el.getAttribute && el.getAttribute('role') === 'button') return el;
      el = el.parentNode;
    }
    return null;
  }

  function bindClicks() {
    document.addEventListener('click', function (ev) {
      var t = ev.target;
      var cta = isCTA(t);
      if (!cta) return;
      var label = cta.getAttribute('data-sg-label')
               || (cta.innerText || cta.textContent || '').trim().slice(0, 80)
               || cta.getAttribute('href') || '';
      send('cta', { label: label });
    }, true);
  }

  function fireVisit() { send('visit'); }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { fireVisit(); bindClicks(); });
  } else {
    fireVisit();
    bindClicks();
  }

  window.SGTracker = window.SGTracker || {};
  window.SGTracker['t' + TRACKER_ID] = {
    visit: fireVisit,
    cta: function (label) { send('cta', { label: label || '' }); }
  };
})();
