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

    fetch('/task/api/get-content.php?_t=' + Date.now(), { cache: 'no-store' })
      .then(function(response) {
        if (!response.ok) throw new Error('API error');
        return response.json();
      })
      .then(function(data) {
        if (data.slogans && Array.isArray(data.slogans) && data.slogans.length > 0) {
          dataEl.textContent = JSON.stringify(data.slogans);
          updateAllSlogans(data.slogans);
        }
      })
      .catch(function(err) {
        console.warn('CMS data load failed, using inline slogans-data:', err);
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
