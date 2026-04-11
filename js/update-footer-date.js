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
    // Static mode: API is not available after static conversion.
    // Keep existing footer time if present; avoid runtime API fetch to prevent console errors.
    const timeElement = document.querySelector('footer time');
    if (timeElement && timeElement.textContent) {
      setFooterDateText(timeElement.textContent.trim());
    }
    return Promise.resolve();
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
