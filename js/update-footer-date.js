/**
 * フッターの更新日を自動更新するスクリプト
 * APIの updated_at を表示して、内容変更時のみ更新日を反映する
 */

(function() {
  'use strict';

  function setFooterDateText(dateText) {
    const timeElement = document.querySelector('footer time');
    if (!timeElement || !dateText) return;
    timeElement.textContent = dateText;
  }

  /**
   * フッターの更新日をAPIから取得して反映する
   */
  function updateFooterDateFromApi() {
    return fetch('/task/api/get-content.php?_t=' + Date.now(), { cache: 'no-store' })
      .then(function(response) {
        if (!response.ok) {
          throw new Error('API error');
        }
        return response.json();
      })
      .then(function(data) {
        if (data && typeof data.updated_at === 'string' && data.updated_at.trim() !== '') {
          setFooterDateText(data.updated_at.trim());
          return;
        }

        // 後方互換: updated_at が無い場合は既存表示を維持
        const timeElement = document.querySelector('footer time');
        if (timeElement && timeElement.textContent) {
          setFooterDateText(timeElement.textContent.trim());
        }
      })
      .catch(function(err) {
        console.warn('Footer date API load failed:', err);
      });
  }

  /**
   * 初期化：ページ読み込み時にAPIの更新日を反映
   */
  function initializeFooterDate() {
    updateFooterDateFromApi();
  }

  // ページ読み込み時に初期化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFooterDate);
  } else {
    initializeFooterDate();
  }

  window.refreshFooterDateFromApi = updateFooterDateFromApi;

})();
