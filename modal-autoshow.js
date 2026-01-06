// Auto-open accordion and modal based on URL hash
(function() {
  function sleep(ms) {
    return new Promise(function(res) { setTimeout(res, ms); });
  }

  // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ繧帝幕縺城未謨ｰ
  function openAccordion(collapseEl) {
    try {
      console.log('竢ｳ Opening accordion:', collapseEl.id);
      
      // jQuery 縺悟茜逕ｨ蜿ｯ閭ｽ縺狗｢ｺ隱搾ｼ域耳螂ｨ・・
      if (typeof jQuery !== 'undefined') {
        jQuery(collapseEl).collapse('show');
        console.log('笨ｨ Accordion opened via jQuery');
        return;
      }
      
      // Bootstrap API 繧定ｩｦ縺・
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
      
      // Fallback: 謇句虚縺ｧ show 繧ｯ繝ｩ繧ｹ繧定ｿｽ蜉
      console.log('邃ｹ・・Using manual show method');
      collapseEl.classList.add('show');
      
      // 隕ｪ縺ｮ繝懊ち繝ｳ繧よ峩譁ｰ
      const button = collapseEl.closest('.accordion-item')?.querySelector('[data-bs-toggle="collapse"]');
      if (button) {
        button.setAttribute('aria-expanded', 'true');
      }
    } catch (e) {
      console.error('笶・Error opening accordion:', e);
    }
  }

  function handleHashNavigation() {
    try {
      const hash = window.location.hash.replace('#', '').trim();
      if (!hash) {
        console.log('桃 No hash found in URL');
        return;
      }

      console.log('桃 Processing hash:', hash);

      // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ ID 縺ｮ縺ｿ繧貞・逅・ｼ・ollapse0, collapse1, ... collapse8・・
      const collapseMatch = hash.match(/^collapse\d+$/i);
      if (collapseMatch) {
        const collapseId = hash;
        console.log('唐 Looking for accordion:', collapseId);

        // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ繧帝幕縺・
        const collapseEl = document.getElementById(collapseId);
        if (!collapseEl) {
          console.warn('笶・Collapse element with ID "' + collapseId + '" not found');
          const allCollapses = document.querySelectorAll('[id^="collapse"]');
          console.log('Available collapse elements:', Array.from(allCollapses).map(el => el.id));
          return;
        }

        console.log('笨・Found collapse element');
        
        // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ繧帝幕縺・
        openAccordion(collapseEl);

        // 繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ
        try {
          const accordionItem = collapseEl.closest('.accordion-item');
          if (accordionItem) {
            console.log('糖 Scrolling to accordion item');
            setTimeout(() => {
              accordionItem.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
          }
        } catch (e) {
          console.warn('笞・・Scroll error:', e.message);
        }
        return;
      }

      // 繝｢繝ｼ繝繝ｫ繝上ャ繧ｷ繝･繧貞・逅・ｼ・odal0-3 縺ｪ縺ｩ縲…ollapse 縺ｧ縺ｯ縺ｪ縺・ID・・
      const modalEl = document.getElementById(hash);
      if (modalEl && modalEl.classList.contains('modal')) {
        console.log('ｪ・Opening modal:', hash);
        openModal(modalEl);
        
        // 繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ
        try {
          setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }, 100);
        } catch (e) {}
      } else {
        console.log('笞・・Element with ID "' + hash + '" not found or not a modal');
      }

    } catch (e) {
      console.error('笶・Error in handleHashNavigation:', e);
    }
  }

  function openModal(modalEl) {
    try {
      console.log('竢ｳ Opening modal:', modalEl.id);
      console.log('投 Modal element:', modalEl);
      console.log('投 Modal classes before:', modalEl.className);
      console.log('投 Modal display before:', modalEl.style.display);
      
      // **驥崎ｦ・*: 繝｢繝ｼ繝繝ｫ繧鍛ody縺ｫ遘ｻ蜍包ｼ医い繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ蜀・・鄂ｮ縺ｫ繧医ｋ髱櫁｡ｨ遉ｺ繧貞屓驕ｿ・・
      if (modalEl.parentElement.id !== 'modal-container' && modalEl.parentElement.tagName !== 'BODY') {
        console.log('売 Moving modal to body (was in', modalEl.parentElement.id, ')');
        // 蜈・・隕ｪ隕∫ｴ縺ｮID繧定ｨ倬鹸・・ive_editor縺ｧ蜈・・菴咲ｽｮ繧堤音螳壹☆繧九◆繧・ｼ・
        if (!modalEl.hasAttribute('data-original-parent')) {
          modalEl.setAttribute('data-original-parent', modalEl.parentElement.id);
        }
        document.body.appendChild(modalEl);
      }
      
      // 譁ｹ豕・: Bootstrap Modal API・域怙繧ら｢ｺ螳滂ｼ・
      if (window.bootstrap && typeof bootstrap.Modal === 'function') {
        try {
          const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
          console.log('逃 Bootstrap Modal instance:', inst);
          inst.show();
          
          // Bootstrap CSS縺ｮ繝舌げ蝗樣∩: fade 繧ｯ繝ｩ繧ｹ繧貞炎髯､縲‥isplay 繧・block 縺ｫ險ｭ螳・
          setTimeout(() => {
            // Bootstrap 縺ｮ fade 繧ｯ繝ｩ繧ｹ繧貞炎髯､・医い繝九Γ繝ｼ繧ｷ繝ｧ繝ｳ蜉ｹ譫懊′蟷ｲ貂峨＠縺ｦ縺・ｋ蜿ｯ閭ｽ諤ｧ・・
            modalEl.classList.remove('fade');
            console.log('肌 Removed fade class');
            
            // 繝｢繝ｼ繝繝ｫ閾ｪ菴薙・繧ｹ繧ｿ繧､繝ｫ繧貞ｼｷ蛻ｶ險ｭ螳夲ｼ・lock 縺ｧ邨ｱ荳・・
            modalEl.style.display = 'block';
            modalEl.style.position = 'fixed';
            modalEl.style.top = '0';
            modalEl.style.left = '0';
            modalEl.style.width = '100%';
            modalEl.style.height = '100%';
            modalEl.style.zIndex = '1055';
            modalEl.style.overflow = 'auto';
            console.log('肌 Fixed modal styles (block display)');
            
            // .modal-dialog 縺ｫ蝗ｺ螳壹し繧､繧ｺ繧定ｨｭ螳・
            const dialog = modalEl.querySelector('.modal-dialog');
            if (dialog) {
              // Bootstrap 縺ｮ modal-dialog-scrollable 繧貞炎髯､
              dialog.classList.remove('modal-dialog-scrollable');
              
              // 蝗ｺ螳壹し繧､繧ｺ縺ｧ驟咲ｽｮ
              dialog.style.display = 'block';
              dialog.style.width = '500px';
              dialog.style.maxWidth = '90%';
              dialog.style.height = '500px'; // 蝗ｺ螳夐ｫ倥＆
              dialog.style.margin = '50px auto'; // 荳ｭ螟ｮ驟咲ｽｮ
              dialog.style.position = 'relative';
              console.log('肌 Fixed modal-dialog styles (block, fixed height 500px)');
              
              // .modal-content 縺ｫ繧ゅせ繧ｿ繧､繝ｫ繧定ｨｭ螳・
              const content = dialog.querySelector('.modal-content');
              if (content) {
                content.style.display = 'flex';
                content.style.flexDirection = 'column';
                content.style.width = '100%';
                content.style.height = '100%';
                content.style.overflow = 'hidden';
                console.log('肌 Fixed modal-content styles');
              }
              
              // .modal-body 縺ｮ繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ險ｭ螳・
              const body = dialog.querySelector('.modal-body');
              if (body) {
                body.style.flex = '1';
                body.style.overflowY = 'auto';
                body.style.overflowX = 'hidden';
                console.log('肌 Fixed modal-body styles');
              }
              
              // 繝ｬ繧､繧｢繧ｦ繝医・蜀崎ｨ育ｮ励ｒ蠑ｷ蛻ｶ
              void dialog.offsetHeight;
              void modalEl.offsetHeight;
              
              // 繧ｵ繧､繧ｺ遒ｺ隱・
              const rect = dialog.getBoundingClientRect();
              console.log('剥 Dialog size:', {width: rect.width, height: rect.height});
              
              if (rect.width > 0 && rect.height > 0) {
                console.log('笨・SUCCESS! Modal is now visible!');
              } else {
                console.error('笶・Dialog still has 0 size');
              }
            }
          }, 50);
          
          // show() 螳溯｡悟ｾ後・迥ｶ諷九ｒ遒ｺ隱・
          setTimeout(() => {
            console.log('投 Modal classes after show():', modalEl.className);
            console.log('投 Modal display after show():', modalEl.style.display);
            console.log('投 Modal aria-modal:', modalEl.getAttribute('aria-modal'));
            console.log('投 Body classes:', document.body.className);
            
            const backdrop = document.querySelector('.modal-backdrop');
            console.log('投 Backdrop exists:', !!backdrop);
            
            // 險育ｮ励＆繧後◆繧ｹ繧ｿ繧､繝ｫ繧堤｢ｺ隱・
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
            
            // 繝｢繝ｼ繝繝ｫ縺檎判髱｢荳翫↓隕九∴繧九°繝√ぉ繝・け
            const rect = modalEl.getBoundingClientRect();
            console.log('棟 Modal position:', {
              top: rect.top,
              left: rect.left,
              width: rect.width,
              height: rect.height,
              visible: rect.width > 0 && rect.height > 0
            });
            
            // .modal-dialog 縺ｮ繧ｹ繧ｿ繧､繝ｫ繧堤｢ｺ隱・
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
              
              // .modal-content 縺ｮ繧ｹ繧ｿ繧､繝ｫ繧堤｢ｺ隱・
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
                
                // modal-header 縺ｨ modal-body 縺ｮ繧ｵ繧､繧ｺ繧堤｢ｺ隱・
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
              
              // 繝｢繝ｼ繝繝ｫ譛ｬ菴薙・flex繧ｹ繧ｿ繧､繝ｫ繧堤｢ｺ隱・
              console.log('耳 Modal display:', modalStyles.display);
              console.log('耳 Modal align-items:', modalStyles.alignItems);
              console.log('耳 Modal justify-content:', modalStyles.justifyContent);
              
            } else {
              console.error('笶・.modal-dialog not found inside modal!');
            }
          }, 100);
          
          console.log('笨ｨ Modal opened via Bootstrap Modal API');
          return;
        } catch (e) {
          console.warn('笞・・Bootstrap Modal error:', e.message);
        }
      }
      
      // 譁ｹ豕・: jQuery 繧剃ｽｿ縺｣縺滓婿豕・
      if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery(modalEl).modal('show');
        console.log('笨ｨ Modal opened via jQuery .modal("show")');
        return;
      }
      
      // 譁ｹ豕・: 繝｢繝ｼ繝繝ｫ繧帝幕縺上・繧ｿ繝ｳ繧呈爾縺励※繧ｯ繝ｪ繝・け
      const modalButton = document.querySelector(`[data-bs-target="#${modalEl.id}"]`);
      if (modalButton) {
        console.log('笨・Found modal button, clicking it');
        modalButton.click();
        console.log('笨ｨ Modal opened via button click');
        return;
      }

      // 譁ｹ豕・: 謇句虚縺ｧ繝｢繝ｼ繝繝ｫ繧定｡ｨ遉ｺ
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

      // 繝輔か繝ｼ繧ｫ繧ｹ
      const focusTarget = modalEl.querySelector('button, [tabindex], a') || modalEl;
      if (focusTarget && focusTarget.focus) {
        focusTarget.focus();
      }

      console.log('笨ｨ Modal opened via fallback method');
    } catch (e) {
      console.error('笶・Error opening modal:', e);
    }
  }

  // 繝壹・繧ｸ隱ｭ縺ｿ霎ｼ縺ｿ螳御ｺ・ｾ後↓繝上ャ繧ｷ繝･繝翫ン繧ｲ繝ｼ繧ｷ繝ｧ繝ｳ繧貞・逅・
  function initHashNavigation() {
    console.log('噫 Initializing hash navigation...');
    console.log('搭 Current hash:', window.location.hash);
    console.log('塘 Document ready state:', document.readyState);
    
    // 縺吶∋縺ｦ縺ｮ繝｢繝ｼ繝繝ｫ縺ｮ蜈・・隕ｪ隕∫ｴ繧定ｨ倬鹸・・ive_editor逕ｨ・・
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
    
    // 繝上ャ繧ｷ繝･縺後≠繧後・縲．OM縺ｮ貅門ｙ螳御ｺ・ｒ蠕・◆縺壹↓蜃ｦ逅・幕蟋・
    if (document.readyState === 'loading') {
      console.log('塘 Page still loading, waiting for DOMContentLoaded...');
      document.addEventListener('DOMContentLoaded', function() {
        console.log('笨・DOMContentLoaded fired');
        recordModalOriginalParents();
        // Bootstrap 縺悟ｮ悟・縺ｫ蛻晄悄蛹悶＆繧後ｋ縺ｾ縺ｧ蠕・▽
        setTimeout(handleHashNavigation, 800);
      });
    } else {
      console.log('笨・Page already loaded, processing hash immediately');
      recordModalOriginalParents();
      // 繝壹・繧ｸ縺梧里縺ｫ隱ｭ縺ｿ霎ｼ縺ｾ繧後※縺・ｋ蝣ｴ蜷・
      setTimeout(handleHashNavigation, 300);
    }
    
    // 繝壹・繧ｸ隱ｭ縺ｿ霎ｼ縺ｿ螳御ｺ・ｾ後↓繧ゅ≧荳蠎ｦ蜃ｦ逅・ｼ亥ｿｵ縺ｮ縺溘ａ・・
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

  // 蛻晄悄蛹門ｮ溯｡・
  initHashNavigation();

  // 繝悶Λ繧ｦ繧ｶ縺ｮ謌ｻ繧・騾ｲ繧譎ゅ↓繧ゅワ繝・す繝･螟画峩繧貞・逅・
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

  // Escape 繧ｭ繝ｼ縺ｧ繝｢繝ｼ繝繝ｫ繧帝哩縺倥ｋ
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const openModal = document.querySelector('.modal.show');
      if (openModal) {
        console.log('爆 Closing modal with Escape key');
        openModal.classList.remove('show');
        openModal.style.display = 'none';
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

  // 繧ｰ繝ｭ繝ｼ繝舌Ν髢｢謨ｰ縺ｨ縺励※蜈ｬ髢具ｼ医ョ繝舌ャ繧ｰ逕ｨ・・
  window.debugHashNav = function() {
    console.log('剥 Debug Hash Navigation');
    console.log('Current hash:', window.location.hash);
    console.log('Collapses:', document.querySelectorAll('[id^="collapse"]').length);
    console.log('Modals:', document.querySelectorAll('.modal').length);
    console.log('jQuery:', typeof jQuery !== 'undefined');
    console.log('Bootstrap:', typeof window.bootstrap !== 'undefined');
    handleHashNavigation();
  };
  
  // 繧ｨ繧ｯ繧ｹ繝昴・繝茨ｼ亥挨縺ｮ蝣ｴ謇縺九ｉ繧ょ他縺ｳ蜃ｺ縺帙ｋ繧医≧縺ｫ・・
  window.HashNav = {
    process: handleHashNavigation,
    openAccordion: openAccordion,
    openModal: openModal,
    closeAllModals: function() {
      const modals = document.querySelectorAll('.modal.show');
      modals.forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
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

