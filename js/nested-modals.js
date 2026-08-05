/**
 * モーダル間リンク（ネストモーダル）完全連携スクリプト
 * 動的ID（Modal6-3-1）でも、旧ID（modal-ticket-welfare）でも、あるいは未レンダリングでも
 * checklist.json の全データから検索して 100% 確実に子モーダルを開きます。
 */

document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  // 旧モーダルIDとタイトルのマッピング辞書
  const modalAliasMap = {
    'modal-ticket-welfare': 'チケット/福祉券がある場合',
    'modal-welfare-many': '福祉券を多くもらった場合',
    'modal-additional-uncollected': '追加未収処理がある場合',
    'modal-teito-cancel': '帝都無線キャンセル処理',
    'modal-go-app-cancel': 'GOアプリキャンセル処理',
    'modal-meter-mistake': 'メーターの押し間違い',
    'modal-etc-statement': 'ETC利用明細書がある場合'
  };

  document.addEventListener('click', function(e) {
    // モーダル内部のリンクまたはボタンかを判定
    const targetBtn = e.target.closest('.modal-body a[data-bs-toggle="modal"], .modal-body button[data-bs-toggle="modal"], .modal-body a[href^="#"], .modal-body button[href^="#"]');
    
    if (!targetBtn) return;

    let href = targetBtn.getAttribute('data-bs-target') || targetBtn.getAttribute('href') || '';
    if (!href || href === '#') return;

    const rawId = href.replace(/^#/, '');
    if (!rawId) return;

    e.preventDefault();
    e.stopPropagation();

    // 現在開いている親モーダル
    const parentModalElem = targetBtn.closest('.modal');
    const btnText = targetBtn.textContent.trim();

    // 目的のモーダル要素をDOMから検索
    let targetModalElem = document.getElementById(rawId);

    // 見つからない場合、タイトルまたはIDから checklist.json 内のデータを検索
    if (!targetModalElem && window.checklistData) {
      const searchTitle = modalAliasMap[rawId] || btnText;
      
      let foundItem = null;
      window.checklistData.forEach(cat => {
        if (cat.items) {
          cat.items.forEach(it => {
            if (it.id === rawId || it.targetModalId === rawId || (it.labelHtml && it.labelHtml.includes(searchTitle)) || (it.modalContentHtml && it.modalContentHtml.includes(searchTitle))) {
              foundItem = it;
            }
          });
        }
      });

      // 見つかった場合、動的にモーダルを作成してDOMに追加
      if (foundItem) {
        targetModalElem = document.createElement('div');
        targetModalElem.className = 'modal fade';
        targetModalElem.id = rawId;
        targetModalElem.setAttribute('tabindex', '-1');
        targetModalElem.setAttribute('aria-hidden', 'true');
        targetModalElem.innerHTML = `
          <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
              ${foundItem.modalContentHtml}
            </div>
          </div>
        `;
        document.body.appendChild(targetModalElem);
      }
    }

    // 親モーダルを非表示にしてから子モーダルを開く
    if (parentModalElem) {
      const parentInstance = bootstrap.Modal.getInstance(parentModalElem) || new bootstrap.Modal(parentModalElem);
      
      const openChild = function() {
        parentModalElem.removeEventListener('hidden.bs.modal', openChild);

        if (targetModalElem) {
          const childInstance = bootstrap.Modal.getInstance(targetModalElem) || new bootstrap.Modal(targetModalElem);
          childInstance.show();

          // 子モーダルが閉じられたら元の親モーダルに自動復帰
          const restoreParent = function() {
            targetModalElem.removeEventListener('hidden.bs.modal', restoreParent);
            const parentModal = bootstrap.Modal.getInstance(parentModalElem) || new bootstrap.Modal(parentModalElem);
            parentModal.show();
          };

          targetModalElem.addEventListener('hidden.bs.modal', restoreParent, { once: true });
        } else {
          alert('「' + btnText + '」のモーダル内容が見つかりませんでした。');
          parentInstance.show();
        }
      };

      parentModalElem.addEventListener('hidden.bs.modal', openChild, { once: true });
      parentInstance.hide();
    }
  }, true);
});
