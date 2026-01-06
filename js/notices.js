/**
 * お知らせ表示機能 - モーダル版
 */
class NoticeManager {
  constructor() {
    this.notices = [];
    this.dismissedNotices = this.loadDismissedNotices();
    this.loadNotices();
  }

  // localStorage から「今後表示しない」に指定されたお知らせのIDを取得
  loadDismissedNotices() {
    const dismissed = localStorage.getItem('dismissedNotices');
    return dismissed ? JSON.parse(dismissed) : [];
  }

  // localStorage に「今後表示しない」のIDを保存
  saveDismissedNotices() {
    localStorage.setItem('dismissedNotices', JSON.stringify(this.dismissedNotices));
  }

  // お知らせを「今後表示しない」に登録
  dismissNotice(noticeId) {
    if (!this.dismissedNotices.includes(noticeId)) {
      this.dismissedNotices.push(noticeId);
      this.saveDismissedNotices();
    }
  }

  loadNotices() {
    console.log('[NoticeManager] お知らせを読み込み中...');
    fetch('api/get-notices.php')
      .then(response => {
        console.log('[NoticeManager] レスポンスステータス:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('[NoticeManager] 取得したお知らせ:', data);
        this.notices = Array.isArray(data) ? data : [];
        this.displayNotices();
      })
      .catch(error => {
        console.error('[NoticeManager] エラー:', error);
        this.notices = [];
      });
  }

  displayNotices() {
    console.log('[NoticeManager] 表示対象のお知らせ数:', this.notices.length);
    
    if (this.notices.length === 0) {
      console.log('[NoticeManager] お知らせがありません');
      return;
    }

    // 表示対象のお知らせのみフィルタリング（display=1 かつ dismissed されていない）
    const displayNotices = this.notices.filter(notice => {
      const isDisplayed = notice.display === 1 || notice.display === '1' || notice.display === true;
      const isDismissed = this.dismissedNotices.includes(notice.id);
      const shouldDisplay = isDisplayed && !isDismissed;
      console.log('[NoticeManager]', notice.title, '- 表示:', shouldDisplay, '(display:', isDisplayed, ', dismissed:', isDismissed, ')');
      return shouldDisplay;
    });

    if (displayNotices.length === 0) {
      console.log('[NoticeManager] 表示対象のお知らせがありません');
      return;
    }

    // 画像のように集約モーダルで表示
    this.createAndShowNoticeCenter(displayNotices);
  }

  // 集約モーダル（ヘッダーに件数、本文にカード一覧）
  createAndShowNoticeCenter(notices) {
    const centerId = 'notice-center-modal';
    if (document.getElementById(centerId)) {
      // 既存があれば一旦消して再生成
      document.getElementById(centerId).parentElement.remove();
    }

    const count = notices.length;
    const headerBlue = '#0d6efd';
    const headerBlueDark = this.adjustColor(headerBlue, -15);

    const makeTypePill = (type) => {
      const map = {
        info:  { bg: '#e7f1ff', text: '#0d6efd', label: '情報' },
        success: { bg: '#e8f5e9', text: '#198754', label: '成功' },
        warning: { bg: '#fff8e1', text: '#ff9900', label: '警告' },
        danger: { bg: '#ffebee', text: '#dc3545', label: '重要' }
      };
      const m = map[type] || map.info;
      return `<span style="background:${m.bg}; color:${m.text}; padding:4px 10px; border-radius:999px; font-size:13px; font-weight:600;">${m.label}</span>`;
    };

    const makeCard = (n) => {
      const title = this.escapeHtml(n.title);
      const content = this.escapeHtml(n.content).replace(/\n/g, '<br>');
      const created = this.escapeHtml(n.created_at || '');
      return `
        <div class="notice-card" data-id="${n.id}" style="background:#fff; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.08); border:1px solid #eef0f4; overflow:hidden;">
          <div style="padding:18px 18px 12px 18px; display:flex; align-items:center; gap:12px;">
            <div style="width:28px; height:28px; border-radius:6px; background:#f2f4f7; color:#4a5568; display:flex; align-items:center; justify-content:center; font-size:16px;">
              <i class="bi bi-info-lg"></i>
            </div>
            <div style="flex:1; font-weight:700; font-size:18px; color:#222;">${title}</div>
            <div>${makeTypePill(n.type)}</div>
          </div>
          <div style="padding:0 18px 14px 58px; color:#333; font-size:15px; line-height:1.85;">
            ${content}
          </div>
          <div style="padding:10px 14px; border-top:1px solid #eef0f4; display:flex; align-items:center; justify-content:space-between; gap:12px; background:#fafbfc;">
            <div style="color:#5f6b7a; font-size:14px; display:flex; align-items:center; gap:8px;">
              <i class="bi bi-calendar-event"></i>
              <span>${created}</span>
            </div>
            <label style="display:flex; align-items:center; gap:8px; font-size:14px; color:#333; cursor:pointer;">
              <input type="checkbox" class="form-check-input notice-dismiss-checkbox" data-id="${n.id}" style="margin:0; transform:scale(1.1);" />
              今後表示しない
            </label>
          </div>
        </div>
      `;
    };

    const cardsHtml = notices.map(makeCard).join('<div style="height:14px"></div>');

    const modalHtml = `
      <div class="modal fade" id="${centerId}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content" style="border:none; border-radius:12px; overflow:hidden; max-height:78vh;">
            <div style="background:linear-gradient(135deg, ${headerBlue} 0%, ${headerBlueDark} 100%); padding:14px 18px; color:#fff; display:flex; align-items:center; justify-content:space-between;">
              <div style="display:flex; align-items:center; gap:10px;">
                <i class="bi bi-megaphone" style="font-size:20px;"></i>
                <div style="font-weight:700; letter-spacing:0.2px;">お知らせ</div>
              </div>
              <div style="background:rgba(255,255,255,0.15); color:#fff; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600;">${count}件</div>
            </div>
            <div class="modal-body" style="background:#f1f3f5; padding:16px; overflow:auto;">
              ${cardsHtml}
            </div>
            <div class="modal-footer" style="background:#fff; border-top:1px solid #e9ecef; padding:10px 14px; display:flex; gap:8px; justify-content:flex-end;">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:6px; padding:6px 14px;">
                閉じる
              </button>
              <button type="button" class="btn btn-primary btn-sm notice-bulk-dismiss" style="border-radius:6px; padding:6px 14px;">
                一括非表示
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    const container = document.getElementById('notices-container') || document.body;
    const wrap = document.createElement('div');
    wrap.innerHTML = modalHtml;
    container.appendChild(wrap.firstElementChild);

    const modal = new bootstrap.Modal(document.getElementById(centerId));
    modal.show();

    // 各チェックボックスで個別非表示
    document.querySelectorAll('#' + centerId + ' .notice-dismiss-checkbox').forEach(cb => {
      cb.addEventListener('change', (e) => {
        const id = e.target.dataset.id;
        if (e.target.checked) {
          this.dismissNotice(id);
          // カードを薄く
          const card = e.target.closest('.notice-card');
          if (card) card.style.opacity = '0.5';
        } else {
          // undo は仕様外だが見た目だけ戻す
          const card = e.target.closest('.notice-card');
          if (card) card.style.opacity = '1';
        }
      });
    });

    // 一括非表示
    document.querySelector('#' + centerId + ' .notice-bulk-dismiss').addEventListener('click', () => {
      notices.forEach(n => this.dismissNotice(n.id));
      modal.hide();
    });

    // 閉じたらDOM除去
    document.getElementById(centerId).addEventListener('hidden.bs.modal', () => {
      const el = document.getElementById(centerId);
      if (el) el.parentElement.remove();
    });
  }

  createAndShowModal(notice) {
    const modalId = `notice-modal-${notice.id}`;
    
    // すでにモーダルが存在する場合はスキップ
    if (document.getElementById(modalId)) {
      return;
    }

    const colorMap = {
      info: { bg: '#0d6efd', light: '#e7f1ff', border: '#0d6efd' },
      success: { bg: '#198754', light: '#e8f5e9', border: '#198754' },
      warning: { bg: '#ffc107', light: '#fff8e1', border: '#ffc107' },
      danger: { bg: '#dc3545', light: '#ffebee', border: '#dc3545' }
    };

    const colors = colorMap[notice.type] || colorMap.info;
    const icons = {
      info: 'info-circle-fill',
      success: 'check-circle-fill',
      warning: 'exclamation-circle-fill',
      danger: 'exclamation-circle-fill'
    };

    const icon = icons[notice.type] || 'info-circle-fill';
    const title = this.escapeHtml(notice.title);
    const content = this.escapeHtml(notice.content).replace(/\n/g, '<br>');

    // モーダルHTMLを生成
    const modalHtml = `
      <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); overflow: hidden; max-height: 70vh; display: flex;">
            <div style="background: linear-gradient(135deg, ${colors.bg} 0%, ${this.adjustColor(colors.bg, -20)} 100%); padding: 16px; color: white; display: flex; align-items: center; gap: 12px;">
              <i class="bi bi-${icon}" style="font-size: 32px; opacity: 0.9;"></i>
              <h5 class="modal-title" id="${modalId}Label" style="margin: 0; font-size: 18px; font-weight: 600; letter-spacing: 0.3px;">
                ${title}
              </h5>
            </div>
            <div class="modal-body" style="padding: 16px; background: #f8f9fa; overflow-y: auto; max-height: calc(70vh - 120px);">
              <div style="color: #333; font-size: 15px; line-height: 1.75; letter-spacing: 0.2px;">
                ${content}
              </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e0e0e0; padding: 12px 16px; background: white; gap: 8px;">
              <button type="button" class="btn" style="background-color: #f0f0f0; color: #333; border: none; border-radius: 6px; padding: 6px 16px; font-weight: 500; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#e0e0e0'" onmouseout="this.style.backgroundColor='#f0f0f0'" data-bs-dismiss="modal">
                <i class="bi bi-x-lg" style="margin-right: 6px;"></i>閉じる
              </button>
              <button type="button" class="btn dismiss-notice-btn" style="background: linear-gradient(135deg, ${colors.bg} 0%, ${this.adjustColor(colors.bg, -15)} 100%); color: white; border: none; border-radius: 6px; padding: 6px 16px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'" data-notice-id="${notice.id}">
                <i class="bi bi-check-circle" style="margin-right: 6px;"></i>今後表示しない
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    // モーダルをDOMに追加
    const container = document.getElementById('notices-container') || document.body;
    const modalElement = document.createElement('div');
    modalElement.innerHTML = modalHtml;
    container.appendChild(modalElement.firstElementChild);

    // モーダルを表示
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();

    // 「今後表示しない」ボタンのイベントリスナーを追加
    document.querySelector(`#${modalId} .dismiss-notice-btn`).addEventListener('click', (e) => {
      const noticeId = e.target.closest('.dismiss-notice-btn').dataset.noticeId;
      this.dismissNotice(noticeId);
      modal.hide();
    });

    // モーダルが閉じられたら、DOMから削除
    document.getElementById(modalId).addEventListener('hidden.bs.modal', () => {
      document.getElementById(modalId).parentElement.remove();
    });
  }

  // 色を調整するヘルパーメソッド
  adjustColor(color, percent) {
    const num = parseInt(color.replace('#',''), 16);
    const amt = Math.round(2.55 * percent);
    const R = Math.max(0, Math.min(255, (num >> 16) + amt));
    const G = Math.max(0, Math.min(255, (num >> 8 & 0x00FF) + amt));
    const B = Math.max(0, Math.min(255, (num & 0x0000FF) + amt));
    return '#' + (0x1000000 + R*0x10000 + G*0x100 + B).toString(16).slice(1);
  }

  escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// ページ読み込み時に初期化
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.noticeManager = new NoticeManager();
  });
} else {
  window.noticeManager = new NoticeManager();
}

