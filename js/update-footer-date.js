/**
 * フッターの更新日を自動更新するスクリプト
 * サイトのファイル最終更新日（document.lastModified）や最新の更新情報を取得し、
 * フッターの <time> タグに自動反映します。
 */

(function() {
  'use strict';

  function formatJapaneseDate(date) {
    if (!date || isNaN(date.getTime())) return null;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}年${month}月${day}日`;
  }

  /**
   * フッターの更新日を反映する
   */
  function updateFooterDate() {
    const timeElement = document.querySelector('footer time');
    if (!timeElement) return;

    // document.lastModified からファイルの最終更新日時を取得して反映
    if (document.lastModified) {
      const lastModDate = new Date(document.lastModified);
      const formatted = formatJapaneseDate(lastModDate);
      if (formatted) {
        timeElement.textContent = formatted;
      }
    }
  }

  // ページ読み込み時に初期化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateFooterDate);
  } else {
    updateFooterDate();
  }

  window.updateFooterDate = updateFooterDate;
})();
