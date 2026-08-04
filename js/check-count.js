// チェック数集計＆表示
(function() {
  function updateCounts() {
    $('.accordion-item').each(function() {
      const itemsDiv = $(this).find('div[id^="items"]');
      if (!itemsDiv.length) return;
      
      const countSpan = $(this).find('.check-count');
      if (!countSpan.length) return;

      const total = itemsDiv.find('input[type="checkbox"]').length;
      if (total === 0) {
        countSpan.empty();
        return;
      }

      const checked = itemsDiv.find('input[type="checkbox"]:checked').length;

      if (checked === total) {
        countSpan.html('<span class="badge badge-success" style="background-color:#28a745;color:#fff;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-size:1em;">完</span>');
      } else {
        countSpan.html('<span class="badge badge-secondary" style="background-color:#1b3a2f;color:#fff;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-size:1em;">' + (total - checked) + '</span>');
      }
    });
  }

  function initCheckCount() {
    updateCounts();
    $(document).off('change.checkcount', 'input[type="checkbox"]').on('change.checkcount', 'input[type="checkbox"]', updateCounts);
  }

  $(document).ready(initCheckCount);
  document.addEventListener('checklistRendered', initCheckCount);
})();

