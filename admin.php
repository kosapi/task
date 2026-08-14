<?php
session_start();

// キャッシュ保持による旧JSエラー誤動作を回避するレスポンスヘッダー
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 管理画面のパスワード設定（※お好みのパスワードに変更可能です）
define('ADMIN_PASSWORD', 'admin123');

// ログアウト処理
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ログイン判定
$error_msg = '';
if (isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error_msg = 'パスワードが違います。';
    }
}

$is_logged_in = !empty($_SESSION['admin_logged_in']);

// checklist.json の初期データをサーバー側で直接読み込み（通信エラー・パスずれゼロ化）
$json_path = __DIR__ . '/data/checklist.json';
$initial_json = file_exists($json_path) ? file_get_contents($json_path) : '[]';
if (empty($initial_json) || json_decode($initial_json) === null) {
    $initial_json = '[]';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <base href="/task/">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ワークチェックリスト 管理画面</title>

  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/main_v55.css?v=20260813_FINAL_SUPER_CACHEBUST_1786630246">

  <style>
    /* アプリ画面のボタン・バッジ風装飾用クラス */
    .app-badge {
      display: inline-block;
      padding: 3px 10px;
      font-size: 0.9em;
      font-weight: bold;
      line-height: 1.4;
      border-radius: 7px;
      margin: 3px 4px;
      vertical-align: middle;
      box-shadow: 0 1px 2px rgba(0,0,0,0.12);
      white-space: nowrap;
    }
    .app-badge-blue {
      background-color: #5897E6 !important;
      color: #ffffff !important;
      border: 1px solid #4a86ce;
    }
    .app-badge-yellow {
      background-color: #EBD671 !important;
      color: #111111 !important;
      border: 1.5px solid #111111;
    }
    .app-badge-black {
      background-color: #1a1a1a !important;
      color: #EE7A55 !important;
      border: 1px solid #333333;
    }
    .app-badge-green {
      background-color: #4CAF50 !important;
      color: #ffffff !important;
      border: 1px solid #3d8b40;
    }
    .app-badge-red {
      background-color: #E53935 !important;
      color: #ffffff !important;
      border: 1px solid #c62828;
    }

    body {
      background-color: #f4f6f9;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .header-bar {
      background-color: #114400 !important;
      color: #ffffff !important;
      padding: 0.75rem 1.25rem !important;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2) !important;
      z-index: 1040 !important;
    }
    .header-bar h1 {
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 1.25rem;
      color: #ffffff !important;
      margin: 0;
    }
    .header-bar .btn-light {
      background-color: #ffffff !important;
      color: #212529 !important;
      border: 1px solid #cccccc !important;
    }
    .header-bar .btn-danger {
      background-color: #dc3545 !important;
      color: #ffffff !important;
      border: none !important;
    }
    .header-bar .btn-warning {
      background-color: #ffc107 !important;
      color: #212529 !important;
      border: none !important;
    }
    .category-nav {
      background-color: #fff;
      border-bottom: 1px solid #dee2e6;
      overflow-x: auto;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
    }
    .nav-pills {
      flex-wrap: nowrap !important;
    }
    .nav-pills .nav-link {
      color: #495057;
      font-weight: bold;
      border-radius: 20px;
      padding: 6px 14px;
      font-size: 0.9rem;
      margin-right: 5px;
      white-space: nowrap;
    }
    .nav-pills .nav-link.active {
      background-color: #1b3a2f;
      color: #fff;
    }
    .item-card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      border-left: 4px solid #1b3a2f;
      margin-bottom: 1rem;
    }
    .item-card:hover {
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .preview-modal-body {
      background-color: #fafafa;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      padding: 10px;
      max-height: 260px;
      overflow-y: auto;
      font-size: 0.9rem;
    }
    .preview-modal-body img {
      max-width: 100%;
      height: auto;
      border-radius: 4px;
      margin: 6px 0;
      display: block;
    }
    .badge-count {
      background-color: #6c757d;
      font-size: 0.8rem;
    }

    /* ネイティブビジュアルエディター用レスポンシブスタイル */
    .editor-toolbar {
      background: #f8f9fa;
      border: 1px solid #ced4da;
      border-bottom: none;
      border-top-left-radius: 6px;
      border-top-right-radius: 6px;
      padding: 8px;
      overflow-x: auto;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
      position: sticky;
      top: -1rem;
      z-index: 1020;
      box-shadow: 0 4px 6px rgba(0,0,0,0.08);
    }
    .editor-toolbar .btn {
      padding: 6px 10px;
      font-size: 0.85rem;
      touch-action: manipulation;
    }
    #editor-body {
      min-height: 360px;
      max-height: 550px;
      overflow-y: auto;
      border: 1px solid #ced4da;
      border-bottom-left-radius: 6px;
      border-bottom-right-radius: 6px;
      padding: 16px 20px;
      background: #fff;
      font-size: 1.05rem;
      line-height: 1.75;
    }
    #editor-body:focus {
      outline: 2px solid #1b3a2f;
    }
    /* エディター本文要素の上下余白（本番表示の見た目に合わせる） */
    #editor-body p {
      margin-top: 0.4rem;
      margin-bottom: 0.8rem;
      line-height: 1.75;
    }
    #editor-body div {
      margin-bottom: 0.8rem;
    }
    #editor-body ul, #editor-body ol {
      margin-top: 0.4rem;
      margin-bottom: 0.8rem;
      padding-left: 1.6rem;
    }
    #editor-body li {
      margin-bottom: 0.5rem;
      line-height: 1.7;
    }
    #editor-body h5 {
      margin-top: 1.2rem;
      margin-bottom: 0.6rem;
      font-weight: bold;
    }
    #editor-body .alert {
      margin-top: 0.8rem;
      margin-bottom: 0.8rem;
    }
    #editorModal .modal-dialog {
      max-width: 800px;
      height: 85vh;
    }
    #editorModal .modal-content {
      height: 100%;
    }

    /* スマホ画面向けの最適化 */
    @media (max-width: 576px) {
      .header-bar {
        padding: 0.5rem 0.75rem;
      }
      .header-bar h1 {
        font-size: 1.05rem;
      }
      .container-fluid {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
      }
      .item-card {
        padding: 0.75rem !important;
      }
      .btn-group-mobile {
        width: 100%;
        display: flex;
        justify-content: space-between;
        gap: 4px;
      }
      .btn-group-mobile .btn {
        flex: 1;
        padding: 6px 4px;
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body class="admin-body">

<?php if (!$is_logged_in): ?>
  <!-- ログインフォーム画面 -->
  <div class="container py-5" style="max-width: 420px;">
    <div class="card shadow border-0 mt-5">
      <div class="card-header bg-dark text-white text-center py-3">
        <h4 class="m-0 fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>管理者ログイン</h4>
      </div>
      <div class="card-body p-4">
        <?php if (!empty($error_msg)): ?>
          <div class="alert alert-danger mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_msg) ?>
          </div>
        <?php endif; ?>
        <form method="POST" action="admin.php">
          <div class="mb-3">
            <label for="password" class="form-label fw-bold">パスワードを入力してください</label>
            <input type="password" class="form-control form-control-lg" id="password" name="password" required placeholder="パスワード" autofocus>
          </div>
          <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
            <i class="bi bi-box-arrow-in-right me-2"></i>ログイン
          </button>
        </form>
      </div>
    </div>
  </div>
<?php else: ?>

  <!-- ヘッダー -->
  <div class="header-bar d-flex justify-content-between align-items-center sticky-top">
    <div class="d-flex align-items-center gap-3">
      <h1><i class="bi bi-pencil-square"></i> ワークチェックリスト 管理画面</h1>
      <span class="badge bg-success" id="save-status">保存済み</span>
    </div>
    <div class="d-flex gap-2">
      <a href="index.html" target="_blank" class="btn btn-light btn-sm text-dark fw-bold shadow-sm px-3">
        <i class="bi bi-box-arrow-up-right me-1"></i> 本番画面を表示
      </a>
      <button class="btn btn-warning btn-sm text-dark fw-bold px-3 shadow-sm" id="btn-save-all">
        <i class="bi bi-cloud-arrow-up-fill me-1"></i> 全体を保存
      </button>
      <a href="admin.php?action=logout" class="btn btn-danger btn-sm text-white fw-bold shadow-sm px-3">
        <i class="bi bi-box-arrow-right me-1"></i> ログアウト
      </a>
    </div>
  </div>

  <div class="container-fluid py-4 px-4">
    <!-- カテゴリタブナビゲーション -->
    <div class="category-nav p-3 mb-4 rounded shadow-sm">
      <ul class="nav nav-pills flex-wrap" id="category-tabs">
        <!-- JSで挿入 -->
      </ul>
    </div>

    <!-- ツールバー -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h4 class="m-0 fw-bold" id="current-category-title">項目一覧</h4>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-success fw-bold shadow-sm" id="btn-edit-slogans" data-bs-toggle="modal" data-bs-target="#slogansModal">
          <i class="bi bi-megaphone-fill me-1"></i> 本日の安全標語を編集
        </button>
        <input type="text" id="search-input" class="form-control" placeholder="🔍 項目名やキーワードで検索..." style="width: 240px;">
        <button class="btn btn-primary fw-bold shadow-sm" id="btn-add-item">
          <i class="bi bi-plus-lg"></i> このカテゴリに項目を追加
        </button>
      </div>
    </div>

    <!-- 項目一覧リスト -->
    <div id="items-list">
      <!-- JSで挿入 -->
    </div>
  </div>

  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script id="checklist-data-json" type="application/json"><?= str_replace('</script>', '<\/script>', $initial_json) ?></script>

  <script>
    let checklistData = [];
    try {
      const jsonElem = document.getElementById('checklist-data-json');
      if (jsonElem) {
        checklistData = JSON.parse(jsonElem.textContent);
      }
    } catch (e) {
      console.error('JSON parse error:', e);
    }
    let activeCatId = 0;

    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('search-input');
      if (searchInput) {
        searchInput.addEventListener('input', function() {
          const query = this.value.toLowerCase().trim();
          renderItemsList(query);
        });
      }

      const btnSaveAll = document.getElementById('btn-save-all');
      if (btnSaveAll) btnSaveAll.addEventListener('click', saveAllData);

      const btnAddItem = document.getElementById('btn-add-item');
      if (btnAddItem) btnAddItem.addEventListener('click', openAddItemModal);

      document.addEventListener('keyup', saveSelection);
      document.addEventListener('mouseup', saveSelection);
      document.addEventListener('touchend', saveSelection);
      document.addEventListener('selectionchange', function() {
        if (document.activeElement && document.activeElement.id === 'editor-body') {
          saveSelection();
        }
      });

      loadChecklist();
    });

    // HTMLエスケープヘルパー
    window.escapeHtml = function(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    };

    // エディター領域の選択範囲（Range）管理
    let savedRange = null;

    window.saveSelection = function() {
      const sel = window.getSelection();
      if (sel && sel.rangeCount > 0) {
        const range = sel.getRangeAt(0);
        const editor = document.getElementById('editor-body');
        if (editor && editor.contains(range.commonAncestorContainer)) {
          savedRange = range.cloneRange();
        }
      }
    };

    window.restoreSelection = function() {
      const editor = document.getElementById('editor-body');
      if (!editor) return;
      editor.focus();
      if (savedRange) {
        const sel = window.getSelection();
        if (sel) {
          sel.removeAllRanges();
          sel.addRange(savedRange);
        }
      }
    };

    window.getSelectedTextFromEditor = function() {
      const sel = window.getSelection();
      let text = (sel ? sel.toString().trim() : '');
      if (!text && savedRange) {
        text = savedRange.toString().trim();
      }
      return text;
    };

    // カーソル位置 / 選択テキスト部分へ確実にHTMLを挿入・置換
    window.insertHTMLAtCursor = function(html) {
      const editor = document.getElementById('editor-body');
      if (!editor) return;

      editor.focus();
      restoreSelection();

      const sel = window.getSelection();
      let inserted = false;

      let range = null;
      if (sel && sel.rangeCount > 0) {
        const currentRange = sel.getRangeAt(0);
        if (editor.contains(currentRange.commonAncestorContainer)) {
          range = currentRange;
        }
      }
      if (!range && savedRange && editor.contains(savedRange.commonAncestorContainer)) {
        range = savedRange.cloneRange();
      }

      if (range) {
        try {
          range.deleteContents();
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = html;
          const frag = document.createDocumentFragment();
          let lastNode = null;
          while (tempDiv.firstChild) {
            lastNode = tempDiv.firstChild;
            frag.appendChild(lastNode);
          }
          range.insertNode(frag);

          if (lastNode && sel) {
            range.setStartAfter(lastNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
          }
          inserted = true;
        } catch (e) {
          console.error('Range API insertion error:', e);
        }
      }

      if (!inserted) {
        try {
          inserted = document.execCommand('insertHTML', false, html);
        } catch (e) {}
      }

      if (!inserted) {
        editor.innerHTML += html;
      }

      saveSelection();
    };

    // ツールバーコマンド実行（太字・リスト等）
    window.execCmd = function(command, value = null) {
      if (command === 'insertHTML') {
        insertHTMLAtCursor(value);
        return;
      }
      restoreSelection();
      document.execCommand(command, false, value);
      saveSelection();
    };

    // リンクの挿入・修正
    window.insertLink = function() {
      restoreSelection();
      const selectedText = getSelectedTextFromEditor();

      const url = prompt('リンク先のURLまたは電話番号（tel:03-...）を入力してください:', 'https://');
      if (!url) return;

      if (selectedText) {
        execCmd('createLink', url);
      } else {
        const linkText = prompt('リンクとして表示する文字を入力してください:', 'リンク');
        if (!linkText) return;
        const linkHtml = `<a href="${escapeHtml(url)}" target="_blank" rel="noopener">${escapeHtml(linkText)}</a>`;
        insertHTMLAtCursor(linkHtml);
      }
    };

    // 表（テーブル）の挿入
    window.insertTable = function() {
      const rows = prompt('行数を入力してください（例: 2または3）', '2');
      if (!rows || isNaN(rows)) return;
      const cols = prompt('列数を入力してください（例: 2または3）', '2');
      if (!cols || isNaN(cols)) return;

      let html = '<table class="table table-bordered table-hover my-2"><tbody>';
      for (let r = 0; r < parseInt(rows); r++) {
        html += '<tr>';
        for (let c = 0; c < parseInt(cols); c++) {
          if (r === 0) {
            html += `<th class="table-primary">見出し ${c + 1}</th>`;
          } else {
            html += `<td>内容 ${r}-${c + 1}</td>`;
          }
        }
        html += '</tr>';
      }
      html += '</tbody></table><p><br></p>';

      insertHTMLAtCursor(html);
    };

    // 超強力バナー枠解除（選択範囲内・カーソル位置のすべての .alert を完全に解体・解除）
    window.removeAlertBox = function() {
      restoreSelection();
      const editor = document.getElementById('editor-body');
      if (!editor) return false;

      const sel = window.getSelection();
      let targetAlerts = [];

      if (sel && sel.rangeCount > 0) {
        const range = sel.getRangeAt(0);

        // 1. 開始点・終了点の親要素から .alert を検出
        let startNode = range.startContainer.nodeType === 3 ? range.startContainer.parentNode : range.startContainer;
        let endNode = range.endContainer.nodeType === 3 ? range.endContainer.parentNode : range.endContainer;

        let alert1 = startNode.closest ? startNode.closest('.alert') : null;
        let alert2 = endNode.closest ? endNode.closest('.alert') : null;
        if (alert1 && editor.contains(alert1)) targetAlerts.push(alert1);
        if (alert2 && editor.contains(alert2) && !targetAlerts.includes(alert2)) targetAlerts.push(alert2);

        // 2. 選択範囲に交差・包含される .alert を全走査して検出
        const allAlerts = editor.querySelectorAll('.alert');
        allAlerts.forEach(alertElem => {
          if (sel.containsNode(alertElem, true) && !targetAlerts.includes(alertElem)) {
            targetAlerts.push(alertElem);
          }
        });
      }

      if (targetAlerts.length > 0) {
        targetAlerts.forEach(alertNode => {
          const parent = alertNode.parentNode;
          while (alertNode.firstChild) {
            parent.insertBefore(alertNode.firstChild, alertNode);
          }
          alertNode.remove();
        });
        saveSelection();
        return true;
      }
      return false;
    };

    // 標準テキストに戻す（バナー枠・見出しをまとめて解除）
    window.resetToNormalText = function() {
      const alertRemoved = removeAlertBox();
      if (!alertRemoved) {
        execCmd('formatBlock', 'p');
      }
    };

    // インフォボックス挿入（選択範囲をバナー枠に変換 / すでにバナー枠がある場合は一発解除）
    window.insertAlertBox = function(type) {
      // もし選択・カーソル位置にバナーがある場合は解除を実行
      const removed = removeAlertBox();
      if (removed) return;

      restoreSelection();
      const sel = window.getSelection();
      let contentHtml = '';

      if (sel && sel.rangeCount > 0 && !sel.isCollapsed) {
        const range = sel.getRangeAt(0);
        const div = document.createElement('div');
        div.appendChild(range.cloneContents());
        contentHtml = div.innerHTML.trim();
      }

      if (!contentHtml) {
        if (type === 'danger') contentHtml = 'ここに警告・注意事項を入力してください';
        else if (type === 'success') contentHtml = 'ここに完了・確認事項を入力してください';
        else if (type === 'info') contentHtml = 'ここに補足・お知らせ事項を入力してください';
        else contentHtml = 'ここに説明事項を入力してください';
      }

      const alertHtml = `<div class="alert alert-${type} my-2" role="alert">${contentHtml}</div><p><br></p>`;
      insertHTMLAtCursor(alertHtml);
    };

    // アプリボタン風バッジの挿入
    window.insertBadge = function(badgeClass, defaultText) {
      const selectedText = getSelectedTextFromEditor();
      const text = selectedText || defaultText;
      const badgeHtml = `<span class="app-badge ${badgeClass}">${escapeHtml(text)}</span>&nbsp;`;
      insertHTMLAtCursor(badgeHtml);
    };

    // 選択テキストのバッジ化
    window.makeSelectionBadge = function(badgeClass) {
      let selectedText = getSelectedTextFromEditor();
      if (!selectedText) {
        selectedText = prompt('バッジ装飾する文字を入力してください:', '了解');
        if (!selectedText) return;
      }
      const badgeHtml = `<span class="app-badge ${badgeClass}">${escapeHtml(selectedText)}</span>&nbsp;`;
      insertHTMLAtCursor(badgeHtml);
    };

    // カスタムバッジ作成
    window.insertCustomBadge = function() {
      const selectedText = getSelectedTextFromEditor();
      const initialText = selectedText || '了解';
      const text = prompt('バッジにする文字を入力してください:', initialText);
      if (!text) return;
      const color = prompt('色を選択してください:\n1: 青（了解・ナビ案内など）\n2: 黄・黒枠（迎車など）\n3: 黒・オレンジ文字（迎車警告など）\n4: 緑\n5: 赤', '1');
      let badgeClass = 'app-badge-blue';
      if (color === '2') badgeClass = 'app-badge-yellow';
      if (color === '3') badgeClass = 'app-badge-black';
      if (color === '4') badgeClass = 'app-badge-green';
      if (color === '5') badgeClass = 'app-badge-red';
      
      const badgeHtml = `<span class="app-badge ${badgeClass}">${escapeHtml(text)}</span>&nbsp;`;
      insertHTMLAtCursor(badgeHtml);
    };

    // 写真追加ボタンのトリガー
    window.triggerImgUpload = function() {
      document.getElementById('native-img-upload').click();
    };

    // 写真選択時の非同期アップロードとエディター挿入
    window.handleNativeImgUpload = function(input) {
      if (!input.files || !input.files[0]) return;
      const file = input.files[0];
      const formData = new FormData();
      formData.append('image', file);

      fetch('api/upload_image.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          execCmd('insertImage', data.url);
        } else {
          alert('画像のアップロードに失敗しました: ' + data.message);
        }
        input.value = '';
      })
      .catch(err => {
        alert('通信エラー: ' + err);
        input.value = '';
      });
    };

    // データの読み込み（即時表示＋バックグラウンド同期）
    function loadChecklist() {
      // 埋め込まれた初期データが既にあれば即座に描画（ロード待ちゼロ）
      if (Array.isArray(checklistData) && checklistData.length > 0) {
        if (activeCatId === 0 && checklistData[0].categoryId !== undefined) {
          activeCatId = checklistData[0].categoryId;
        }
        renderCategoryTabs();
        renderItemsList();
      }

      // バックグラウンドで最新データをチェック
      fetch('data/checklist.json?v=' + new Date().getTime())
        .then(res => res.ok ? res.json() : null)
        .then(data => {
          if (Array.isArray(data) && data.length > 0) {
            checklistData = data;
            if (activeCatId === 0 && data[0].categoryId !== undefined) {
              activeCatId = data[0].categoryId;
            }
            renderCategoryTabs();
            renderItemsList();
          }
        })
        .catch(err => {
          console.warn('[Admin] 非同期通信スキップ（表示継続）:', err);
        });
    }

    // 読み込み直後に即座に描画
    try {
      loadChecklist();
    } catch(e) {
      console.error('Immediate load error:', e);
    }

    // カテゴリタブの描画
    function renderCategoryTabs() {
      const nav = document.getElementById('category-tabs');
      nav.innerHTML = '';

      checklistData.forEach((cat) => {
        const li = document.createElement('li');
        li.className = 'nav-item';
        
        const a = document.createElement('a');
        a.className = 'nav-link ' + (cat.categoryId === activeCatId ? 'active' : '');
        a.href = '#';
        a.innerHTML = `${escapeHtml(cat.categoryTitle)} <span class="badge badge-count ms-1">${cat.items ? cat.items.length : 0}</span>`;
        a.addEventListener('click', (e) => {
          e.preventDefault();
          activeCatId = cat.categoryId;
          renderCategoryTabs();
          renderItemsList();
        });
        li.appendChild(a);
        nav.appendChild(li);
      });

      const currentCat = checklistData.find(c => c.categoryId === activeCatId);
      if (currentCat) {
        document.getElementById('current-category-title').innerText = currentCat.categoryTitle + ' の項目一覧';
      }
    }

    // 項目一覧の描画
    function renderItemsList(searchQuery = '') {
      const listContainer = document.getElementById('items-list');
      listContainer.innerHTML = '';

      let itemsToRender = [];
      
      if (searchQuery) {
        checklistData.forEach(cat => {
          if (cat.items) {
            cat.items.forEach(item => {
              const fullText = (item.labelHtml + ' ' + item.modalContentHtml).toLowerCase();
              if (fullText.includes(searchQuery)) {
                itemsToRender.push({ item: item, categoryTitle: cat.categoryTitle, catId: cat.categoryId });
              }
            });
          }
        });
      } else {
        const currentCat = checklistData.find(c => c.categoryId === activeCatId);
        if (currentCat && currentCat.items) {
          itemsToRender = currentCat.items.map(item => ({ item: item, categoryTitle: currentCat.categoryTitle, catId: activeCatId }));
        }
      }

      if (itemsToRender.length === 0) {
        listContainer.innerHTML = searchQuery 
          ? `<div class="alert alert-warning py-4 text-center fs-5">「${escapeHtml(searchQuery)}」に一致する項目は見つかりませんでした。</div>`
          : '<div class="alert alert-info py-4 text-center fs-5">このカテゴリにはまだ項目がありません。「項目を追加」ボタンから追加できます。</div>';
        return;
      }

      itemsToRender.forEach(({ item, categoryTitle, catId }, index) => {
        const card = document.createElement('div');
        card.className = 'item-card p-3 d-flex justify-content-between align-items-center flex-wrap gap-3';
        
        const infoDiv = document.createElement('div');
        infoDiv.style.flex = '1';
        infoDiv.style.minWidth = '300px';
        infoDiv.innerHTML = `
          <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <span class="badge bg-secondary">${searchQuery ? escapeHtml(categoryTitle) : '#' + (index + 1)}</span>
            ${item.parentLabel ? `<span class="badge bg-info text-dark"><i class="bi bi-link-45deg"></i> 親項目: ${escapeHtml(item.parentLabel)}</span>` : ''}
            <h5 class="m-0 fw-bold">${item.labelHtml}</h5>
          </div>
          <div class="preview-modal-body mt-2">
            ${item.modalContentHtml || '<em class="text-muted">（内容なし）</em>'}
          </div>
        `;

        const btnGroup = document.createElement('div');
        btnGroup.className = 'd-flex gap-2 align-items-center flex-wrap btn-group-mobile';
        
        if (!searchQuery) {
          const btnUp = document.createElement('button');
          btnUp.className = 'btn btn-outline-secondary btn-sm';
          btnUp.innerHTML = '<i class="bi bi-arrow-up"></i> 上へ';
          btnUp.disabled = index === 0;
          btnUp.onclick = () => moveItem(index, -1);

          const btnDown = document.createElement('button');
          btnDown.className = 'btn btn-outline-secondary btn-sm';
          btnDown.innerHTML = '<i class="bi bi-arrow-down"></i> 下へ';
          btnDown.disabled = index === itemsToRender.length - 1;
          btnDown.onclick = () => moveItem(index, 1);

          const btnEdit = document.createElement('button');
          btnEdit.className = 'btn btn-primary btn-sm fw-bold px-3';
          btnEdit.innerHTML = '<i class="bi bi-pencil-square"></i> 編集';
          btnEdit.onclick = () => editItem(index);

          const btnDel = document.createElement('button');
          btnDel.className = 'btn btn-outline-danger btn-sm';
          btnDel.innerHTML = '<i class="bi bi-trash"></i> 削除';
          btnDel.onclick = () => deleteItem(index);

          btnGroup.appendChild(btnUp);
          btnGroup.appendChild(btnDown);
          btnGroup.appendChild(btnEdit);
          btnGroup.appendChild(btnDel);
        } else {
          const btnEdit = document.createElement('button');
          btnEdit.className = 'btn btn-primary btn-sm fw-bold px-3';
          btnEdit.innerHTML = '<i class="bi bi-pencil-square"></i> 編集する';
          btnEdit.onclick = () => editItemByObject(catId, item.id);
          btnGroup.appendChild(btnEdit);
        }

        card.appendChild(infoDiv);
        card.appendChild(btnGroup);
        listContainer.appendChild(card);
      });
    }

    // ID指定で直接編集を開く
    window.editItemByObject = function(catId, itemId) {
      let targetIndex = -1;

      const cat = checklistData.find(c => c.categoryId === catId);
      if (cat && cat.items) {
        targetIndex = cat.items.findIndex(it => it.id === itemId || it.targetModalId === itemId);
      }

      if (targetIndex === -1) {
        checklistData.forEach(c => {
          if (c.items) {
            const idx = c.items.findIndex(it => it.id === itemId || it.targetModalId === itemId);
            if (idx !== -1) {
              catId = c.categoryId;
              targetIndex = idx;
            }
          }
        });
      }

      if (catId !== -1 && targetIndex !== -1) {
        activeCatId = catId;
        renderCategoryTabs();
        editItem(targetIndex);
      } else {
        alert('編集対象の項目が見つかりませんでした。');
      }
    };

    // 並び替え
    window.moveItem = function(index, direction) {
      const currentCat = checklistData.find(c => c.categoryId === activeCatId);
      if (!currentCat) return;

      const targetIndex = index + direction;
      if (targetIndex < 0 || targetIndex >= currentCat.items.length) return;

      const temp = currentCat.items[index];
      currentCat.items[index] = currentCat.items[targetIndex];
      currentCat.items[targetIndex] = temp;

      renderItemsList();
      markUnsaved();
    };

    // 削除
    window.deleteItem = function(index) {
      const currentCat = checklistData.find(c => c.categoryId === activeCatId);
      if (!currentCat) return;

      const cleanName = currentCat.items[index].labelHtml.replace(/<[^>]*>/g, '');
      if (confirm(`「${cleanName}」を削除しますか？`)) {
        currentCat.items.splice(index, 1);
        renderCategoryTabs();
        renderItemsList();
        markUnsaved();
      }
    };

    // 編集ページへ遷移
    window.editItem = function(index) {
      window.location.href = `edit.php?cat_id=${activeCatId}&item_index=${index}`;
    };

    // 新規項目追加ページへ遷移
    function openAddItemModal() {
      window.location.href = `edit.php?cat_id=${activeCatId}&item_index=-1`;
    }

    // DOMParserによる保存前クリーンアップと正確な1重ラッパー適用
    function cleanAndWrapModalContent(editorHtml, modalLabelId, modalTitle) {
      try {
        const parser = new DOMParser();
        const doc = parser.parseFromString('<div>' + (editorHtml || '') + '</div>', 'text/html');
        const container = doc.body.firstElementChild;

        // 混入した modal-header や modal-body を完全除去・解体
        const headers = container.querySelectorAll('.modal-header');
        headers.forEach(h => h.remove());

        const modalBodies = container.querySelectorAll('.modal-body');
        modalBodies.forEach(mb => {
          while (mb.firstChild) {
            mb.parentNode.insertBefore(mb.firstChild, mb);
          }
          mb.remove();
        });

        // h5 タグに kokuban sticky-top クラスを補完
        const h5s = container.querySelectorAll('h5');
        h5s.forEach(h5 => {
          if (h5.classList.contains('modal-title')) return;
          if (!h5.classList.contains('speechBubble') && !h5.classList.contains('mark_b')) {
            if (!h5.classList.contains('kokuban')) {
              h5.classList.add('kokuban');
            }
            if (!h5.classList.contains('sticky-top')) {
              h5.classList.add('sticky-top');
            }
          }
        });

        // 空になったバッジ・ナンバリング枠（文字なしのゴミ枠）を完全除去
        const badges = container.querySelectorAll('.app-badge, .circle-num-blue, .bn, .app-badge-navy');
        badges.forEach(b => {
          const text = b.textContent.replace(/[\s\uFEFF\xA0\u200B]/g, '').trim();
          if (text === '') {
            b.remove();
          }
        });

        const cleanBodyHtml = container.innerHTML.trim();

        return `<div class="modal-header">
  <h5 class="modal-title kokuban" id="${modalLabelId}">${escapeHtml(modalTitle)}</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body d-inline-block text-wrap">
  ${cleanBodyHtml}
</div>`;
      } catch (e) {
        console.error('DOMParser wrapping error:', e);
        return `<div class="modal-header">
  <h5 class="modal-title kokuban" id="${modalLabelId}">${escapeHtml(modalTitle)}</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body d-inline-block text-wrap">
  ${editorHtml.trim()}
</div>`;
      }
    }

    // 保存（項目確定）
    function saveItemFromModal() {
      const catId = parseInt(document.getElementById('edit-cat-id').value);
      const index = parseInt(document.getElementById('edit-item-index').value);
      const label = document.getElementById('edit-label').value.trim();
      const modalTitle = document.getElementById('edit-modal-title').value.trim();
      
      let bodyHtml = document.getElementById('editor-body').innerHTML;

      if (!label) {
        alert('表示名を入力してください');
        return;
      }

      const currentCat = checklistData.find(c => c.categoryId === catId);
      if (!currentCat) return;

      const itemNum = index === -1 ? currentCat.items.length + 1 : (index + 1);
      const modalLabelId = `ModalLabel${catId}-${itemNum}`;

      const fullModalHtml = cleanAndWrapModalContent(bodyHtml, modalLabelId, modalTitle || label);

      if (index === -1) {
        const newId = `Check${catId}-${itemNum}`;
        currentCat.items.push({
          id: newId,
          name: newId,
          linkId: `M${catId}-${itemNum}`,
          targetModalId: `Modal${catId}-${itemNum}`,
          labelHtml: escapeHtml(label),
          modalContentHtml: fullModalHtml
        });
      } else {
        currentCat.items[index].labelHtml = escapeHtml(label);
        currentCat.items[index].modalContentHtml = fullModalHtml;
      }

      if (editorModalInstance) editorModalInstance.hide();
      renderCategoryTabs();
      renderItemsList();
      markUnsaved();
    }

    // 保存マーク
    function markUnsaved() {
      const status = document.getElementById('save-status');
      status.className = 'badge bg-warning text-dark';
      status.innerText = '未保存の変更あり';
    }

    // 全保存
    function saveAllData() {
      const btn = document.getElementById('btn-save-all');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 保存中...';

      const jsonString = JSON.stringify(checklistData);
      const b64Data = window.btoa(unescape(encodeURIComponent(jsonString)));

      fetch('api/save_checklist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ data_b64: b64Data })
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill"></i> 全体を保存';

        if (data.success) {
          const status = document.getElementById('save-status');
          status.className = 'badge bg-success';
          status.innerText = '保存済み';
          alert('全体データの保存が完了しました！本番画面にそのまま反映されます。');
        } else {
          alert('保存失敗: ' + data.message);
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill"></i> 全体を保存';
        alert('通信エラー: ' + err);
      });
    }

    // 標語データの初期化・読み込み
    let slogansData = ["横たわる 命を照らす ハイビーム","事故防止 一人一人が 責任者","シートベルト 命を守る お声掛け","見て、待って、自転車、二輪車、譲って防げ事故防止","後ろ側 見えてなければ 降りて見る","駐停車 まずは確認 Pレンジ","交差点 減速確認 再確認"];
    if (checklistData && checklistData.slogans) {
      slogansData = checklistData.slogans;
    } else if (checklistData && checklistData[0] && checklistData[0].slogans) {
      slogansData = checklistData[0].slogans;
    }

    const slogansModal = document.getElementById('slogansModal');
    if (slogansModal) {
      slogansModal.addEventListener('show.bs.modal', function() {
        document.querySelectorAll('.slogan-input').forEach(input => {
          const day = parseInt(input.getAttribute('data-day'), 10);
          if (slogansData[day] !== undefined) {
            input.value = slogansData[day];
          }
        });
      });
    }

    const btnSaveSlogans = document.getElementById('btn-save-slogans');
    if (btnSaveSlogans) {
      btnSaveSlogans.addEventListener('click', function() {
        const inputs = document.querySelectorAll('.slogan-input');
        inputs.forEach(input => {
          const day = parseInt(input.getAttribute('data-day'), 10);
          slogansData[day] = input.value.trim();
        });

        if (Array.isArray(checklistData)) {
          checklistData.slogans = slogansData;
          if (checklistData[0]) checklistData[0].slogans = slogansData;
        }

        const modalEl = document.getElementById('slogansModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        saveAll();
      });
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
  </script>

  <!-- 本日の安全標語 編集モーダル -->
  <div class="modal fade" id="slogansModal" tabindex="-1" aria-labelledby="slogansModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg border-0">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold" id="slogansModalLabel"><i class="bi bi-megaphone-fill me-2"></i>本日の安全標語（曜日別設定）</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i>本番画面の最上部に表示される曜日ごとの安全標語を変更できます。</p>
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label fw-bold text-danger"><i class="bi bi-calendar-event me-1"></i>日曜日:</label>
              <input type="text" class="form-control slogan-input" data-day="0" placeholder="例：横たわる 命を照らす ハイビーム">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold text-primary"><i class="bi bi-calendar-event me-1"></i>月曜日:</label>
              <input type="text" class="form-control slogan-input" data-day="1" placeholder="例：事故防止 一人一人が 責任者">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold text-danger"><i class="bi bi-calendar-event me-1"></i>火曜日:</label>
              <input type="text" class="form-control slogan-input" data-day="2" placeholder="例：シートベルト 命を守る お声掛け">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold text-info"><i class="bi bi-calendar-event me-1"></i>水曜日:</label>
              <input type="text" class="form-control slogan-input" data-day="3" placeholder="例：見て、待って、自転車、二輪車、譲って防げ事故防止">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold text-success"><i class="bi bi-calendar-event me-1"></i>木曜日:</label>
              <input type="text" class="form-control slogan-input" data-day="4" placeholder="例：後ろ側 見えてなければ 降りて見る">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold text-warning"><i class="bi bi-calendar-event me-1"></i>金曜日:</label>
              <input type="text" class="form-control slogan-input" data-day="5" placeholder="例：駐停車 まずは確認 Pレンジ">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold text-primary"><i class="bi bi-calendar-event me-1"></i>土曜日:</label>
              <input type="text" class="form-control slogan-input" data-day="6" placeholder="例：交差点 減速確認 再確認">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-success fw-bold px-4" id="btn-save-slogans"><i class="bi bi-check-circle me-1"></i>標語を保存する</button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
</body>
</html>