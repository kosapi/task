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
      
      // データ抽出を遅延実行して確実に全DOMを取得（スマホのh5対応）
      setTimeout(() => {
        console.log('[ChecklistAssistant] 遅延データ再抽出');
        this.extractChecklistData();
      }, 2000);
    }

    createChatUI() {
      console.log('[ChecklistAssistant] createChatUI() 実行');
      
      // 既存のチャットとボタンを全て削除
      document.querySelectorAll('#checklist-chat-assistant').forEach(el => el.remove());
      document.querySelectorAll('#chat-toggle-btn').forEach(el => el.remove());
      
      const self = this;
      
      // チャットコンテナの作成
      const chatContainer = document.createElement('div');
      chatContainer.id = 'checklist-chat-assistant';
      chatContainer.className = 'chat-assistant';
      chatContainer.innerHTML = `
        <div class="chat-header">
          <i class="bi bi-chat-dots"></i>
          <span>チェックリスト検索</span>
          <div style="flex: 1;"></div>
          <button class="chat-close-btn" id="chatCloseBtn">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="chat-messages" id="chatMessages"></div>
        <div class="chat-input-area">
          <div class="chat-input-wrapper">
            <input 
              type="text" 
              class="chat-input" 
              id="chatInput" 
              placeholder="キーワード検索..." 
              autocomplete="off"
            >
            <button class="chat-clear-btn" id="chatClearBtn" style="display: none;">
              <i class="bi bi-x-circle-fill"></i>
            </button>
          </div>
          <button class="chat-send-btn" id="chatSendBtn">
            <i class="bi bi-send"></i>
          </button>
        </div>
      `;

      // 開くボタンを作成
      const toggleButton = document.createElement('button');
      toggleButton.id = 'chat-toggle-btn';
      toggleButton.className = 'chat-toggle-btn';
      toggleButton.innerHTML = '<i class="bi bi-search"></i>';
      toggleButton.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 999999; pointer-events: auto; cursor: pointer;';

      // 安全標語セクションの虫眼鏡ボタンは index.html に直接記述済み
      console.log('[ChecklistAssistant] 虫眼鏡ボタンはHTMLに設置済み');

      // accordion の後にチャットコンテナを挿入
      const accordion = document.getElementById('accordion');
      if (accordion && accordion.parentNode) {
        console.log('[ChecklistAssistant] accordion 発見、その後に挿入');
        accordion.parentNode.insertBefore(chatContainer, accordion.nextSibling);
        accordion.parentNode.insertBefore(toggleButton, accordion.nextSibling);
      } else {
        document.body.appendChild(chatContainer);
        document.body.appendChild(toggleButton);
      }

      // 初期状態：チャットを非表示、ボタンを表示
      chatContainer.classList.add('chat-hidden');
      console.log('[ChecklistAssistant] 初期状態設定完了');

      // イベントリスナーを設定
      setTimeout(() => {
        // チャット開く/閉じる機能
        const toggleChat = (shouldOpen) => {
          const chat = document.getElementById('checklist-chat-assistant');
          const toggleBtn = document.getElementById('chat-toggle-btn');
          const toggleBtnHeader = document.getElementById('chatToggleBtnHeader');
          
          if (!chat) return;
          
          if (shouldOpen === undefined) {
            shouldOpen = chat.classList.contains('chat-hidden');
          }
          
          if (shouldOpen) {
            // チャットを開く
            chat.classList.remove('chat-hidden');
            if (toggleBtn) toggleBtn.classList.add('hidden');
            
            // keyboard-visibleクラスを削除（画面の4分の1で固定）
            chat.classList.remove('keyboard-visible');
            chat.style.cssText = '';
            
            // モバイルでも入力欄にフォーカスを当てる
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
              setTimeout(() => {
                const input = document.getElementById('chatInput');
                if (input) input.focus();
              }, 300);
            }
          } else {
            // チャットを閉じる
            chat.classList.add('chat-hidden');
            chat.classList.remove('keyboard-visible');
            chat.style.cssText = '';
            if (toggleBtn) toggleBtn.classList.remove('hidden');
            if (self && typeof self.resetSearchState === 'function') {
              self.resetSearchState();
            }
          }
        };
        
        // 外側の虫眼鏡ボタン
        const btn = document.getElementById('chat-toggle-btn');
        if (btn) {
          btn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleChat();
          };
        }
        
        // ヘッダー内の虫眼鏡ボタン（チャットを開く）
        const toggleBtnHeader = document.getElementById('chatToggleBtnHeader');
        if (toggleBtnHeader) {
          toggleBtnHeader.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleChat();
          };
        }

        const closeBtn = document.getElementById('chatCloseBtn');
        if (closeBtn) {
          const closeHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const chat = document.getElementById('checklist-chat-assistant');
            const toggleBtn = document.getElementById('chat-toggle-btn');
            if (chat) {
              chat.classList.add('chat-hidden');
              chat.classList.remove('keyboard-visible');
              chat.style.cssText = '';
            }
            if (toggleBtn) toggleBtn.classList.remove('hidden');
            if (self && typeof self.resetSearchState === 'function') {
              self.resetSearchState();
            }
          };
          closeBtn.onclick = closeHandler;
          closeBtn.ontouchstart = closeHandler;
        }

        const sendBtn = document.getElementById('chatSendBtn');
        if (sendBtn) {
          sendBtn.onclick = function(e) {
            e.preventDefault();
            if (window.checklistAssistant) {
              window.checklistAssistant.sendMessage();
            }
          };
        }

        const clearBtn = document.getElementById('chatClearBtn');
        const chatInput = document.getElementById('chatInput');
        if (clearBtn && chatInput) {
          // 入力欄の値が変更されたらクリアボタンの表示を切り替え
          chatInput.addEventListener('input', function() {
            if (chatInput.value.trim().length > 0) {
              clearBtn.style.display = 'block';
            } else {
              clearBtn.style.display = 'none';
            }
          });

          // クリアボタンをクリックしたら入力欄をクリア
          clearBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            chatInput.value = '';
            clearBtn.style.display = 'none';
            chatInput.focus();
          };
        }
      }, 100);

      console.log('[ChecklistAssistant] チャットUI作成完了');

      const input = document.getElementById('chatInput');
      if (input) {
        // エンターキーで送信
        input.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') {
            this.sendMessage();
          }
        });

        // モバイルでのキーボード表示検知
        const chatBox = document.getElementById('checklist-chat-assistant');
        if (chatBox && window.innerWidth <= 768) {
          input.addEventListener('focus', () => {
            setTimeout(() => {
              chatBox.classList.add('keyboard-visible');
            }, 300);
          });

          input.addEventListener('blur', () => {
            setTimeout(() => {
              chatBox.classList.remove('keyboard-visible');
            }, 300);
          });
        }

        // ウィンドウリサイズ時の処理
        window.addEventListener('resize', () => {
          const chatBox = document.getElementById('checklist-chat-assistant');
          if (!chatBox || chatBox.classList.contains('chat-hidden')) return;
          
          const isMobile = window.innerWidth <= 768;
          
          if (isMobile) {
            // モバイルに変更された場合
            chatBox.classList.add('keyboard-visible');
            chatBox.style.cssText = '';
          } else {
            // デスクトップに変更された場合は右下に移動
            chatBox.classList.remove('keyboard-visible');
            chatBox.style.cssText = 'position: fixed !important; bottom: 20px !important; right: 20px !important; top: auto !important; left: auto !important; width: 380px !important; height: auto !important; z-index: 999998 !important; border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important; background: white !important; display: flex !important; flex-direction: column !important;';
          }
        });
      }

      this.addMessage('bot', 'こんにちは👋 何について知りたいですか？');
    }

    extractChecklistData() {
      console.log('[ChecklistAssistant] extractChecklistData() 実行');
      
      // チェックボックスとモーダルリンクの両方を取得
      this.checklistData = [];
      
      // 1. チェックボックスベースのデータを取得
      const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
      console.log('[ChecklistAssistant] 見つかったチェックボックス数:', allCheckboxes.length);
      
      allCheckboxes.forEach((checkbox, index) => {
        let labelText = '';
        let modalTitle = '';
        let accordionTitle = '';
        
        if (checkbox.parentElement?.tagName === 'LABEL') {
          labelText = checkbox.parentElement.textContent.trim();
        }
        
        if (!labelText) {
          const label = checkbox.nextElementSibling;
          if (label && label.tagName === 'LABEL') {
            labelText = label.textContent.trim();
          }
        }
        
        if (!labelText) {
          const formCheck = checkbox.closest('.form-check');
          if (formCheck) {
            const label = formCheck.querySelector('label');
            if (label) {
              labelText = label.textContent.trim();
            }
          }
        }
        
        // アコーディオンのタイトルを取得
        const accordionBody = checkbox.closest('.accordion-body');
        if (accordionBody) {
          const accordionItem = accordionBody.closest('.accordion-item');
          if (accordionItem) {
            const accordionButton = accordionItem.querySelector('.accordion-button');
            if (accordionButton) {
              accordionTitle = accordionButton.textContent.trim().replace(/\s+/g, ' ');
            }
          }
        }
        
        // モーダルのタイトルと本文を取得
        let modalBody = '';
        const dataModalId = checkbox.getAttribute('data-modal-id');
        if (dataModalId) {
          const modal = document.getElementById(dataModalId);
          if (modal) {
            const titleElement = modal.querySelector('.modal-title');
            if (titleElement) {
              modalTitle = titleElement.textContent.trim();
            }
            // モーダルの本文も取得
            const bodyElement = modal.querySelector('.modal-body');
            if (bodyElement) {
              modalBody = bodyElement.textContent.trim().replace(/\s+/g, ' ');
            }
          }
        }
        
        if (labelText) {
          labelText = labelText.replace(/\s+/g, ' ').trim();
          if (modalTitle) {
            modalTitle = modalTitle.replace(/\s+/g, ' ').trim();
          }
          
          if (labelText && labelText.length > 1 && labelText.length < 200) {
            const formCheck = checkbox.closest('.form-check');
            const containerId = formCheck?.id || 'checklist-item-' + index;
            
            // チェックボックステキスト＋アコーディオンタイトル＋モーダルタイトル＋モーダル本文を検索対象に含める
            let fullText = labelText;
            if (accordionTitle) fullText += ' ' + accordionTitle;
            if (modalTitle) fullText += ' ' + modalTitle;
            if (modalBody) fullText += ' ' + modalBody;
            const fullSearchNormalized = this.normalizeText(fullText);
            
            this.checklistData.push({
              text: labelText,
              accordionTitle: accordionTitle,
              modalTitle: modalTitle,
              modalBody: modalBody,
              fullSearchText: fullText,
              fullSearchNormalized: fullSearchNormalized,
              checkboxId: checkbox.id,
              containerId: containerId,
              element: checkbox,
              type: 'checkbox'
            });
          }
        }
      });
      
      // 2. モーダルへのリンク（チェックボックスがない項目）も取得
      const allModalLinks = document.querySelectorAll('a[data-bs-toggle="modal"]');
      console.log('[ChecklistAssistant] 見つかったモーダルリンク数:', allModalLinks.length);
      
      allModalLinks.forEach((link, index) => {
        const linkText = link.textContent.trim();
        const modalId = link.getAttribute('data-bs-target')?.replace('#', '');
        
        if (!modalId) return;
        
        // アコーディオンのタイトルを取得
        let accordionTitle = '';
        const accordionBody = link.closest('.accordion-body');
        if (accordionBody) {
          const accordionItem = accordionBody.closest('.accordion-item');
          if (accordionItem) {
            const accordionButton = accordionItem.querySelector('.accordion-button');
            if (accordionButton) {
              accordionTitle = accordionButton.textContent.trim().replace(/\s+/g, ' ');
            }
          }
        }
        
        // モーダルの内容を取得
        let modalTitle = '';
        let modalBody = '';
        const modal = document.getElementById(modalId);
        if (modal) {
          const titleElement = modal.querySelector('.modal-title');
          if (titleElement) {
            modalTitle = titleElement.textContent.trim().replace(/\s+/g, ' ');
          }
          const bodyElement = modal.querySelector('.modal-body');
          if (bodyElement) {
            modalBody = bodyElement.textContent.trim().replace(/\s+/g, ' ');
          }
        }
        
        // フルテキストを作成
        let fullText = linkText;
        if (accordionTitle) fullText += ' ' + accordionTitle;
        if (modalTitle) fullText += ' ' + modalTitle;
        if (modalBody) fullText += ' ' + modalBody;
        const fullSearchNormalized = this.normalizeText(fullText);
        
        const formCheck = link.closest('.form-check');
        const containerId = formCheck?.id || 'modal-link-' + index;
        
        this.checklistData.push({
          text: linkText,
          accordionTitle: accordionTitle,
          modalTitle: modalTitle,
          modalBody: modalBody,
          fullSearchText: fullText,
          fullSearchNormalized: fullSearchNormalized,
          checkboxId: null,
          containerId: containerId,
          element: link,
          modalId: modalId,
          type: 'link'
        });
      });

      // 3. アコーディオンタイトルのみを持つ項目も検索対象に含める（タイトルで検索しやすくする）
      const accordionItems = document.querySelectorAll('.accordion-item');
      accordionItems.forEach((item, index) => {
        const button = item.querySelector('.accordion-button');
        const collapse = item.querySelector('.accordion-collapse');
        if (!button) return;

        const accordionTitle = button.textContent.trim().replace(/\s+/g, ' ');
        let bodyText = '';
        if (collapse) {
          const body = collapse.querySelector('.accordion-body');
          if (body) {
            bodyText = body.textContent.trim().replace(/\s+/g, ' ');
          }
        }

        const fullText = accordionTitle + (bodyText ? ' ' + bodyText : '');
        const fullSearchNormalized = this.normalizeText(fullText);
        const accordionId = collapse?.id || 'accordion-' + index;

        this.checklistData.push({
          text: accordionTitle,
          accordionTitle: accordionTitle,
          modalTitle: '',
          modalBody: bodyText,
          fullSearchText: fullText,
          fullSearchNormalized: fullSearchNormalized,
          checkboxId: null,
          containerId: accordionId,
          element: button,
          modalId: null,
          type: 'accordion'
        });
      });

      // 4. アコーディオン本文やモーダル本文内の h5 見出しも検索対象に含める
      const headings = document.querySelectorAll('.accordion-body h5, .modal-body h5');
      headings.forEach((heading, index) => {
        const headingText = heading.textContent.trim().replace(/\s+/g, ' ');
        if (!headingText) return;

        // 文脈取得: アコーディオンタイトル
        let accordionTitle = '';
        const accordionBody = heading.closest('.accordion-body');
        if (accordionBody) {
          const accordionItem = accordionBody.closest('.accordion-item');
          if (accordionItem) {
            const accordionButton = accordionItem.querySelector('.accordion-button');
            if (accordionButton) {
              accordionTitle = accordionButton.textContent.trim().replace(/\s+/g, ' ');
            }
          }
        }

        // 文脈取得: モーダルタイトル
        let modalTitle = '';
        let modalBody = '';
        let modalId = null;
        const modalEl = heading.closest('.modal');
        if (modalEl) {
          modalId = modalEl.id || null;
          const titleEl = modalEl.querySelector('.modal-title');
          if (titleEl) modalTitle = titleEl.textContent.trim().replace(/\s+/g, ' ');
          const bodyEl = modalEl.querySelector('.modal-body');
          if (bodyEl) modalBody = bodyEl.textContent.trim().replace(/\s+/g, ' ');
        } else {
          // 見出し直近の本文テキストも少し含める
          const parentBody = heading.closest('.accordion-body');
          if (parentBody) modalBody = parentBody.textContent.trim().replace(/\s+/g, ' ');
        }

        const fullText = [headingText, accordionTitle, modalTitle, modalBody].filter(Boolean).join(' ');
        const fullSearchNormalized = this.normalizeText(fullText);
        const containerId = heading.id || `heading-${index}`;
        if (!heading.id) heading.id = containerId;

        this.checklistData.push({
          text: headingText,
          accordionTitle: accordionTitle,
          modalTitle: modalTitle,
          modalBody: modalBody,
          fullSearchText: fullText,
          fullSearchNormalized: fullSearchNormalized,
          checkboxId: null,
          containerId: containerId,
          element: heading,
          modalId: modalId,
          type: 'heading'
        });
      });
      
      console.log('[ChecklistAssistant] 抽出されたデータ総数:', this.checklistData.length);
      if (this.checklistData.length > 0) {
        console.log('[ChecklistAssistant] サンプルデータ[0]:', this.checklistData[0]);
        console.log('[ChecklistAssistant] サンプルデータ[1]:', this.checklistData[1]);
      }
    }

    sendMessage() {
      const input = document.getElementById('chatInput');
      if (!input) return;
      
      const message = input.value.trim();
      if (!message) return;
      
      this.addMessage('user', message);
      input.value = '';
      
      // 複数キーワード検索：スペース区切りで AND 検索（曖昧マッチ用に正規化）
      const keywords = message.split(/\s+/).map(k => this.normalizeText(k)).filter(k => k.length > 0);
      
      // 完全マッチと部分マッチを分ける
      const exactMatches = [];
      const partialMatches = [];
      
      this.checklistData.forEach(item => {
        const searchText = item.fullSearchNormalized || this.normalizeText(item.fullSearchText || '');
        const compactSearch = searchText.replace(/\s+/g, '');

        let hitCount = 0;
        keywords.forEach(keyword => {
          const compactKeyword = keyword.replace(/\s+/g, '');
          if (searchText.includes(keyword) || compactSearch.includes(compactKeyword)) {
            hitCount++;
          }
        });

        const ratio = keywords.length > 0 ? hitCount / keywords.length : 0;

        if (ratio >= 1) {
          exactMatches.push({ ...item, matchCount: hitCount });
        } else if (ratio >= 0.34 && hitCount > 0) {
          // 1/3以上ヒットしたら関連キーワードとして扱う（曖昧検索）
          partialMatches.push({ ...item, matchCount: hitCount });
        }
      });
      
      // マッチ数でソート（高いものが優先）
      exactMatches.sort((a, b) => b.matchCount - a.matchCount);
      partialMatches.sort((a, b) => b.matchCount - a.matchCount);
      
      if (exactMatches.length > 0) {
        // 見つかった項目をリンク化して表示
        let htmlResponse = '<strong>✓ マッチした項目:</strong><br>';
        
        // 完全マッチ項目を表示（最大50件）
        exactMatches.slice(0, 50).forEach((r, index) => {
          let displayText = this.escapeHtml(r.text);
          if (r.modalTitle && r.modalTitle !== r.text) {
            displayText += ` <small style="color: #999;">(${this.escapeHtml(r.modalTitle)})</small>`;
          }
          htmlResponse += `<div class="checklist-item-link" data-checkbox-id="${r.checkboxId || ''}" data-container-id="${r.containerId || ''}" data-modal-id="${r.modalId || ''}" data-type="${r.type || 'checkbox'}" style="cursor: pointer; margin: 8px 0; padding: 8px; background: #f0f0f0; border-radius: 4px; border-left: 3px solid #667eea;">
            <span style="color: #333;">• ${displayText}</span>
          </div>`;
        });
        
        // 関連キーワード（部分マッチ）を表示
        if (partialMatches.length > 0) {
          htmlResponse += '<br><strong style="color: #666;">✦ 関連キーワード:</strong><br>';
          partialMatches.slice(0, 30).forEach((r, index) => {
            let displayText = this.escapeHtml(r.text);
            if (r.modalTitle && r.modalTitle !== r.text) {
              displayText += ` <small style="color: #999;">(${this.escapeHtml(r.modalTitle)})</small>`;
            }
            htmlResponse += `<div class="checklist-item-link" data-checkbox-id="${r.checkboxId || ''}" data-container-id="${r.containerId || ''}" data-modal-id="${r.modalId || ''}" data-type="${r.type || 'checkbox'}" style="cursor: pointer; margin: 6px 0; padding: 6px 8px; background: #f5f5f5; border-radius: 4px; border-left: 3px solid #ccc; opacity: 0.85;">
              <span style="color: #666; font-size: 12px;">○ ${displayText}</span>
            </div>`;
          });
        }
        
        if (exactMatches.length > 50) {
          htmlResponse += `<small style="color: #999;">他 ${exactMatches.length - 50} 件</small>`;
        }
        if (partialMatches.length > 30) {
          htmlResponse += `<small style="color: #999;"> / 関連 ${partialMatches.length - 30} 件</small>`;
        }
        
        this.addMessage('bot', htmlResponse, true);
      } else {
        this.addMessage('bot', 'その検索キーワードは見つかりませんでした。別のキーワードを試してください。');
      }
    }

    normalizeText(text) {
      if (!text) return '';

      // 全角・半角の揺れを吸収し、カタカナをひらがなに変換して曖昧検索を強化
      let normalized = text.normalize('NFKC').toLowerCase();
      normalized = normalized.replace(/[ァ-ヶ]/g, ch => String.fromCharCode(ch.charCodeAt(0) - 0x60));
      normalized = normalized.replace(/[‐‑‒–—―ー]/g, '-');
      normalized = normalized.replace(/[、，｡。]/g, ' ');
      return normalized.replace(/\s+/g, ' ').trim();
    }

    resetSearchState() {
      const input = document.getElementById('chatInput');
      const clearBtn = document.getElementById('chatClearBtn');
      const messagesDiv = document.getElementById('chatMessages');

      if (input) input.value = '';
      if (clearBtn) clearBtn.style.display = 'none';

      if (messagesDiv) {
        messagesDiv.innerHTML = '';
        this.addMessage('bot', 'こんにちは👋 何について知りたいですか？');
      }
    }

    escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    addMessage(role, text, isHtml = false) {
      const messagesDiv = document.getElementById('chatMessages');
      if (!messagesDiv) return;
      
      const messageEl = document.createElement('div');
      messageEl.className = 'chat-message ' + role;
      
      const bubbleEl = document.createElement('div');
      bubbleEl.className = 'chat-bubble';
      
      if (isHtml) {
        bubbleEl.innerHTML = text;
      } else {
        bubbleEl.textContent = text;
      }
      
      messageEl.appendChild(bubbleEl);
      messagesDiv.appendChild(messageEl);
      
      // リンククリックイベントを設定
      if (isHtml) {
        const links = bubbleEl.querySelectorAll('.checklist-item-link');
        links.forEach(link => {
          link.addEventListener('click', (e) => {
            const checkboxId = link.getAttribute('data-checkbox-id');
            const containerId = link.getAttribute('data-container-id');
            const modalId = link.getAttribute('data-modal-id');
            const type = link.getAttribute('data-type');
            this.navigateToCheckbox(checkboxId, containerId, modalId, type);
          });
        });
      }
      
      messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    navigateToCheckbox(checkboxId, containerId, modalId, type) {
      console.log('[ChecklistAssistant] navigateToCheckbox実行:', { checkboxId, containerId, modalId, type });
      
      // チャットを閉じる（遅延させてモーダル状態を確実にクリア）
      setTimeout(() => {
        const chat = document.getElementById('checklist-chat-assistant');
        const toggleBtn = document.getElementById('chat-toggle-btn');
        if (chat) {
          chat.classList.add('chat-hidden');
          chat.classList.remove('keyboard-visible');
          chat.style.cssText = '';
        }
        if (toggleBtn) toggleBtn.classList.remove('hidden');
        if (typeof this.resetSearchState === 'function') {
          this.resetSearchState();
        }
      }, 100);
      
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

