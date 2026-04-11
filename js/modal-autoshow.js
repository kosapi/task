// Auto-open accordion and modal based on URL hash
(function() {
  function sleep(ms) {
    return new Promise(function(res) { setTimeout(res, ms); });
  }

  // アコーディオンを開く関数
  function openAccordion(collapseEl) {
    try {
      console.log('アコーディオンを開きます:', collapseEl.id);
      
      // jQueryが利用可能ならjQueryで開く
      if (typeof jQuery !== 'undefined') {
        jQuery(collapseEl).collapse('show');
        console.log('笨ｨ Accordion opened via jQuery');
        return;
      }
      
      // Bootstrap APIが利用可能ならBootstrapで開く
      if (window.bootstrap && typeof bootstrap.Collapse === 'function') {
        try {
          const bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: false });
          bsCollapse.show();
          console.log('笨ｨ Accordion opened via Bootstrap API');
          return;
        } catch (e) {
          console.warn('笞・・Bootstrap Collapse error:', e.message);
        }
      }
      
      // Fallback: 手動でshowクラスを付与
      console.log('邃ｹ・・Using manual show method');
      collapseEl.classList.add('show');
      
      // ボタンのaria-expanded属性をtrueに
      const button = collapseEl.closest('.accordion-item')?.querySelector('[data-bs-toggle="collapse"]');
      if (button) {
        button.setAttribute('aria-expanded', 'true');
      }
    } catch (e) {
      console.error('アコーディオンを開く際のエラー:', e);
    }
  }

  function handleHashNavigation() {
    try {
      const hash = window.location.hash.replace('#', '').trim();
      if (!hash) {
        console.log('URLにハッシュがありません - 全てのアコーディオンは閉じたままです');
        return;
      }

      console.log('ハッシュを処理中:', hash);

      // アコーディオンID（collapse0, collapse1, ... collapse8など）を判定
      const collapseMatch = hash.match(/^collapse\d+$/i);
      if (collapseMatch) {
        const collapseId = hash;
        console.log('アコーディオンを探しています:', collapseId);

        // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ繧帝幕縺・
        const collapseEl = document.getElementById(collapseId);
        if (!collapseEl) {
          console.warn('Collapse要素（ID: "' + collapseId + '"）が見つかりません');
          const allCollapses = document.querySelectorAll('[id^="collapse"]');
          console.log('利用可能なcollapse要素:', Array.from(allCollapses).map(el => el.id));
          return;
        }

        console.log('collapse要素が見つかりました');
        
        // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ繧帝幕縺・
        openAccordion(collapseEl);

        // 繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ
        try {
          const accordionItem = collapseEl.closest('.accordion-item');
          if (accordionItem) {
            console.log('アコーディオンアイテムへスクロールします');
            setTimeout(() => {
              accordionItem.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
          }
        } catch (e) {
          console.warn('スクロールエラー:', e.message);
        }
        return;
      }

      // モーダルID（modal0-3など）を判定 collapseでなければIDで検索
      const modalEl = document.getElementById(hash);
      if (modalEl && modalEl.classList.contains('modal')) {
        console.log('モーダルを開きます:', hash);
        openModal(modalEl);
        
        // スクロール
        try {
          setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }, 100);
        } catch (e) {}
      } else if (modalEl) {
        // モーダルでなければスクロールのみ
        console.log('要素へスクロール:', hash);
        try {
          setTimeout(() => {
            modalEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }, 100);
        } catch (e) {
          console.warn('スクロールエラー:', e.message);
        }
      } else {
        // 要素が見つからない場合
        console.warn('ID "' + hash + '" の要素が見つかりません');
      }

    } catch (e) {
      console.error('handleHashNavigationのエラー:', e);
    }
  }

  function openModal(modalEl) {
    try {
      console.log('モーダルを開きます:', modalEl.id);
      console.log('モーダル要素:', modalEl);
      console.log('モーダルのクラス（前）:', modalEl.className);
      console.log('モーダルのdisplay（前）:', modalEl.style.display);
      
      // モーダルがbody直下でない場合はbodyに移動
      if (modalEl.parentElement.id !== 'modal-container' && modalEl.parentElement.tagName !== 'BODY') {
        console.log('モーダルをbodyに移動（元: ', modalEl.parentElement.id, '）');
        // 元の親IDをdata-original-parentに保存
        if (!modalEl.hasAttribute('data-original-parent')) {
          modalEl.setAttribute('data-original-parent', modalEl.parentElement.id);
        }
        document.body.appendChild(modalEl);
      }
      
      // Bootstrap Modal APIで開く
      if (window.bootstrap && typeof bootstrap.Modal === 'function') {
        try {
          const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
          console.log('Bootstrap Modalインスタンス:', inst);
          inst.show();
          
            // Bootstrap CSSのfadeクラスを除去しdisplayをblockに
          setTimeout(() => {
            // fadeクラス除去後、スタイルを調整
            modalEl.classList.remove('fade');
            console.log('肌 Removed fade class');
            
            // モーダルのスタイルをblockに
            modalEl.style.display = 'block';
            modalEl.style.position = 'fixed';
            modalEl.style.top = '0';
            modalEl.style.left = '0';
            modalEl.style.width = '100%';
            modalEl.style.height = '100%';
            modalEl.style.zIndex = '1055';
            modalEl.style.overflow = 'auto';
            console.log('肌 Fixed modal styles (block display)');
            
            // .modal-dialogのスタイル調整
            const dialog = modalEl.querySelector('.modal-dialog');
            if (dialog) {
              // modal-dialog-scrollableクラスを除去
              dialog.classList.remove('modal-dialog-scrollable');
              
              // dialogのサイズ調整
              dialog.style.display = 'block';
              dialog.style.width = '500px';
              dialog.style.maxWidth = '90%';
              dialog.style.height = '500px'; // 蝗ｺ螳夐ｫ倥＆
              dialog.style.margin = '50px auto'; // 荳ｭ螟ｮ驟咲ｽｮ
              dialog.style.position = 'relative';
              console.log('肌 Fixed modal-dialog styles (block, fixed height 500px)');
              
              // .modal-contentのスタイル調整
              const content = dialog.querySelector('.modal-content');
              if (content) {
                content.style.display = 'flex';
                content.style.flexDirection = 'column';
                content.style.width = '100%';
                content.style.height = '100%';
                content.style.overflow = 'hidden';
                console.log('肌 Fixed modal-content styles');
              }
              
              // .modal-bodyのスタイル調整
              const body = dialog.querySelector('.modal-body');
              if (body) {
                body.style.flex = '1';
                body.style.overflowY = 'auto';
                body.style.overflowX = 'hidden';
                console.log('肌 Fixed modal-body styles');
              }
              
              // レイアウト再計算
              void dialog.offsetHeight;
              void modalEl.offsetHeight;
              
              // dialogのサイズ確認
              const rect = dialog.getBoundingClientRect();
              console.log('剥 Dialog size:', {width: rect.width, height: rect.height});
              
              if (rect.width > 0 && rect.height > 0) {
                console.log('SUCCESS! モーダルが表示されました');
              } else {
                console.error('Dialogのサイズが0です');
              }
            }
          }, 50);
          
          // show()後の状態確認
          setTimeout(() => {
            console.log('投 Modal classes after show():', modalEl.className);
            console.log('投 Modal display after show():', modalEl.style.display);
            console.log('投 Modal aria-modal:', modalEl.getAttribute('aria-modal'));
            console.log('投 Body classes:', document.body.className);
            
            const backdrop = document.querySelector('.modal-backdrop');
            console.log('投 Backdrop exists:', !!backdrop);
            
            // モーダルとBackdropのスタイル確認
            const modalStyles = window.getComputedStyle(modalEl);
            const backdropStyles = backdrop ? window.getComputedStyle(backdrop) : null;
            
            console.log('耳 Modal z-index:', modalStyles.zIndex);
            console.log('耳 Modal opacity:', modalStyles.opacity);
            console.log('耳 Modal visibility:', modalStyles.visibility);
            console.log('耳 Modal position:', modalStyles.position);
            console.log('耳 Modal top:', modalStyles.top);
            console.log('耳 Modal right:', modalStyles.right);
            console.log('耳 Modal bottom:', modalStyles.bottom);
            console.log('耳 Modal left:', modalStyles.left);
            console.log('耳 Modal width:', modalStyles.width);
            console.log('耳 Modal height:', modalStyles.height);
            
            if (backdropStyles) {
              console.log('耳 Backdrop z-index:', backdropStyles.zIndex);
              console.log('耳 Backdrop opacity:', backdropStyles.opacity);
              console.log('耳 Backdrop display:', backdropStyles.display);
            }
            
            // モーダルの位置確認
            const rect = modalEl.getBoundingClientRect();
            console.log('棟 Modal position:', {
              top: rect.top,
              left: rect.left,
              width: rect.width,
              height: rect.height,
              visible: rect.width > 0 && rect.height > 0
            });
            
              // .modal-dialogのスタイル確認
            const dialog = modalEl.querySelector('.modal-dialog');
            if (dialog) {
              const dialogStyles = window.getComputedStyle(dialog);
              const dialogRect = dialog.getBoundingClientRect();
              console.log('耳 Dialog display:', dialogStyles.display);
              console.log('耳 Dialog visibility:', dialogStyles.visibility);
              console.log('耳 Dialog opacity:', dialogStyles.opacity);
              console.log('耳 Dialog width:', dialogStyles.width);
              console.log('耳 Dialog height:', dialogStyles.height);
              console.log('棟 Dialog position:', {
                top: dialogRect.top,
                left: dialogRect.left,
                width: dialogRect.width,
                height: dialogRect.height
              });
              
              // .modal-contentのスタイル確認
              const content = dialog.querySelector('.modal-content');
              if (content) {
                const contentStyles = window.getComputedStyle(content);
                const contentRect = content.getBoundingClientRect();
                console.log('耳 Content display:', contentStyles.display);
                console.log('耳 Content width:', contentStyles.width);
                console.log('耳 Content height:', contentStyles.height);
                console.log('棟 Content position:', {
                  width: contentRect.width,
                  height: contentRect.height
                });
                
                // modal-headerとmodal-bodyのサイズ確認
                const header = content.querySelector('.modal-header');
                const body = content.querySelector('.modal-body');
                if (header) {
                  const headerRect = header.getBoundingClientRect();
                  const headerStyles = window.getComputedStyle(header);
                  console.log('棟 Header size:', {width: headerRect.width, height: headerRect.height});
                  console.log('耳 Header display:', headerStyles.display);
                  console.log('耳 Header innerHTML length:', header.innerHTML.length);
                }
                if (body) {
                  const bodyRect = body.getBoundingClientRect();
                  const bodyStyles = window.getComputedStyle(body);
                  console.log('棟 Body size:', {width: bodyRect.width, height: bodyRect.height});
                  console.log('耳 Body display:', bodyStyles.display);
                  console.log('耳 Body whiteSpace:', bodyStyles.whiteSpace);
                  console.log('耳 Body innerHTML length:', body.innerHTML.length);
                }
              }
              
              // モーダルのflexスタイル確認
              console.log('耳 Modal display:', modalStyles.display);
              console.log('耳 Modal align-items:', modalStyles.alignItems);
              console.log('耳 Modal justify-content:', modalStyles.justifyContent);
              
            } else {
              console.error('.modal-dialogがモーダル内に見つかりません!');
            }
          }, 100);
          
          console.log('Bootstrap Modal APIでモーダルを開きました');
          return;
        } catch (e) {
          console.warn('Bootstrap Modalエラー:', e.message);
        }
      }
      
      // jQueryでモーダルを開く
      if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery(modalEl).modal('show');
        console.log('笨ｨ Modal opened via jQuery .modal("show")');
        return;
      }
      
      // ボタンでモーダルを開く
      const modalButton = document.querySelector(`[data-bs-target="#${modalEl.id}"]`);
      if (modalButton) {
        console.log('笨・Found modal button, clicking it');
        modalButton.click();
        console.log('笨ｨ Modal opened via button click');
        return;
      }

      // Fallback: 手動でモーダルを表示
      console.log('邃ｹ・・Using manual modal display method');
      
      if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
      }

      document.body.classList.add('modal-open');
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
      modalEl.setAttribute('aria-modal', 'true');
      modalEl.removeAttribute('aria-hidden');

      // フォーカスを設定
      const focusTarget = modalEl.querySelector('button, [tabindex], a') || modalEl;
      if (focusTarget && focusTarget.focus) {
        focusTarget.focus();
      }

      console.log('Fallbackでモーダルを開きました');
    } catch (e) {
      console.error('モーダルを開く際のエラー:', e);
    }
  }

  // ハッシュナビゲーション初期化
  function initHashNavigation() {
    console.log('噫 Initializing hash navigation...');
    console.log('搭 Current hash:', window.location.hash);
    console.log('塘 Document ready state:', document.readyState);
    
    // モーダルの元親IDを記録
    function recordModalOriginalParents() {
      const allModals = document.querySelectorAll('.modal');
      console.log(`統 Recording original parents for ${allModals.length} modals`);
      allModals.forEach(modal => {
        if (!modal.hasAttribute('data-original-parent') && modal.parentElement) {
          const parentId = modal.parentElement.id;
          if (parentId && parentId !== 'modal-container' && modal.parentElement.tagName !== 'BODY') {
            modal.setAttribute('data-original-parent', parentId);
            console.log(`笨・Set data-original-parent="${parentId}" for ${modal.id}`);
          }
        }
      });
    }
    
    // ページロード状態によって処理を分岐
    if (document.readyState === 'loading') {
      console.log('塘 Page still loading, waiting for DOMContentLoaded...');
      document.addEventListener('DOMContentLoaded', function() {
        console.log('笨・DOMContentLoaded fired');
        recordModalOriginalParents();
        // Bootstrapの初期化待ち
        setTimeout(handleHashNavigation, 800);
      });
    } else {
      console.log('笨・Page already loaded, processing hash immediately');
      recordModalOriginalParents();
      // ページロード済みなら即時処理
      setTimeout(handleHashNavigation, 300);
    }
    
    // window loadイベントで再処理
    window.addEventListener('load', function() {
      console.log('塘 Window load event fired');
      setTimeout(() => {
        const hash = window.location.hash.replace('#', '').trim();
        if (hash) {
          console.log('売 Reprocessing hash on load event:', hash);
          handleHashNavigation();
        }
      }, 500);
    });
  }

  // 初期化実行
  initHashNavigation();

  // hashchangeイベントで再処理
  window.addEventListener('hashchange', function(e) {
    console.log('売 Hash changed:', window.location.hash);
    
    // 蜑阪・繝｢繝ｼ繝繝ｫ繧帝哩縺倥ｋ
    const openModal = document.querySelector('.modal.show');
    if (openModal) {
      if (window.bootstrap && typeof bootstrap.Modal === 'function') {
        bootstrap.Modal.getInstance(openModal)?.hide();
      }
      openModal.classList.remove('show');
    }
    
    // 蜑阪・繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ繧帝哩縺倥ｋ
    const openCollapse = document.querySelector('.collapse.show');
    if (openCollapse) {
      if (window.bootstrap && typeof bootstrap.Collapse === 'function') {
        bootstrap.Collapse.getInstance(openCollapse)?.hide();
      }
      openCollapse.classList.remove('show');
    }
    
    setTimeout(handleHashNavigation, 100);
  });

  // Escapeキーでモーダルを閉じる
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const openModal = document.querySelector('.modal.show');
      if (openModal) {
        console.log('爆 Closing modal with Escape key');
        openModal.classList.remove('show');
        openModal.style.display = 'none';
          // move focus out of modal to avoid aria-hidden focus warnings
          try {
            var active = document.activeElement;
            if (openModal.contains(active)) {
              // focus body so descendant won't retain focus
              document.body.focus && document.body.focus();
            }
          } catch (e) {}
          openModal.removeAttribute('aria-modal');
          openModal.setAttribute('aria-hidden', 'true');
        
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
        document.body.classList.remove('modal-open');
      }
    }
  });

  // デバッグ用関数
  window.debugHashNav = function() {
    console.log('剥 Debug Hash Navigation');
    console.log('Current hash:', window.location.hash);
    console.log('Collapses:', document.querySelectorAll('[id^="collapse"]').length);
    console.log('Modals:', document.querySelectorAll('.modal').length);
    console.log('jQuery:', typeof jQuery !== 'undefined');
    console.log('Bootstrap:', typeof window.bootstrap !== 'undefined');
    handleHashNavigation();
  };
  
  // 外部から操作するためのAPI
  window.HashNav = {
    process: handleHashNavigation,
    openAccordion: openAccordion,
    openModal: openModal,
    closeAllModals: function() {
      const modals = document.querySelectorAll('.modal.show');
      modals.forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
        try {
          var active = document.activeElement;
          if (modal.contains(active)) {
            document.body.focus && document.body.focus();
          }
        } catch (e) {}
        modal.removeAttribute('aria-modal');
        modal.setAttribute('aria-hidden', 'true');
      });
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) {
        backdrop.remove();
      }
      document.body.classList.remove('modal-open');
    }
  };
})();

