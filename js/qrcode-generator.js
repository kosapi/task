/**
 * QRコード生成スクリプト
 * シェアボタン（Modal16）でページのURLのQRコードを生成
 */

(function() {
  'use strict';

  /**
   * QRコードを生成する
   * QRCode.js ライブラリを使用
   */
  function generateQRCode() {
    const container = document.getElementById('qrcode-container');
    if (!container) {
      console.warn('[QRCode] QRコードコンテナが見つかりません');
      return;
    }

    // 既存のQRコードをクリア
    container.innerHTML = '';

    // 現在のページのURLを取得
    const currentURL = window.location.href;

    // QRCode.jsが読み込まれているか確認
    if (typeof QRCode === 'undefined') {
      container.innerHTML = '<p class="text-danger">QRコードライブラリが読み込まれていません</p>';
      console.error('[QRCode] QRCode.js が読み込まれていません');
      return;
    }

    try {
      // QRコードを生成
      new QRCode(container, {
        text: currentURL,
        width: 256,
        height: 256,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
      });
      console.log('[QRCode] QRコード生成完了:', currentURL);
    } catch (error) {
      container.innerHTML = '<p class="text-danger">QRコードの生成に失敗しました</p>';
      console.error('[QRCode] QRコード生成エラー:', error);
    }
  }

  /**
   * モーダルが表示された時にQRコードを生成
   */
  function setupQRCodeModal() {
    const modal = document.getElementById('Modal16');
    if (!modal) {
      console.warn('[QRCode] Modal16が見つかりません');
      return;
    }

    // Bootstrapのモーダルイベントをリッスン
    modal.addEventListener('shown.bs.modal', function() {
      console.log('[QRCode] モーダルが表示されました - QRコード生成開始');
      generateQRCode();
    });

    console.log('[QRCode] QRコードモーダルのイベントリスナーを設定しました');
  }

  // DOM読み込み完了後に初期化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupQRCodeModal);
  } else {
    setupQRCodeModal();
  }

})();
