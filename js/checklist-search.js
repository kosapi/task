/**
 * チェックリスト チャット検索アシスタント (簡潔版)
 * キーワード入力 → 関連項目を自動検索
 */

// 二重読み込みを防ぐガード
if (window.__checklistAssistantInitialized) {
  console.warn('[ChecklistAssistant] 既に初期化済みのため再実行をスキップ');
} else {
  window.__checklistAssistantInitialized = true;

  console.log('[ChecklistAssistant] スクリプト読み込み開始');

  class ChecklistChatAssistant {
    constructor() {
      this.container = null;
      this.messages = [];
      this.checklistData = [];
      console.log('[ChecklistAssistant] コンストラクタ実行');
      this.init();
    }

    init() {
      console.log('[ChecklistAssistant] init() 実行');
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
          console.log('[ChecklistAssistant] DOMContentLoaded イベント発火');
          this.setup();
        });
      } else {
        console.log('[ChecklistAssistant] DOMは既に準備完了');
        this.setup();
      }
    }

    setup() {
      console.log('[ChecklistAssistant] setup() 実行');
      this.createChatUI();
      this.extractChecklistData();
      
      // スマホのモーダル遅延ロード対応：複数タイミングでh5抽出を試みる
      // 各タイミングで抽出してh5が見つかったか確認
      const extractTimings = [1000, 2000, 3000, 5000, 8000];
      extractTimings.forEach((delay, index) => {
        setTimeout(() => {
          console.log(`[ChecklistAssistant] h5抽出試行 ${index + 1}/${extractTimings.length} (${delay}ms後)`);
          this.extractChecklistData();
        }, delay);
      });
    }

    createChatUI() {
      console.log('[ChecklistAssistant] createChatUI() 実行');
      
      // 既存のチャットとボタンを全て削除
      document.querySelectorAll('#checklist-chat-assistant').forEach(el => el.remove());
      document.querySelectorAll('#chat-toggle-btn').forEach(el => el.remove());
      
      const self = this;
      
      // チャットコンテナの作成
      const chatContainer = document.createElement('div');
      // Checklist search feature has been removed per request.
      console.log('[ChecklistAssistant] Disabled');
      // リンクタイプの場合はモーダルを開く、またはリンク要素にスクロール
      if (type === 'link' && modalId) {
        // まずアコーディオンを開く
        const modal = document.getElementById(modalId);
        if (modal) {
          const originalParent = modal.getAttribute('data-original-parent');
          if (originalParent) {
            const parentElement = document.getElementById(originalParent);
            if (parentElement) {
              const accordionItem = parentElement.closest('.accordion-item');
              if (accordionItem) {
                const collapseElement = accordionItem.querySelector('.accordion-collapse');
                const button = accordionItem.querySelector('[data-bs-toggle="collapse"]');
                if (button && button.classList.contains('collapsed')) {
                  button.click();
                }
                
                // アコーディオンが開いてからモーダルリンクにスクロール
                setTimeout(() => {
                  const modalLink = document.querySelector(`a[data-bs-target="#${modalId}"]`);
                  if (modalLink) {
                    modalLink.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // ハイライト効果
                    const formCheck = modalLink.closest('.form-check');
                    if (formCheck) {
                      formCheck.classList.add('checklist-highlight');
                      setTimeout(() => {
                        formCheck.classList.remove('checklist-highlight');
                      }, 2000);
                    }
                  }
                }, 400);
              }
            }
          }
        }
        return;
      }

      // アコーディオンタイトルの場合：該当セクションを開く
      if (type === 'accordion') {
        if (containerId) {
          const collapse = document.getElementById(containerId);
          const accordionItem = collapse?.closest('.accordion-item');
          if (accordionItem) {
            const button = accordionItem.querySelector('[data-bs-toggle="collapse"]');
            if (button && button.classList.contains('collapsed')) {
              button.click();
            }
            setTimeout(() => {
              accordionItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
              accordionItem.classList.add('checklist-highlight');
              setTimeout(() => accordionItem.classList.remove('checklist-highlight'), 2000);
            }, 300);
          }
        }
        return;
      }

      // h5 見出しの場合
      if (type === 'heading') {
        // モーダル内の見出しならモーダルを開いてスクロール
        if (modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            try {
              const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
              modalInstance.show();
            } catch (e) {
              console.warn('Modal open failed', e);
            }
            setTimeout(() => {
              const headingEl = document.getElementById(containerId);
              if (headingEl) {
                headingEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                headingEl.classList.add('checklist-highlight');
                setTimeout(() => headingEl.classList.remove('checklist-highlight'), 2000);
              }
            }, 400);
          }
          return;
        }

        // アコーディオン内の見出しなら該当セクションを開いてスクロール
        if (containerId) {
          const headingEl = document.getElementById(containerId);
          const accordionItem = headingEl?.closest('.accordion-item');
          if (accordionItem) {
            const button = accordionItem.querySelector('[data-bs-toggle="collapse"]');
            if (button && button.classList.contains('collapsed')) {
              button.click();
            }
          }
          setTimeout(() => {
            if (headingEl) {
              headingEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
              headingEl.classList.add('checklist-highlight');
              setTimeout(() => headingEl.classList.remove('checklist-highlight'), 2000);
            }
          }, 300);
        }
        return;
      }
      
      // チェックボックスタイプの場合
      let targetElement = null;
      if (checkboxId) {
        targetElement = document.getElementById(checkboxId);
      }
      if (!targetElement && containerId) {
        targetElement = document.getElementById(containerId);
      }
      
      if (targetElement) {
        // 親のアコーディオンを開く
        const accordion = targetElement.closest('.accordion-item');
        if (accordion) {
          const button = accordion.querySelector('[data-bs-toggle="collapse"]');
          if (button && button.classList.contains('collapsed')) {
            button.click();
          }
        }
        
        // スクロール
        setTimeout(() => {
          targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
          
          // ハイライト効果
          if (targetElement.closest('.form-check')) {
            targetElement.closest('.form-check').classList.add('checklist-highlight');
            setTimeout(() => {
              targetElement.closest('.form-check').classList.remove('checklist-highlight');
            }, 2000);
          }
        }, 300);
      }
    }
  }

  // グローバルインスタンス
  window.checklistAssistant = new ChecklistChatAssistant();
  window.ChecklistChatAssistant = ChecklistChatAssistant;
  
  console.log('[ChecklistAssistant] 初期化完了');
}

