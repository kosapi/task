// Slogan rendering extracted from index.html
(function() {
  var defaultSlogans = ['', '', '', '', '', '', ''];

  function showTodaySlogan(dayIndex) {
    var weekdayIds = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdayIds.forEach(function(id, idx) {
      var el = document.getElementById(id);
      if (!el) return;
      el.style.display = (idx === dayIndex) ? 'block' : 'none';
    });
  }

  function updateAllSlogans(slogans) {
    if (!slogans || !Array.isArray(slogans)) return;
    var dayIndex = new Date().getDay();

    var weekdayIds = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdayIds.forEach(function(id, idx) {
      var el = document.getElementById(id);
      if (!el) return;
      var p = el.querySelector('p.text-center');
      if (p) p.textContent = slogans[idx] || '';
    });

    showTodaySlogan(dayIndex);
  }

  function init() {
    var dataEl = document.getElementById('slogans-data');
    if (!dataEl) return;

    var initialSlogans = defaultSlogans;
    try {
      var parsed = JSON.parse(dataEl.textContent);
      if (Array.isArray(parsed) && parsed.length > 0) {
        initialSlogans = parsed;
      }
    } catch (e) {
      initialSlogans = defaultSlogans;
    }

    updateAllSlogans(initialSlogans);

    // Static mode: do not fetch CMS API when using pre-rendered slogans-data
    // If you want runtime updates, remove this guard and re-enable fetch.
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
