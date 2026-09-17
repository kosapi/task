/**
 * モーダル間リンク（ネストモーダル）完全連携スクリプト
 * 独立サブモーダル（modal-ticket-welfare等）をスムーズに表示・切替します
 */

document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  document.addEventListener('click', function(e) {
    // モーダル内部のリンクまたはボタンかを判定
    // ※ href^="#" は除外（ページ内ジャンプリンク・readAloudボタンを誤って対象にしないため）
    const targetBtn = e.target.closest('.modal-body a[data-bs-toggle="modal"], .modal-body button[data-bs-toggle="modal"], .modal-body [data-nested-modal-target]');
    
    if (!targetBtn) return;

    let href = targetBtn.getAttribute('data-bs-target') || targetBtn.getAttribute('data-nested-modal-target') || targetBtn.getAttribute('href') || '';
    if (!href || href === '#') return;

    const rawId = href.replace(/^#/, '');
    if (!rawId) return;

    const targetModalElem = document.getElementById(rawId);
    if (!targetModalElem) return;

    // 対象要素が Bootstrap モーダル（.modal クラスを持つ）かを確認
    // ページ内ジャンプ先の <p id="m1"> 等を誤って Modal として扱わないための安全弁
    if (!targetModalElem.classList.contains('modal')) return;

    e.preventDefault();
    e.stopPropagation();

    // 現在開いている親モーダル
    const parentModalElem = targetBtn.closest('.modal');

    // 親モーダルを非表示にしてから子モーダルを開く
    if (parentModalElem) {
      const parentInstance = bootstrap.Modal.getInstance(parentModalElem) || new bootstrap.Modal(parentModalElem);
      
      const openChild = function() {
        parentModalElem.removeEventListener('hidden.bs.modal', openChild);

        const childInstance = bootstrap.Modal.getInstance(targetModalElem) || new bootstrap.Modal(targetModalElem);
        childInstance.show();

        // 子モーダルが閉じられたら元の親モーダルに自動復帰
        const restoreParent = function() {
          targetModalElem.removeEventListener('hidden.bs.modal', restoreParent);
          const parentModal = bootstrap.Modal.getInstance(parentModalElem) || new bootstrap.Modal(parentModalElem);
          parentModal.show();
        };

        targetModalElem.addEventListener('hidden.bs.modal', restoreParent, { once: true });
      };

      // aria-hidden 警告を防ぐためフォーカスを外してから非表示にする
      if (document.activeElement && parentModalElem.contains(document.activeElement)) {
        document.activeElement.blur();
      }

      parentModalElem.addEventListener('hidden.bs.modal', openChild, { once: true });
      parentInstance.hide();
    } else {
      const childInstance = bootstrap.Modal.getInstance(targetModalElem) || new bootstrap.Modal(targetModalElem);
      childInstance.show();
    }
  }, true);
});
