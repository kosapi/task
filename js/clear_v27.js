console.log('[Clear] スクリプト読み込み開始');

// クリアボタンをクリックしたときの処理
document.addEventListener('DOMContentLoaded', function() {
  console.log('[Clear] DOMContentLoaded イベント');
  
  var clearButton = document.getElementById('clear');
  if (!clearButton) {
    console.error('[Clear] クリアボタント（#clear）が見つかりません');
    return;
  }
  
  console.log('[Clear] クリアボタン発見:', clearButton);
  
  clearButton.addEventListener('click', function(e) {
    console.log('[Clear] クリアボタンがクリックされました');
    
    if (!confirm('すべてのチェックをクリアしてよろしいですか？')) {
      console.log('[Clear] ユーザーがキャンセルしました');
      return;
    }
    
    console.log('[Clear] ===== クリア処理開始 =====');
    
    // 古い garlic.js のキーを削除
    var garlicKeys = [
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(0)>div>div>div:eq(2)>input.Check0-1",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(0)>div>div>div:eq(4)>input.Check0-2",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(0)>div>div>div:eq(6)>input.Check0-3",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(0)>div>div>div:eq(8)>input.Check0-4",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(0)>div>div>div:eq(10)>input.Check0-5",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(0)>input.Check3-1",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(2)>input.Check3-2",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(4)>input.Check3-3",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(6)>input.Check3-4",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(8)>input.Check3-5",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(10)>input.Check3-6",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(12)>input.Check3-7",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(14)>input.Check3-8",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(16)>input.Check3-9",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(1)>div>div>div>div:eq(18)>input.Check3-10",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(2)>div>div>div>div:eq(0)>input.Check1-1",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(2)>div>div>div>div:eq(2)>input.Check1-2",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(2)>div>div>div>div:eq(3)>input.Check1-3",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(2)>div>div>div>div:eq(5)>input.Check1-4",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(0)>input.Check2-1",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(2)>input.Check2-2",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(4)>input.Check2-3",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(6)>input.Check2-4",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(8)>input.Check2-5",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(10)>input.Check2-6",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(12)>input.Check2-7",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(13)>input.Check2-8",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(15)>input.Check2-9",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(0)>div>div>div:eq(16)>input.Check2-10",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(1)>div>div>div>div:eq(0)>input.Check4-1",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(3)>div:eq(1)>div>div>div>div:eq(2)>input.Check4-2",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(0)>div>div>div:eq(1)>input.Check7-1",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(0)>div>div>div:eq(3)>input.Check7-2",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(0)>div>div>div:eq(5)>input.Check7-3",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(0)>div>div>div:eq(7)>input.Check7-4",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(0)>div>div>div:eq(9)>input.Check7-5",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(0)>div>div>div:eq(11)>input.Check7-6",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(0)>div>div>div:eq(13)>input.Check7-7",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(0)>input.Check5-1",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(2)>input.Check5-2",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(4)>input.Check5-3",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(6)>input.Check5-4",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(8)>input.Check5-5",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(10)>input.Check5-6",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(11)>input.Check5-7",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(12)>input.Check5-8",
      "garlic:teito.link/task/>form>div>div:eq(0)>div:eq(4)>div:eq(1)>div>div>div>div:eq(13)>input.Check5-9",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(0)>input.Check6-1",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(2)>input.Check6-2",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(4)>input.Check6-3",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(6)>input.Check6-4",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(8)>input.Check6-5",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(10)>input.Check6-6",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(12)>input.Check6-7",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(14)>input.Check6-8",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(16)>input.Check6-9",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(18)>input.Check6-10",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(20)>input.Check6-11",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(21)>input.Check6-12",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(0)>div>div>div:eq(23)>input.Check6-13",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(1)>div>div>div>div:eq(0)>input.Check8-1",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(1)>div>div>div>div:eq(2)>input.Check8-2",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(1)>div>div>div>div:eq(4)>input.Check8-3",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(1)>div>div>div>div:eq(7)>input.Check8-4",
      "garlic:teito.link/task/>form>div>div:eq(1)>div:eq(1)>div>div>div>div:eq(11)>input.Check8-5"
    ];
    
    garlicKeys.forEach(function(key) {
      localStorage.removeItem(key);
    });
    console.log('[Clear] 古い garlic.js キー削除完了:', garlicKeys.length, '件');
    
    // 新しい checkbox-persist.js のキーをすべて削除
    var keysToRemove = [];
    for (var i = 0; i < localStorage.length; i++) {
      var key = localStorage.key(i);
      if (key && key.indexOf('checkbox_persist_') === 0) {
        keysToRemove.push(key);
      }
    }
    
    keysToRemove.forEach(function(key) {
      localStorage.removeItem(key);
      console.log('[Clear] 削除:', key);
    });
    console.log('[Clear] checkbox-persist キー削除完了:', keysToRemove.length, '件');
    
    // すべてのチェックボックスをアンチェック（vanilla JavaScript で直接操作）
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    console.log('[Clear] チェックボックスクリア開始、対象数:', checkboxes.length);
    
    checkboxes.forEach(function(checkbox) {
      checkbox.checked = false;
    });
    
    console.log('[Clear] すべてのチェックボックスをアンチェック完了');
    console.log('[Clear] ===== ページをリロード =====');
    
    // ページをリロード
    location.reload();
  });
});

console.log('[Clear] スクリプト初期化完了');
