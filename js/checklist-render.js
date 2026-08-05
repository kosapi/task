(function () {
  'use strict';

  function renderChecklist(data) {
    var accordionContainer = document.getElementById('accordion');
    if (!accordionContainer) return;

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
            subModalsHtml +=        item.modalContentHtml;
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
        accordionHtml += '            <a href="#' + escapeHtml(item.targetModalId) + '" class="link-primary" data-bs-toggle="modal" data-bs-target="#' + escapeHtml(item.targetModalId) + '" id="' + escapeHtml(item.linkId) + '">' + item.labelHtml + '</a>';
        accordionHtml += '          </label>';
        accordionHtml += '        </div>';

        if (item.modalContentHtml) {
          accordionHtml += '        <div class="modal fade" id="' + escapeHtml(item.targetModalId) + '" tabindex="-1" aria-labelledby="ModalLabel' + escapeHtml(item.id.replace('Check', '')) + '" aria-hidden="true" data-original-parent="' + escapeHtml(cat.itemsDivId) + '">';
          accordionHtml += '          <div class="modal-dialog modal-dialog-scrollable">';
          accordionHtml += '            <div class="modal-content">';
          accordionHtml +=                item.modalContentHtml;
          accordionHtml += '            </div>';
          accordionHtml += '          </div>';
          accordionHtml += '        </div>';
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

    // カスタムイベント発行
    document.dispatchEvent(new CustomEvent('checklistRendered'));
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Fetch checklist.json
  document.addEventListener('DOMContentLoaded', function () {
    var jsonUrl = 'data/checklist.json?v=' + new Date().getTime();
    fetch(jsonUrl)
      .then(function (response) {
        if (!response.ok) throw new Error('HTTP error ' + response.status);
        return response.json();
      })
      .then(function (data) {
        renderChecklist(data);
      })
      .catch(function (err) {
        console.warn('Failed to load checklist data from relative path, trying API fallback...', err);
        fetch('api/get_checklist.php?v=' + new Date().getTime())
          .then(function (res) { return res.json(); })
          .then(function (data) { renderChecklist(data); })
          .catch(function (err2) {
            console.error('API Fallback failed:', err2);
          });
      });
  });
})();
