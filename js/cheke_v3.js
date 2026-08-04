// cheke_v3.js: 動的要素および安全ガード対応
document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('click', function(e) {
    const target = e.target.closest('a[data-bs-toggle="modal"]');
    if (target) {
      const linkId = target.getAttribute('id');
      if (linkId) {
        const checkId = linkId.replace(/^M/, 'Check');
        const checkbox = document.getElementById(checkId);
        if (checkbox) {
          checkbox.checked = true;
          // カスタムイベントの発火
          checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }
    }
  });
});
