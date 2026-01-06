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
          <button class="chat-close-btn" id="chatCloseBtn">
            <i class="bi bi-chevron-down"></i>
          </button>
        </div>
        <div class="chat-messages" id="chatMessages"></div>
        <div class="chat-input-area">
          <input 
            type="text" 
            class="chat-input" 
            id="chatInput" 
            placeholder="キーワード検索..." 
            autocomplete="off"
          >
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

      // accordion の後に挿入
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
        const btn = document.getElementById('chat-toggle-btn');
        if (btn) {
          btn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const chat = document.getElementById('checklist-chat-assistant');
            const toggleBtn = document.getElementById('chat-toggle-btn');
            if (chat) chat.classList.toggle('chat-hidden');
            if (toggleBtn) toggleBtn.classList.toggle('hidden');
          };
        }

        const closeBtn = document.getElementById('chatCloseBtn');
        if (closeBtn) {
          closeBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const chat = document.getElementById('checklist-chat-assistant');
            const toggleBtn = document.getElementById('chat-toggle-btn');
            if (chat) chat.classList.add('chat-hidden');
            if (toggleBtn) toggleBtn.classList.remove('hidden');
          };
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
      }, 100);

      console.log('[ChecklistAssistant] チャットUI作成完了');

      const input = document.getElementById('chatInput');
      if (input) {
        input.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') {
            this.sendMessage();
          }
        });
      }

      this.addMessage('bot', 'こんにちは👋 何について知りたいですか？');
    }

    extractChecklistData() {
      console.log('[ChecklistAssistant] extractChecklistData() 実行');
      
      const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
      console.log('[ChecklistAssistant] 見つかったチェックボックス数:', allCheckboxes.length);
      
      allCheckboxes.forEach((checkbox, index) => {
        let labelText = '';
        
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
        
        if (labelText) {
          labelText = labelText.replace(/\s+/g, ' ').trim();
          
          if (labelText && labelText.length > 1 && labelText.length < 200) {
            const formCheck = checkbox.closest('.form-check');
            const containerId = formCheck?.id || 'checklist-item-' + index;
            
            this.checklistData.push({
              text: labelText,
              checkboxId: checkbox.id,
              containerId: containerId,
              element: checkbox
            });
          }
        }
      });
      
      console.log('[ChecklistAssistant] 抽出されたデータ総数:', this.checklistData.length);
    }

    sendMessage() {
      const input = document.getElementById('chatInput');
      if (!input) return;
      
      const message = input.value.trim();
      if (!message) return;
      
      this.addMessage('user', message);
      input.value = '';
      
      const results = this.checklistData.filter(item =>
        item.text.toLowerCase().includes(message.toLowerCase())
      );
      
      if (results.length > 0) {
        let response = '見つかった項目:\n';
        results.slice(0, 5).forEach(r => {
          response += '• ' + r.text + '\n';
        });
        this.addMessage('bot', response);
      } else {
        this.addMessage('bot', 'その検索キーワードは見つかりませんでした。別のキーワードを試してください。');
      }
    }

    addMessage(role, text) {
      const messagesDiv = document.getElementById('chatMessages');
      if (!messagesDiv) return;
      
      const messageEl = document.createElement('div');
      messageEl.className = 'chat-message ' + role;
      messageEl.textContent = text;
      messagesDiv.appendChild(messageEl);
      messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
  }

  // グローバルインスタンス
  window.checklistAssistant = new ChecklistChatAssistant();
  window.ChecklistChatAssistant = ChecklistChatAssistant;
  
  console.log('[ChecklistAssistant] 初期化完了');
}

