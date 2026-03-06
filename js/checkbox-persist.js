/**
 * Checkbox Persistence - jQuery不要な localStorage ベースのチェックボックス状態保存
 * garlic.js の代替実装
 */

console.log('[CheckboxPersist] スクリプト読み込み開始');

(function() {
  'use strict';

  // localStorage が利用可能か確認
  var isLocalStorageAvailable = function() {
    try {
      var test = '__localStorage_test__';
      localStorage.setItem(test, test);
      localStorage.removeItem(test);
      console.log('[CheckboxPersist] localStorage は利用可能です');
      return true;
    } catch (e) {
      console.error('[CheckboxPersist] localStorage は利用不可です:', e);
      return false;
    }
  };

  if (!isLocalStorageAvailable()) {
    console.error('[CheckboxPersist] localStorage が利用不可なため、チェックボックス永続化は無効です');
    return;
  }

  var STORAGE_PREFIX = 'checkbox_persist_';

  /**
   * チェックボックスの状態を保存
   */
  var saveCheckboxState = function(checkbox) {
    if (!checkbox) {
      console.warn('[CheckboxPersist] チェックボックスが null です');
      return;
    }

    var id = checkbox.id || checkbox.name;
    if (!id) {
      console.warn('[CheckboxPersist] チェックボックスに ID または name がありません');
      return;
    }

    var key = STORAGE_PREFIX + id;
    var value = checkbox.checked ? '1' : '0';
    
    try {
      localStorage.setItem(key, value);
      console.log('[CheckboxPersist] ✅ 保存成功:', key, '=', value);
    } catch (e) {
      console.error('[CheckboxPersist] ❌ 保存エラー:', key, e);
    }
  };

  /**
   * チェックボックスの状態を復元
   */
  var restoreCheckboxState = function(checkbox) {
    if (!checkbox) {
      console.warn('[CheckboxPersist] チェックボックスが null です');
      return;
    }

    var id = checkbox.id || checkbox.name;
    if (!id) {
      return; // ログは不要
    }

    var key = STORAGE_PREFIX + id;
    var stored = localStorage.getItem(key);
    
    if (stored !== null) {
      var shouldBeChecked = (stored === '1');
      var wasChecked = checkbox.checked;
      checkbox.checked = shouldBeChecked;
      
      console.log('[CheckboxPersist] ✅ 復元:', key, '→', shouldBeChecked, '(変更:', wasChecked !== shouldBeChecked, ')');
      
      if (wasChecked !== shouldBeChecked) {
        // change イベントを発火
        var event = new Event('change', { bubbles: true });
        checkbox.dispatchEvent(event);
      }
    }
  };

  /**
   * ページ読み込み時の初期化
   */
  var initializeCheckboxes = function() {
    console.log('[CheckboxPersist] ===== チェックボックス初期化開始 =====');
    
    // ID または name 属性を持つすべてのチェックボックスを選択
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    console.log('[CheckboxPersist] 見つかったチェックボックス総数:', checkboxes.length);

    var processedCount = 0;
    checkboxes.forEach(function(checkbox, index) {
      var id = checkbox.id || checkbox.name;
      
      // ID または name がある場合のみ処理
      if (id) {
        processedCount++;
        
        // 状態を復元
        restoreCheckboxState(checkbox);

        // change イベントリスナーを追加
        checkbox.addEventListener('change', function() {
          console.log('[CheckboxPersist] change イベント:', id);
          saveCheckboxState(this);
        });

        // input イベントリスナーも追加（予防措置）
        checkbox.addEventListener('input', function() {
          console.log('[CheckboxPersist] input イベント:', id);
          saveCheckboxState(this);
        });

        if (index < 5) { // 最初の5つだけログ出力
          console.log('[CheckboxPersist]   - 処理済み:', id);
        }
      }
    });

    console.log('[CheckboxPersist] 処理されたチェックボックス数:', processedCount);
    console.log('[CheckboxPersist] ===== チェックボックス初期化完了 =====');
    
    // localStorage の内容をデバッグ出力
    console.log('[CheckboxPersist] 📦 localStorage 内容:');
    for (var i = 0; i < localStorage.length; i++) {
      var key = localStorage.key(i);
      if (key.indexOf(STORAGE_PREFIX) === 0) {
        console.log('[CheckboxPersist]   ', key, '=', localStorage.getItem(key));
      }
    }
  };

  /**
   * ページ読み込み完了時に初期化
   */
  console.log('[CheckboxPersist] document.readyState:', document.readyState);
  
  if (document.readyState === 'loading') {
    console.log('[CheckboxPersist] DOMContentLoaded を待機中...');
    document.addEventListener('DOMContentLoaded', function() {
      console.log('[CheckboxPersist] DOMContentLoaded イベント発火');
      initializeCheckboxes();
    });
  } else {
    console.log('[CheckboxPersist] DOM は既に準備完了、すぐに初期化');
    initializeCheckboxes();
  }

  // window.load イベントでも確認
  window.addEventListener('load', function() {
    console.log('[CheckboxPersist] window.load イベント発火');
    // ここで再度チェックボックスをスキャン（念のため）
    setTimeout(function() {
      var checkboxes = document.querySelectorAll('input[type="checkbox"]');
      console.log('[CheckboxPersist] [window.load] チェックボックス再確認:', checkboxes.length);
    }, 100);
  });

  // チェックボックス復元完了を通知するカスタムイベントを発火
  var event = new Event('checkboxPersistReady');
  document.dispatchEvent(event);
  console.log('[CheckboxPersist] checkboxPersistReady イベントを発火');

  console.log('[CheckboxPersist] スクリプト初期化完了');
})();

