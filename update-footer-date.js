/**
 * フッターの更新日を自動更新するスクリプト
 * 保存ボタンが押された時に、フッターの日付を本日の日付に変更する
 */

(function() {
  'use strict';

  /**
   * 日付をYYYY年MM月DD日形式でフォーマットする
   * @param {Date} date - フォーマットする日付
   * @returns {string} フォーマットされた日付文字列
   */
  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}年${month}月${day}日`;
  }

  /**
   * フッターの更新日を更新する
   */
  function updateFooterDate() {
    const timeElement = document.querySelector('footer time');
    if (timeElement) {
      const today = new Date();
      timeElement.textContent = formatDate(today);
      // ローカルストレージに最新の更新日を保存
      localStorage.setItem('lastUpdateDate', formatDate(today));
    }
  }

  /**
   * 初期化：ページ読み込み時に以前保存された日付を復元する
   */
  function initializeFooterDate() {
    const timeElement = document.querySelector('footer time');
    if (timeElement) {
      const savedDate = localStorage.getItem('lastUpdateDate');
      if (savedDate) {
        timeElement.textContent = savedDate;
      }
    }
  }

  // ページ読み込み時に初期化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFooterDate);
  } else {
    initializeFooterDate();
  }

  // シェアボタン（Modal16トリガー）のクリック時に日付を更新
  document.addEventListener('click', function(event) {
    // シェアボタン（ID: bc_share 相当）をクリック
    const shareButton = event.target.closest('a[data-bs-toggle="modal"][data-bs-target="#Modal16"]');
    if (shareButton) {
      updateFooterDate();
      return;
    }

    // Modal16内のコンテンツ保存ボタンなど（将来の拡張用）
    if (event.target.id === 'saveContentBtn' || event.target.classList.contains('save-content')) {
      updateFooterDate();
      return;
    }
  });

  // Garlic.jsの保存時（form submit時）に日付を更新
  // Garlic.jsのデフォルト動作では、フォーム送信時に保存される
  if (typeof $ !== 'undefined' && $.fn.garlic) {
    $(document).on('submit', 'form', function() {
      updateFooterDate();
    });
  }

  // グローバルに日付更新関数を公開（外部スクリプトから呼び出し可能にする）
  window.updateFooterDate = updateFooterDate;

})();
