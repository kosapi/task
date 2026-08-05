/**
 * モーダル間リンク（ネストモーダル）完全連携スクリプト
 * モーダル内のリンクボタンをクリックした際に親モーダルを閉じ、目的の子モーダルを正確に開く
 * 子モーダルを閉じると、自動的に元の親モーダルへ復帰します
 */

document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  // グローバルクリック委譲リスナー
  document.addEventListener('click', function(e) {
    // モーダル内部のリンクまたはボタンかを判定
    const targetBtn = e.target.closest('.modal-body a[data-bs-toggle="modal"], .modal-body button[data-bs-toggle="modal"], .modal-body a[href^="#Modal"], .modal-body a[href^="#modal"]');
    
    if (!targetBtn) return;

    // 目的のモーダルIDを取得
    let targetModalId = targetBtn.getAttribute('data-bs-target');
    if (!targetModalId || targetModalId === '#') {
      targetModalId = targetBtn.getAttribute('href');
    }

    if (!targetModalId || !targetModalId.startsWith('#')) return;
    
    targetModalId = targetModalId.substring(1);
    const targetModalElem = document.getElementById(targetModalId);
    
    if (!targetModalElem) {
      console.warn('[NestedModal] Target modal not found:', targetModalId);
      return;
    }

    e.preventDefault();
    e.stopPropagation();

    // 現在開いている親モーダルを取得
    const parentModalElem = targetBtn.closest('.modal');
    
    if (parentModalElem) {
      const parentModalId = parentModalElem.id;

      // Bootstrapのインスタンスを取得して親モーダルを閉じる
      const parentInstance = bootstrap.Modal.getInstance(parentModalElem) || new bootstrap.Modal(parentModalElem);
      
      // 親モーダルが閉じ終わるのを待って子モーダルを開く
      const onParentHidden = function() {
        parentModalElem.removeEventListener('hidden.bs.modal', onParentHidden);

        const childInstance = bootstrap.Modal.getInstance(targetModalElem) || new bootstrap.Modal(targetModalElem);
        childInstance.show();

        // 子モーダルが閉じられたら元の親モーダルに自動復帰
        const onChildHidden = function() {
          targetModalElem.removeEventListener('hidden.bs.modal', onChildHidden);
          const restoreParent = bootstrap.Modal.getInstance(parentModalElem) || new bootstrap.Modal(parentModalElem);
          restoreParent.show();
        };

        targetModalElem.addEventListener('hidden.bs.modal', onChildHidden, { once: true });
      };

      parentModalElem.addEventListener('hidden.bs.modal', onParentHidden, { once: true });
      parentInstance.hide();

    } else {
      // 単体で開く場合
      const childInstance = bootstrap.Modal.getInstance(targetModalElem) || new bootstrap.Modal(targetModalElem);
      childInstance.show();
    }
  }, true);
});
