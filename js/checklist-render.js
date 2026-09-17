(function () {
  'use strict';

  var isRendered = false;

  function renderChecklist(data) {
    var accordionContainer = document.getElementById('accordion');
    if (!accordionContainer) return false;

    window.checklistData = data;
    var accordionHtml = '';
    var subModalsHtml = '';

    data.forEach(function (cat) {
      // categoryId 99 の場合は独立サブモーダルとして処理（アコーディオンには追加しない）
      if (cat.categoryId === 99) {
        cat.items.forEach(function(item) {
          if (item.modalContentHtml) {
            subModalsHtml += '<div class="modal fade" id="' + escapeHtml(item.targetModalId || item.id) + '" tabindex="-1" aria-hidden="true">';
            subModalsHtml += '  <div class="modal-dialog modal-dialog-scrollable">';
            subModalsHtml += '    <div class="modal-content">';
            subModalsHtml +=        prepareLazyImages(item.modalContentHtml);
            subModalsHtml += '    </div>';
            subModalsHtml += '  </div>';
            subModalsHtml += '</div>';
          }
        });
        return;
      }

      accordionHtml += '<div class="accordion-item">';
      accordionHtml += '  <h2 class="accordion-header" id="' + escapeHtml(cat.headingId) + '">';
      accordionHtml += '    <button class="accordion-button collapsed shadow text-reset fw-bold" type="button" data-bs-toggle="collapse"';
      accordionHtml += '      data-bs-target="#' + escapeHtml(cat.collapseId) + '" aria-expanded="false" aria-controls="' + escapeHtml(cat.collapseId) + '">';
      accordionHtml += '      <span class="accordion-title">' + cat.categoryTitle + '</span>';
      accordionHtml += '      <span class="check-count" id="' + escapeHtml(cat.checkCountId) + '"></span>';
      accordionHtml += '      <span class="accordion-arrow"><svg viewBox="0 0 44 44"><polygon points="22,36 38,12 6,12" /></svg></span>';
      accordionHtml += '    </button>';
      accordionHtml += '  </h2>';

      accordionHtml += '  <div id="' + escapeHtml(cat.collapseId) + '" class="accordion-collapse collapse" aria-labelledby="' + escapeHtml(cat.headingId) + '" data-bs-parent="#accordion">';
      accordionHtml += '    <div class="accordion-body">';
      accordionHtml += '      <div id="' + escapeHtml(cat.itemsDivId) + '">';

      cat.items.forEach(function (item) {
        accordionHtml += '        <div class="form-check">';
        accordionHtml += '          <input class="form-check-input" type="checkbox" value="" id="' + escapeHtml(item.id) + '" name="' + escapeHtml(item.name || item.id) + '">';
        accordionHtml += '          <label class="form-check-label" for="' + escapeHtml(item.id) + '">';
        if (item.modalContentHtml && item.modalContentHtml.trim() !== '') {
          accordionHtml += '            <a href="#' + escapeHtml(item.targetModalId) + '" class="link-primary" data-bs-toggle="modal" data-bs-target="#' + escapeHtml(item.targetModalId) + '" id="' + escapeHtml(item.linkId || ('link-' + item.id)) + '">' + item.labelHtml + '</a>';
        } else {
          accordionHtml += '            ' + item.labelHtml;
        }
        accordionHtml += '          </label>';
        accordionHtml += '        </div>';

        if (item.modalContentHtml && item.modalContentHtml.trim() !== '') {
          subModalsHtml += '        <div class="modal fade" id="' + escapeHtml(item.targetModalId) + '" tabindex="-1" aria-labelledby="ModalLabel' + escapeHtml(item.id.replace('Check', '')) + '" aria-hidden="true" data-original-parent="' + escapeHtml(cat.itemsDivId) + '">';
          subModalsHtml += '          <div class="modal-dialog modal-dialog-scrollable">';
          subModalsHtml += '            <div class="modal-content">';
          subModalsHtml +=                prepareLazyImages(item.modalContentHtml);
          subModalsHtml += '            </div>';
          subModalsHtml += '          </div>';
          subModalsHtml += '        </div>';
        }
      });

      accordionHtml += '      </div>';
      accordionHtml += '    </div>';
      accordionHtml += '  </div>';
      accordionHtml += '</div>';
    });

    accordionContainer.innerHTML = accordionHtml;

    // 独立サブモーダルの描画先
    var extraContainer = document.getElementById('extra-submodals-container');
    if (!extraContainer) {
      extraContainer = document.createElement('div');
      extraContainer.id = 'extra-submodals-container';
      document.body.appendChild(extraContainer);
    }
    extraContainer.innerHTML = subModalsHtml;

    isRendered = true;

    // カスタムイベント発行
    document.dispatchEvent(new CustomEvent('checklistRendered'));
    return true;
  }

  // モーダル内の画像を遅延読み込み（Lazy Load）形式に変換する関数
  function prepareLazyImages(html) {
    if (!html) return '';
    // imgタグの src="img/..." を data-lazy-src="img/..." に置き換え、初期srcには極小SVGをセット
    return html.replace(/<img\b([^>]*?)\bsrc=(['"])(img\/[^'"]+)\2([^>]*?)>/gi, function (match, before, quote, src, after) {
      return '<img' + before + 'src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E" data-lazy-src=' + quote + src + quote + ' loading="lazy"' + after + '>';
    });
  }

  // 指定コンテナ内の未読み込み遅延画像を実体化する関数
  function loadContainerImages(container) {
    if (!container) return;
    var lazyImages = container.querySelectorAll('img[data-lazy-src]');
    for (var i = 0; i < lazyImages.length; i++) {
      var img = lazyImages[i];
      var realSrc = img.getAttribute('data-lazy-src');
      if (realSrc) {
        img.src = realSrc;
        img.removeAttribute('data-lazy-src');
      }
    }
  }

  // モーダルが開かれた瞬間に、そのモーダル内部の画像を即座にロード
  document.addEventListener('show.bs.modal', function (event) {
    var modal = event.target;
    if (modal) {
      loadContainerImages(modal);
    }
  });

  // グローバルに提供（nested-modals等からも必要に応じて呼べるようにする）
  window.loadModalImages = loadContainerImages;

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // 1. インライン埋め込みデータによる 0ms 超即時同期描画
  function tryPreloadedRender() {
    var preloadScript = document.getElementById('checklist-preloaded-data');
    if (preloadScript && preloadScript.textContent.trim()) {
      try {
        var data = JSON.parse(preloadScript.textContent);
        if (Array.isArray(data) && data.length > 0) {
          return renderChecklist(data);
        }
      } catch (e) {
        console.warn('Preload parse error:', e);
      }
    }
    return false;
  }

  // グローバル公開
  window.renderPreloadedChecklist = tryPreloadedRender;
  window.renderChecklist = renderChecklist;

  // 可能な限り即時実行を試行
  tryPreloadedRender();

  // 画面ロード完了時の処理（プリロードが未実行だった場合のみフェッチフォールバック）
  document.addEventListener('DOMContentLoaded', function () {
    if (!isRendered) {
      if (tryPreloadedRender()) return;

      var cacheBuster = new Date().getTime();
      var jsonUrl = 'data/checklist.json?nocache=' + cacheBuster;

      fetch(jsonUrl, { cache: 'no-store' })
        .then(function (response) {
          if (!response.ok) throw new Error('HTTP error ' + response.status);
          return response.json();
        })
        .then(function (data) {
          renderChecklist(data);
        })
        .catch(function (err) {
          console.warn('Failed to load checklist data from relative path, trying API fallback...', err);
          fetch('api/get_checklist.php?nocache=' + cacheBuster, { cache: 'no-store' })
            .then(function (res) { return res.json(); })
            .then(function (data) { renderChecklist(data); })
            .catch(function (err2) {
              console.error('API Fallback failed:', err2);
            });
        });
    }
  });
})();
