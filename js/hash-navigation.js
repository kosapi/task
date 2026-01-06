// 繝上ャ繧ｷ繝･繝翫ン繧ｲ繝ｼ繧ｷ繝ｧ繝ｳ遒ｺ螳溷ｮ溯｡・
// modal-autoshow.js 縺ｮ隱ｭ縺ｿ霎ｼ縺ｿ螳御ｺ・ｒ蠕・◆縺壹↓蛻晄悄蛹・
window.addEventListener('load', function() {
  setTimeout(function() {
    const hash = window.location.hash.replace('#', '').trim();
    // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ縺ｾ縺溘・繝｢繝ｼ繝繝ｫ繝上ャ繧ｷ繝･繧貞・逅・
    if (hash && typeof window.HashNav !== 'undefined') {
      console.log('噫 Forcing hash navigation on page load:', window.location.hash);
      window.HashNav.process();
    }
  }, 200);
});

// 繧ｰ繝ｭ繝ｼ繝舌Ν繝・せ繝磯未謨ｰ・医さ繝ｳ繧ｽ繝ｼ繝ｫ縺九ｉ蜻ｼ縺ｳ蜃ｺ縺怜庄・・
window.testAccordion = function(collapseId) {
  if (!collapseId) collapseId = 'collapse0';
  console.log('ｧｪ Testing accordion:', collapseId);
  window.location.hash = collapseId;
  setTimeout(() => {
    if (typeof window.HashNav !== 'undefined') {
      console.log('売 Calling window.HashNav.process()');
      window.HashNav.process();
    } else {
      console.warn('笞・・window.HashNav is not available');
      // 繝輔か繝ｼ繝ｫ繝舌ャ繧ｯ
      if (typeof window.debugHashNav === 'function') {
        console.log('売 Calling window.debugHashNav() as fallback');
        window.debugHashNav();
      }
    }
  }, 100);
};

