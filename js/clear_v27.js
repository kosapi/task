// Clear Button Handler - Simple Version
(function() {
  console.log('[CLEAR_V27] スクリプト読み込み開始');
  
  // ページ読み込み時にクリアフラグをチェック（後方互換・現行では未使用）
  if (localStorage.getItem('__clear_requested__') === '1') {
    localStorage.removeItem('__clear_requested__');
    var keys = [];
    for (var i = 0; i < localStorage.length; i++) {
      var key = localStorage.key(i);
      if (key && key.indexOf('checkbox_persist_') === 0) {
        keys.push(key);
      }
    }
    for (var k = 0; k < keys.length; k++) {
      localStorage.removeItem(keys[k]);
    }
  }
  
  // クリアボタンを探す関数
  function setupClearButton() {
    console.log('[CLEAR_V27] setupClearButton 実行');
    
    var clearBtn = document.getElementById('clear');
    console.log('[CLEAR_V27] clear ボタン検索:', clearBtn ? '✅ 見つかり' : '❌ 見つからず');
    
    if (!clearBtn) {
      console.error('[CLEAR_V27] #clear ボタンが見つかりません');
      return;
    }
    
    // クリア処理関数
    var handleClear = function(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      console.log('[CLEAR_V27] クリアボタンがクリックされました');
      
      // 確認ダイアログ
      if (!window.confirm('すべてのチェックをクリアしてよろしいですか？')) {
        console.log('[CLEAR_V27] ユーザーがキャンセルしました');
        return false;
      }
      
      console.log('[CLEAR_V27] ========== クリア処理開始 ==========');
      
      // 1. localStorage のチェックボックス保存データだけ確実に消す
      try {
        var keysToDelete = [];
        for (var i = 0; i < localStorage.length; i++) {
          var key = localStorage.key(i);
          if (key && key.indexOf('checkbox_persist_') === 0) {
            keysToDelete.push(key);
          }
        }
        for (var x = 0; x < keysToDelete.length; x++) {
          localStorage.removeItem(keysToDelete[x]);
        }
        console.log('[CLEAR_V27] checkbox_persist_* を削除: ' + keysToDelete.length + ' 件');
      } catch (err) {
        console.error('[CLEAR_V27] localStorage削除エラー:', err);
      }
      
      // 2. すべてのチェックボックスをアンチェック（保持データが残っていても強制解除）
      var allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
      console.log('[CLEAR_V27] チェックボックス検出: ' + allCheckboxes.length + ' 個');
      
      for (var j = 0; j < allCheckboxes.length; j++) {
        var checkbox = allCheckboxes[j];
        var wasChecked = checkbox.checked;
        checkbox.checked = false;
        checkbox.removeAttribute('checked');
        checkbox.setAttribute('aria-checked', 'false');
        
        if (checkbox.parentElement && checkbox.parentElement.classList) {
          checkbox.parentElement.classList.remove('checked');
        }
        
        try {
          var changeEvent = new Event('change', { bubbles: true });
          checkbox.dispatchEvent(changeEvent);
        } catch (err) {
          console.log('[CLEAR_V27] イベント発火エラー: ' + err);
        }
        
        if (wasChecked) {
          console.log('[CLEAR_V27] アンチェック: ' + checkbox.id);
        }
      }
      console.log('[CLEAR_V27] チェックボックスアンチェック完了');
      
      // 3. 確認：実際にアンチェックされているか確認
      var checkedCount = document.querySelectorAll('input[type="checkbox"]:checked').length;
      console.log('[CLEAR_V27] チェック状態の確認: ' + checkedCount + ' 個がまだチェックされている');
      
      // 4. プログレスバーを手動でリセット
      for (var k = 0; k <= 8; k++) {
        var pb = document.querySelector('.progress-bar' + k);
        if (pb) {
          pb.style.width = '0%';
          pb.className = 'progress-bar progress-bar' + k;
          pb.textContent = ' 0% ';
          console.log('[CLEAR_V27] progress-bar' + k + ' を更新');
        }
      }
      console.log('[CLEAR_V27] プログレスバー更新完了');
      
      // 5. チェックデータ再保存を防ぎつつ再描画
      setTimeout(function() {
        window.location.reload();
      }, 50);
      
      return false;
    };
    
    // PC とスマホの両方に対応
    clearBtn.addEventListener('click', handleClear);
    clearBtn.addEventListener('touchend', handleClear);
    
    console.log('[CLEAR_V27] クリアボタンにイベントを設定しました');
  }
  
  // DOM がロード済みかチェック
  if (document.readyState === 'loading') {
    console.log('[CLEAR_V27] DOMContentLoaded を待機');
    document.addEventListener('DOMContentLoaded', setupClearButton);
  } else {
    console.log('[CLEAR_V27] DOM は既に準備完了');
    setupClearButton();
  }
  
})();

console.log('[CLEAR_V27] スクリプト読み込み完了');
