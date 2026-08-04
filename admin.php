<?php
session_start();

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
  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">

  <style>
    body {
      background-color: #f4f6f9;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .header-bar {
      background-color: #1b3a2f;
      color: #fff;
      padding: 0.75rem 1rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .header-bar h1 {
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 1.25rem;
      margin: 0;
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
      padding: 6px;
      overflow-x: auto;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
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
      padding: 12px;
      background: #fff;
      font-size: 1rem;
    }
    #editor-body:focus {
      outline: 2px solid #1b3a2f;
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
<body>

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
      <a href="index.html" target="_blank" class="btn btn-outline-light btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> 本番画面を表示
      </a>
      <button class="btn btn-warning btn-sm fw-bold px-3" id="btn-save-all">
        <i class="bi bi-cloud-arrow-up-fill"></i> 全体を保存
      </button>
      <a href="admin.php?action=logout" class="btn btn-outline-danger btn-sm text-white border-light">
        <i class="bi bi-box-arrow-right"></i> ログアウト
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
      <div class="d-flex gap-2">
        <input type="text" id="search-input" class="form-control" placeholder="🔍 項目名やキーワードで検索..." style="width: 250px;">
        <button class="btn btn-primary fw-bold" id="btn-add-item">
          <i class="bi bi-plus-lg"></i> このカテゴリに項目を追加
        </button>
      </div>
    </div>

    <!-- 項目一覧リスト -->
    <div id="items-list">
      <!-- JSで挿入 -->
    </div>
  </div>

  <!-- 項目編集・追加モーダル -->
  <div class="modal fade" id="editorModal" tabindex="-1" aria-labelledby="editorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="editorModalLabel"><i class="bi bi-pencil-square"></i> 項目の編集</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editorForm" onsubmit="return false;">
            <input type="hidden" id="edit-cat-id">
            <input type="hidden" id="edit-item-index">

            <!-- 項目名 -->
            <div class="mb-3">
              <label for="edit-label" class="form-label fw-bold">チェックボックスの表示名（例: 本採用について）</label>
              <input type="text" class="form-control form-control-lg" id="edit-label" required placeholder="例：本採用について">
            </div>

            <!-- モーダルタイトル -->
            <div class="mb-3">
              <label for="edit-modal-title" class="form-label fw-bold">ポップアップ（モーダル）のタイトル</label>
              <input type="text" class="form-control" id="edit-modal-title" required placeholder="例：本採用に向けて気をつけること">
            </div>

            <!-- 堅牢なノーコードビジュアルエディター -->
            <div class="mb-3">
              <label class="form-label fw-bold">モーダルの説明本文（Wordのように編集できます）</label>
              <div class="editor-toolbar d-flex flex-wrap gap-1 align-items-center">
                <button type="button" class="btn btn-outline-secondary border" onclick="execCmd('undo')" title="元に戻す (Ctrl+Z)"><i class="bi bi-arrow-counterclockwise"></i> 元に戻す</button>
                <button type="button" class="btn btn-outline-secondary border" onclick="execCmd('redo')" title="やり直す (Ctrl+Y)"><i class="bi bi-arrow-clockwise"></i> やり直し</button>
                <div class="vr mx-1"></div>
                <button type="button" class="btn btn-light border" onclick="execCmd('bold')" title="太字"><i class="bi bi-type-bold"></i></button>
                <button type="button" class="btn btn-light border" onclick="execCmd('italic')" title="斜体"><i class="bi bi-type-italic"></i></button>
                <button type="button" class="btn btn-light border" onclick="execCmd('underline')" title="下線"><i class="bi bi-type-underline"></i></button>
                <div class="vr mx-1"></div>
                <button type="button" class="btn btn-light border" onclick="execCmd('insertUnorderedList')" title="箇条書きリスト"><i class="bi bi-list-ul"></i></button>
                <button type="button" class="btn btn-light border" onclick="execCmd('insertOrderedList')" title="番号付きリスト"><i class="bi bi-list-ol"></i></button>
                <div class="vr mx-1"></div>
                <button type="button" class="btn btn-light border" onclick="execCmd('formatBlock', 'h5')" title="見出し"><i class="bi bi-type-h1"></i> 見出し</button>
                <button type="button" class="btn btn-light border" onclick="execCmd('formatBlock', 'p')" title="標準テキスト"><i class="bi bi-text-paragraph"></i> 標準</button>
                <div class="vr mx-1"></div>
                <button type="button" class="btn btn-outline-primary border" onclick="insertLink()" title="リンク（Webサイト・電話番号など）を挿入・修正"><i class="bi bi-link-45deg"></i> リンク追加/修正</button>
                <button type="button" class="btn btn-outline-primary border" onclick="insertTable()" title="表（テーブル）を作成"><i class="bi bi-table"></i> 表を追加</button>
                <button type="button" class="btn btn-outline-danger border" onclick="insertAlertBox('danger')" title="警告枠（赤）を追加"><i class="bi bi-exclamation-triangle"></i> 赤枠バナー</button>
                <button type="button" class="btn btn-outline-success border text-dark" onclick="insertAlertBox('success')" title="完了・成功枠（緑）を追加"><i class="bi bi-check-circle"></i> 緑枠バナー</button>
                <button type="button" class="btn btn-outline-info border text-dark" onclick="insertAlertBox('info')" title="補足枠（青）を追加"><i class="bi bi-info-circle"></i> 青枠バナー</button>
                <div class="vr mx-1"></div>
                <button type="button" class="btn btn-secondary border text-white fw-bold" onclick="triggerImgUpload()"><i class="bi bi-image"></i> 写真を追加</button>
                <input type="file" id="native-img-upload" accept="image/*" style="display:none;" onchange="handleNativeImgUpload(this)">
              </div>
              <div id="editor-body" contenteditable="true"></div>
            </div>

          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-success fw-bold px-4" id="btn-save-item">
            <i class="bi bi-check-circle-fill"></i> この項目を保存して一覧に戻る
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let checklistData = [];
    let activeCatId = 0;
    let editorModalInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
      try {
        editorModalInstance = new bootstrap.Modal(document.getElementById('editorModal'));
      } catch(e) {
        console.warn('Bootstrap modal init error:', e);
      }

      const searchInput = document.getElementById('search-input');
      if (searchInput) {
        searchInput.addEventListener('input', function() {
          const query = this.value.toLowerCase().trim();
          renderItemsList(query);
        });
      }

      document.getElementById('btn-save-all').addEventListener('click', saveAllData);
      document.getElementById('btn-add-item').addEventListener('click', openAddItemModal);
      document.getElementById('btn-save-item').addEventListener('click', saveItemFromModal);

      loadChecklist();
    });

    // ツールバーコマンド実行（太字・リスト等）
    window.execCmd = function(command, value = null) {
      document.execCommand(command, false, value);
      document.getElementById('editor-body').focus();
    };

    // リンクの挿入・修正
    window.insertLink = function() {
      const selection = window.getSelection();
      const selectedText = selection.toString();

      const url = prompt('リンク先のURLまたは電話番号（tel:03-...）を入力してください:', 'https://');
      if (!url) return;

      if (selectedText) {
        execCmd('createLink', url);
      } else {
        const linkText = prompt('リンクとして表示する文字を入力してください:', 'リンク');
        if (!linkText) return;
        const linkHtml = `<a href="${escapeHtml(url)}" target="_blank" rel="noopener">${escapeHtml(linkText)}</a>`;
        execCmd('insertHTML', linkHtml);
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

      execCmd('insertHTML', html);
    };

    // インフォボックス（注意書き・バナー）の挿入
    window.insertAlertBox = function(type) {
      let defaultText = 'ここに説明事項を入力してください';
      if (type === 'danger') defaultText = 'ここに警告・注意事項を入力してください';
      if (type === 'success') defaultText = 'ここに完了・確認事項（緑枠）を入力してください';
      if (type === 'info') defaultText = 'ここに補足・お知らせ事項を入力してください';

      const alertHtml = `<div class="alert alert-${type} my-2" role="alert">${defaultText}</div><p><br></p>`;
      execCmd('insertHTML', alertHtml);
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

    // データの読み込み
    function loadChecklist() {
      fetch('data/checklist.json?v=' + new Date().getTime())
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data) && data.length > 0) {
            checklistData = data;
            if (activeCatId === 0 && data[0].categoryId !== undefined) {
              activeCatId = data[0].categoryId;
            }
            renderCategoryTabs();
            renderItemsList();
          } else {
            document.getElementById('items-list').innerHTML = '<div class="alert alert-warning text-center">データが空です</div>';
          }
        })
        .catch(err => {
          console.warn('[Admin] checklist.json 直接読込失敗、API経由で試行:', err);
          fetch('api/get_checklist.php?v=' + new Date().getTime())
            .then(res => res.json())
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
            .catch(err2 => {
              alert('データの読み込みに失敗しました:\n' + err2.message);
            });
        });
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
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary">${searchQuery ? escapeHtml(categoryTitle) : '#' + (index + 1)}</span>
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

    // 編集モーダルを開く
    window.editItem = function(index) {
      const currentCat = checklistData.find(c => c.categoryId === activeCatId);
      if (!currentCat) return;

      const item = currentCat.items[index];
      document.getElementById('edit-cat-id').value = activeCatId;
      document.getElementById('edit-item-index').value = index;

      document.getElementById('editorModalLabel').innerHTML = '<i class="bi bi-pencil-square"></i> 項目の編集';
      document.getElementById('edit-label').value = item.labelHtml.replace(/<[^>]*>/g, '') || '';
      
      let modalTitle = '';
      let modalBodyHtml = item.modalContentHtml || '';

      const match = modalBodyHtml.match(/<h5 class="modal-title[^">]*>(.*?)<\/h5>/s);
      if (match) {
        modalTitle = match[1].replace(/<button.*$/s, '').replace(/<[^>]*>/g, '').trim();
      }

      document.getElementById('edit-modal-title').value = modalTitle || item.labelHtml.replace(/<[^>]*>/g, '');
      
      let bodyOnlyHtml = modalBodyHtml;
      const bodyMatch = modalBodyHtml.match(/<div class="modal-body[^">]*>(.*?)<\/div>\s*$/s);
      if (bodyMatch) {
        bodyOnlyHtml = bodyMatch[1];
      }

      document.getElementById('editor-body').innerHTML = bodyOnlyHtml;
      if (editorModalInstance) editorModalInstance.show();
    };

    // 新規項目追加モーダルを開く
    function openAddItemModal() {
      const currentCat = checklistData.find(c => c.categoryId === activeCatId);
      if (!currentCat) return;

      document.getElementById('edit-cat-id').value = activeCatId;
      document.getElementById('edit-item-index').value = -1;

      document.getElementById('editorModalLabel').innerHTML = '<i class="bi bi-plus-circle-fill"></i> 新規項目の追加';
      document.getElementById('edit-label').value = '';
      document.getElementById('edit-modal-title').value = '';
      
      document.getElementById('editor-body').innerHTML = '';
      if (editorModalInstance) editorModalInstance.show();
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

      const fullModalHtml = `<div class="modal-header">
  <h5 class="modal-title kokuban" id="${modalLabelId}">${escapeHtml(modalTitle || label)}</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body d-inline-block text-wrap">
  ${bodyHtml}
</div>`;

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

      fetch('api/save_checklist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(checklistData)
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

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
  </script>
<?php endif; ?>
</body>
</html>