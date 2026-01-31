/**
 * モーダル・アコーディオン初期化スクリプト
 */

// URLハッシュをクリアしてアコーディオンが自動で開かないようにする
if (window.location.hash) {
  // ハッシュが collapse で始まる場合のみクリア
  const hash = window.location.hash.replace('#', '').trim();
  if (hash.startsWith('collapse')) {
    console.log('Clearing accordion hash:', hash);
    // ハッシュを削除（履歴に残さない）
    history.replaceState(null, null, ' ');
  }
}

// ローカルストレージからアコーディオン状態をクリア
try {
  // garlic.js や他のスクリプトが保存したアコーディオン状態をクリア
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    if (key && (key.includes('collapse') || key.includes('accordion'))) {
      localStorage.removeItem(key);
      console.log('Removed localStorage key:', key);
    }
  }
} catch (e) {
  console.log('Could not clear localStorage:', e);
}

function setupNestedModalHandlers() {
  console.log('🔧 ネストされたモーダルハンドラーをセットアップ中...');
  
  // data-nested-modal-target を持つすべてのボタンに対して
  document.querySelectorAll('[data-nested-modal-target]').forEach(function(button) {
    var targetModalId = button.getAttribute('data-nested-modal-target');
    console.log('📌 ボタン発見:', button.textContent.trim(), '→', targetModalId);
    
    button.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      console.log('🖱️ ネストされたモーダルボタンがクリックされました:', targetModalId);
      
      // このボタンがモーダル内にあるかチェック
      var parentModal = this.closest('.modal');
      if (parentModal) {
        console.log('📦 親モーダル発見:', parentModal.id);
        
        // 親モーダルのインスタンスを取得して閉じる
        var parentModalInstance = bootstrap.Modal.getInstance(parentModal);
        if (parentModalInstance) {
          console.log('✅ 親モーダルを閉じています...');
          parentModalInstance.hide();
          
          // 親モーダル閉じた後、ターゲットモーダルを開く
          setTimeout(function() {
            var targetModal = document.getElementById(targetModalId);
            if (targetModal) {
              console.log('✅ ターゲットモーダルを開いています:', targetModalId);
              var targetModalInstance = new bootstrap.Modal(targetModal);
              targetModalInstance.show();
            } else {
              console.warn('⚠️ ターゲットモーダルが見つかりません:', targetModalId);
            }
          }, 300);
        }
      }
    });
  });
  
  console.log('✅ ネストされたモーダルハンドラーのセットアップ完了');
}

function setupModalInPageLinks() {
  console.log('🔗 モーダル内ページリンクの設定中...');
  
  // モーダル内の #anchor リンクでスムーズスクロール
  document.querySelectorAll('.modal-body a[href^="#"]').forEach(function(link) {
    link.addEventListener('click', function(e) {
      var href = this.getAttribute('href');
      var target = document.querySelector(href);
      if (target && target.closest('.modal-body')) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        console.log('📍 モーダル内リンク遷移:', href);
      }
    });
  });
}

// DOMContentLoaded 時に初期化
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    setupNestedModalHandlers();
    setupModalInPageLinks();
  });
} else {
  setupNestedModalHandlers();
  setupModalInPageLinks();
}

