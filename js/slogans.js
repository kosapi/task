// Slogan rendering extracted from index.html
(function() {
  var defaultSlogans = [
    '横臥者を 早期発見 ハイビーム',
    '来ないだろう 決めつけないで 目で確認',
    '発進時 シートベルトの お声がけ',
    '自転車・二輪車の 急な飛び出し 死角から',
    '降りて見る そのひと手間が 防ぐ事故',
    '誤発進 防ぐ こまめな Pレンジ',
    '交差点 止まる心と 待つ気持ち'
  ];

  function showTodaySlogan(dayIndex) {
    var weekdayIds = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdayIds.forEach(function(id, idx) {
      var el = document.getElementById(id);
      if (!el) return;
      el.style.display = (idx === dayIndex) ? 'block' : 'none';
    });
    var sync = document.getElementById('slogan-sync');
    if (sync) sync.style.display = 'none';
  }

  function updateAllSlogans(slogans) {
    if (!slogans || !Array.isArray(slogans)) return;
    var today = new Date();
    var dayIndex = today.getDay();

    var syncEl = document.getElementById('slogan-sync-text');
    if (syncEl) syncEl.textContent = slogans[dayIndex] || '';

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
      initialSlogans = JSON.parse(dataEl.textContent);
    } catch (e) {
      initialSlogans = defaultSlogans;
    }

    updateAllSlogans(initialSlogans);

    fetch('/task/api/get-content.php')
      .then(function(response) {
        if (!response.ok) throw new Error('API error');
        return response.json();
      })
      .then(function(data) {
        if (data.slogans && Array.isArray(data.slogans) && data.slogans.length > 0) {
          dataEl.textContent = JSON.stringify(data.slogans);
          updateAllSlogans(data.slogans);
          window.cmsDataLoaded = true;
          window.cmsData = data;
        }
      })
      .catch(function(err) {
        console.warn('CMS data load failed, using default:', err);
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
