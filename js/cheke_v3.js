// cheke_v3.js: モーダル開くリンク・サブモーダルリンククリック時の自動連動チェックを100%保証
document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('click', function(e) {
    // モーダルを開くリンクまたはフォームチェックラベル内のリンクを検出
    const link = e.target.closest('a[data-bs-toggle="modal"], a[data-bs-target], .sub-modal-link, .form-check-label a, a[href*="modal"]');
    if (!link) return;

    let checkbox = null;

    // 1. 直近の .form-check または .item-card 内のチェックボックスを探す
    const container = link.closest('.form-check, .item-card, .list-group-item, li');
    if (container) {
      checkbox = container.querySelector('input[type="checkbox"]');
    }

    // 2. ID による紐付け（M1-1 -> Check1-1）からの補完探索
    if (!checkbox) {
      const linkId = link.getAttribute('id') || link.getAttribute('data-link-id');
      if (linkId) {
        const checkId = linkId.replace(/^M/, 'Check');
        checkbox = document.getElementById(checkId);
      }
    }

    // 3. チェックを入れて保存＆カウント更新イベントを発火
    if (checkbox) {
      setTimeout(function() {
        checkbox.checked = true;
        checkbox.setAttribute('checked', 'checked');
        checkbox.dispatchEvent(new Event('change', { bubbles: true }));

        // チェックカウント更新処理 (updateCheckCount) を直接呼び出し
        if (typeof window.updateCheckCount === 'function') {
          window.updateCheckCount();
        }
      }, 50);
    }
  });
});
