/**
 * 本番デプロイ前 動作テストガイド
 * ブラウザコンソールで動作確認する項目
 */

window.DeploymentChecklist = {
  // 1. 環境判定確認
  testEnvironment: function() {
    console.log('=== 環境判定テスト ===');
    console.log('ENVIRONMENT:', window.ENVIRONMENT);
    console.log('BASE_URL:', window.BASE_URL);
    console.log('isProduction:', window.ENV.isProduction());
    console.log('isLocal:', window.ENV.isLocal());
    console.log('isStaging:', window.ENV.isStaging());
    if (window.ENVIRONMENT === 'production') {
      console.warn('⚠️ 本番環境です！');
    }
  },

  // 2. デバッグログ制御確認
  testDebugConfig: function() {
    console.log('=== デバッグログ制御テスト ===');
    console.log('DEBUG.enabled:', window.DEBUG.enabled);
    DEBUG.log('✅ このメッセージは開発/ステージング環境でのみ表示されます');
    if (!window.DEBUG.enabled && window.ENVIRONMENT === 'production') {
      console.log('✅ 本番環境ではログが抑制されています');
    }
  },

  // 3. モーダル開閉テスト
  testModals: function() {
    console.log('=== モーダルテスト ===');
    const modals = document.querySelectorAll('.modal');
    console.log('見つかったモーダル数:', modals.length);
    modals.forEach(m => {
      console.log('Modal ID:', m.id, 'Display:', window.getComputedStyle(m).display);
    });
  },

  // 4. アコーディオンテスト
  testAccordions: function() {
    console.log('=== アコーディオンテスト ===');
    const accordion = document.getElementById('accordion');
    if (accordion) {
      const items = accordion.querySelectorAll('.accordion-item');
      console.log('アコーディオンアイテム数:', items.length);
      console.log('✅ アコーディオン要素が存在します');
    } else {
      console.warn('⚠️ accordion が見つかりません');
    }
  },

  // 5. 進捗バーテスト
  testProgressBars: function() {
    console.log('=== 進捗バーテスト ===');
    const progressBars = document.querySelectorAll('[class^="progress-bar"]');
    console.log('見つかった進捗バー:', progressBars.length);
    progressBars.forEach((pb, i) => {
      const checkboxes = document.querySelectorAll(`#items${i} input[type="checkbox"]`);
      const checked = document.querySelectorAll(`#items${i} input[type="checkbox"]:checked`);
      console.log(`progress-bar${i}: 全${checkboxes.length}, チェック${checked.length}`);
    });
  },

  // 6. チェックリスト検索UI確認
  testChatAssistant: function() {
    console.log('=== チェックリスト検索UIテスト ===');
    const chatBtn = document.getElementById('chat-toggle-btn');
    const chatContainer = document.getElementById('checklist-chat-assistant');
    if (chatBtn) {
      console.log('✅ チャットボタン存在');
      console.log('表示:', window.getComputedStyle(chatBtn).display);
    }
    if (chatContainer) {
      console.log('✅ チャットコンテナ存在');
    }
    if (window.ChecklistChatAssistant) {
      console.log('✅ ChecklistChatAssistantクラス読み込み完了');
    }
  },

  // 7. ハッシュナビゲーション確認
  testHashNavigation: function() {
    console.log('=== ハッシュナビゲーションテスト ===');
    console.log('現在のハッシュ:', window.location.hash);
    if (window.HashNav) {
      console.log('✅ HashNav オブジェクト存在');
    }
    if (typeof window.testAccordion === 'function') {
      console.log('✅ testAccordion関数利用可能');
      console.log('使用方法: window.testAccordion("collapse0") など');
    }
  },

  // 8. エラーログ確認
  testErrorLogging: function() {
    console.log('=== エラーログテスト ===');
    console.log('エラーログファイル:', (window.ENVIRONMENT === 'production' ? '本番用エラーログに記録されます' : 'ローカルテスト'));
  },

  // すべてのテストを実行
  runAll: function() {
    console.log('╔════════════════════════════════════════════╗');
    console.log('║   本番デプロイ前 全テスト実行開始            ║');
    console.log('╚════════════════════════════════════════════╝');
    this.testEnvironment();
    this.testDebugConfig();
    this.testModals();
    this.testAccordions();
    this.testProgressBars();
    this.testChatAssistant();
    this.testHashNavigation();
    this.testErrorLogging();
    console.log('');
    console.log('╔════════════════════════════════════════════╗');
    console.log('║         テスト完了                          ║');
    console.log('║   window.DeploymentChecklist.runAll()     ║');
    console.log('║   で再実行可能                             ║');
    console.log('╚════════════════════════════════════════════╝');
  }
};

// ページロード時に初期テスト実行（自動）
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    DEBUG.log('📋 デプロイチェックリスト準備完了。window.DeploymentChecklist.runAll() を実行してください。');
  });
} else {
  DEBUG.log('📋 デプロイチェックリスト準備完了。window.DeploymentChecklist.runAll() を実行してください。');
}
