/**
 * アコーディオンのハッシュリンク機能
 * URLハッシュを使ってアコーディオンを開き、その場所にスクロールできるようにする
 * モーダルへの直接リンクもサポート
 */

(function() {
  'use strict';

  /**
   * URLハッシュに基づいてアコーディオンを開く
   */
  function openAccordionFromHash() {
    const hash = window.location.hash;
    if (!hash) return;

    // ハッシュが #Modal で始まる場合（モーダルへの直接リンク）
    if (hash.startsWith('#Modal')) {
      const modalElement = document.querySelector(hash);
      
      if (modalElement && modalElement.classList.contains('modal')) {
        // モーダルが含まれるアコーディオンを特定
        const accordionCollapse = modalElement.closest('.accordion-collapse');
        
        if (accordionCollapse) {
          // アコーディオンを開く
          const bsCollapse = new bootstrap.Collapse(accordionCollapse, {
            toggle: false
          });
          bsCollapse.show();
          
          // アコーディオンが開いた後にモーダルを開く
          accordionCollapse.addEventListener('shown.bs.collapse', function onShown() {
            // モーダルを開く
            const bsModal = new bootstrap.Modal(modalElement);
            bsModal.show();
            
            // スクロール
            setTimeout(() => {
              const accordionItem = accordionCollapse.closest('.accordion-item');
              if (accordionItem) {
                accordionItem.scrollIntoView({ 
                  behavior: 'smooth', 
                  block: 'start' 
                });
              }
            }, 100);
            
            // イベントリスナーを削除（一度だけ実行）
            accordionCollapse.removeEventListener('shown.bs.collapse', onShown);
          });
        } else {
          // アコーディオン内にない場合は直接モーダルを開く
          const bsModal = new bootstrap.Modal(modalElement);
          bsModal.show();
        }
      }
    }
    // ハッシュが #collapse で始まる場合
    else if (hash.startsWith('#collapse')) {
      const targetElement = document.querySelector(hash);
      
      if (targetElement && targetElement.classList.contains('accordion-collapse')) {
        // Bootstrap 5 のCollapse インスタンスを作成して開く
        const bsCollapse = new bootstrap.Collapse(targetElement, {
          toggle: true
        });

        // アコーディオンが開いた後にスクロール
        setTimeout(() => {
          const accordionItem = targetElement.closest('.accordion-item');
          if (accordionItem) {
            accordionItem.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start' 
            });
          }
        }, 350); // アニメーション完了を待つ
      }
    }
    // ハッシュが #heading で始まる場合も対応
    else if (hash.startsWith('#heading')) {
      const headingId = hash.substring(1); // #を除去
      const collapseId = headingId.replace('heading', 'collapse');
      const targetElement = document.querySelector('#' + collapseId);
      
      if (targetElement && targetElement.classList.contains('accordion-collapse')) {
        const bsCollapse = new bootstrap.Collapse(targetElement, {
          toggle: true
        });

        setTimeout(() => {
          const accordionItem = targetElement.closest('.accordion-item');
          if (accordionItem) {
            accordionItem.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start' 
            });
          }
        }, 350);
      }
    }
  }

  /**
   * アコーディオンが開かれた時にURLハッシュを更新
   */
  function updateHashOnAccordionOpen() {
    const accordionElement = document.getElementById('accordion');
    if (!accordionElement) return;

    accordionElement.addEventListener('shown.bs.collapse', function(event) {
      const collapseId = event.target.id;
      if (collapseId && collapseId.startsWith('collapse')) {
        // URLハッシュを更新（スクロールはしない）
        if (history.replaceState) {
          history.replaceState(null, null, '#' + collapseId);
        } else {
          // 古いブラウザ対応
          window.location.hash = collapseId;
        }
      }
    });
  }

  /**
   * ハッシュ変更時の処理
   */
  function handleHashChange() {
    openAccordionFromHash();
  }

  /**
   * 初期化
   */
  function init() {
    // DOMContentLoaded後に実行
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        // Bootstrapが読み込まれるまで少し待つ
        setTimeout(() => {
          openAccordionFromHash();
          updateHashOnAccordionOpen();
        }, 100);
      });
    } else {
      setTimeout(() => {
        openAccordionFromHash();
        updateHashOnAccordionOpen();
      }, 100);
    }

    // ハッシュ変更を監視
    window.addEventListener('hashchange', handleHashChange);
  }

  // 初期化を実行
  init();

})();
