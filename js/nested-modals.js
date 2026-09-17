/**
 * モーダル間リンク（ネストモーダル）完全連携スクリプト
 * 独立サブモーダル（modal-ticket-welfare等）をスムーズに表示・切替します
 */

document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  // 1. モーダル内部のページ内アンカーリンク（目次INDEXリンク・pagetop等）のスムーズスクロール処理
  document.addEventListener('click', function(e) {
    const anchor = e.target.closest('.modal a[href^="#"]');
    if (!anchor) return;

    // data-bs-toggle="modal" 等のモーダル切り替えボタン・リンクは除外（後続のネストモーダル処理へ）
    if (anchor.getAttribute('data-bs-toggle') === 'modal' || 
        anchor.getAttribute('data-nested-modal-target') ||
        anchor.hasAttribute('data-bs-target')) {
      return;
    }

    const href = anchor.getAttribute('href');
    if (!href || href === '#' || href.indexOf('#') !== 0) return;

    const targetId = href.substring(1);
    if (!targetId) return;

    const modal = anchor.closest('.modal');
    if (!modal) return;

    // 同一モーダル内にターゲットIDが存在するか確認（数字始まりID '01', '1' 等にも対応）
    let targetElem = null;
    try {
      targetElem = modal.querySelector('#' + (window.CSS && CSS.escape ? CSS.escape(targetId) : targetId));
    } catch (err) {
      targetElem = document.getElementById(targetId);
    }

    // 同一モーダル内の要素である場合
    if (targetElem && modal.contains(targetElem)) {
      e.preventDefault();
      e.stopPropagation();

      const modalBody = modal.querySelector('.modal-body') || modal;
      const bodyRect = modalBody.getBoundingClientRect();
      const elemRect = targetElem.getBoundingClientRect();
      const offsetTop = elemRect.top - bodyRect.top + modalBody.scrollTop;

      modalBody.scrollTo({
        top: Math.max(0, offsetTop - 12),
        behavior: 'smooth'
      });
    }
  }, true);

  // 2. モーダル間リンク（ネストモーダル）の処理
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
