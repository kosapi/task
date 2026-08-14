<?php
session_start();

// キャッシュ保持による旧JSエラー誤動作を回避するレスポンスヘッダー
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// ログイン判定（未ログイン時は管理画面ログインへ）
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$item_index = isset($_GET['item_index']) ? (int)$_GET['item_index'] : -1;

// checklist.json の初期データを直接読み込み（ロードエラー防止）
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
  <title>項目編集 - ワークチェックリスト 管理画面</title>

  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/main_v55.css?v=20260813_FINAL_SUPER_CACHEBUST_1786630246">

  <style>
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
      font-size: 1.2rem;
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
    
    /* 追従固定ツールバー */
    .sticky-editor-toolbar {
      position: sticky;
      top: 56px;
      z-index: 1030;
      background: #ffffff;
      border: 1px solid #ced4da;
      border-radius: 8px 8px 0 0;
      padding: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .sticky-editor-toolbar .btn {
      padding: 6px 12px;
      font-size: 0.9rem;
    }

    #editor-body {
      min-height: 550px;
      padding: 20px;
      background: #ffffff;
      border: 1px solid #ced4da;
      border-top: none;
      border-radius: 0 0 8px 8px;
      font-size: 1.05rem;
      line-height: 1.8;
      box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    #editor-body:focus {
      outline: 2px solid #1b3a2f;
    }

    /* エディター本文要素の上下余白（本番表示の見た目に合わせる） */
    #editor-body p {
      margin-top: 0.8rem;
      margin-bottom: 1.2rem;
      line-height: 1.8;
    }
    #editor-body div {
      margin-top: 0.8rem;
      margin-bottom: 1.5rem;
      padding: 6px;
      border-radius: 6px;
    }
    #editor-body ul, #editor-body ol {
      margin-top: 0.6rem;
      margin-bottom: 1.2rem;
      padding-left: 1.8rem;
    }
    #editor-body li {
      margin-bottom: 0.6rem;
      line-height: 1.75;
    }
    #editor-body h5 {
      margin-top: 1.5rem;
      margin-bottom: 0.8rem;
      font-weight: bold;
      padding: 8px 12px;
      border-radius: 4px;
    }
    #editor-body h5.kokuban, #editor-body h5.sticky-top {
      background-color: #1b3a2f !important;
      color: #ffffff !important;
      border-left: 5px solid #28a745;
      display: block;
    }
    #editor-body h5.kokuban::before {
      content: "📌 固定見出し: ";
      font-size: 0.85em;
      opacity: 0.85;
      margin-right: 4px;
    }
    #editor-body .alert {
      margin-top: 1.2rem;
      margin-bottom: 1.2rem;
    }
    #editor-body hr {
      margin-top: 1.5rem;
      margin-bottom: 1.5rem;
    }

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

    /* 空のバッジ・装飾枠の非表示（縦長空枠の発生防止） */
    .bn:empty,
    .bman:empty,
    .bnumber:empty,
    .bnumber2:empty,
    .bgsya:empty,
    .circle:empty,
    .bblack:empty,
    .borange:empty,
    .borange2:empty,
    .bred:empty,
    .bbn:empty,
    .bgreen:empty,
    .bwhite:empty,
    .bm:empty,
    .bb:empty,
    .bg:empty,
    .by:empty,
    .mark_g:empty,
    .mark_b:empty,
    .mark_r:empty,
    .app-badge:empty,
    .circle-num-blue:empty {
      display: none !important;
    }

    /* エディタ内バッジのホバーヒント */
    #editor-body .app-badge,
    #editor-body .circle-num-blue,
    #editor-body .bn,
    #editor-body .bb,
    #editor-body .bgsya,
    #editor-body .borange,
    #editor-body .borange2,
    #editor-body .bblack,
    #editor-body .bwhite,
    #editor-body .bman,
    #editor-body .bnumber,
    #editor-body .bred,
    #editor-body .bg,
    #editor-body .by {
      cursor: pointer;
      position: relative;
      transition: outline 0.15s ease;
    }
    #editor-body .app-badge:hover,
    #editor-body .circle-num-blue:hover,
    #editor-body .bn:hover,
    #editor-body .bb:hover,
    #editor-body .bgsya:hover,
    #editor-body .borange:hover,
    #editor-body .bblack:hover {
      outline: 2px dashed #ff9800;
    }

    /* ソースコード編集用テキストエリア */
    #editor-html-source {
      font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
      tab-size: 2;
    }
  </style>
</head>
<body class="edit-body">

  <!-- ヘッダー -->
  <div class="header-bar d-flex justify-content-between align-items-center sticky-top">
    <div class="d-flex align-items-center gap-3">
      <a href="admin.php" class="btn btn-outline-light btn-sm fw-bold">
        <i class="bi bi-arrow-left"></i> 一覧に戻る
      </a>
      <h1 id="page-title"><i class="bi bi-pencil-square"></i> 項目の編集</h1>
    </div>
    <div class="d-flex gap-2">
      <a href="index.html" target="_blank" class="btn btn-outline-light btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> 本番画面を表示
      </a>
      <button class="btn btn-success fw-bold px-4 shadow-sm" id="btn-save-page">
        <i class="bi bi-check-circle-fill me-1"></i> 保存して一覧に戻る
      </button>
    </div>
  </div>

  <div class="container py-4" style="max-width: 960px;">
    
    <div class="card border-0 shadow-sm p-4 mb-4">
      <input type="hidden" id="edit-cat-id" value="<?= $cat_id ?>">
      <input type="hidden" id="edit-item-index" value="<?= $item_index ?>">

      <!-- 項目名 -->
      <div class="mb-3">
        <label for="edit-label" class="form-label fw-bold fs-5">1. チェックボックスの表示名</label>
        <input type="text" class="form-control form-control-lg" id="edit-label" required placeholder="例：本採用について">
      </div>

      <!-- モーダルタイトル -->
      <div class="mb-3">
        <label for="edit-modal-title" class="form-label fw-bold fs-5">2. ポップアップ（モーダル）のタイトル</label>
        <input type="text" class="form-control form-control-lg" id="edit-modal-title" required placeholder="例：本採用に向けて気をつけること">
      </div>
    </div>

    <!-- ビジュアルエディター -->
    <div class="mb-5">
      <label class="form-label fw-bold fs-5 mb-2">3. モーダルの説明本文（Wordのように自由編集できます）</label>
      
      <!-- 画面スクロール時も上に固定追従するツールバー -->
      <div class="sticky-editor-toolbar d-flex flex-wrap gap-1 align-items-center">
        <button type="button" class="btn btn-outline-secondary border" onclick="execCmd('undo')" title="元に戻す (Ctrl+Z)"><i class="bi bi-arrow-counterclockwise"></i> 元に戻す</button>
        <button type="button" class="btn btn-outline-secondary border" onclick="execCmd('redo')" title="やり直す (Ctrl+Y)"><i class="bi bi-arrow-clockwise"></i> やり直し</button>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-light border" onclick="execCmd('bold')" title="太字"><i class="bi bi-type-bold"></i></button>
        <button type="button" class="btn btn-light border" onclick="execCmd('italic')" title="斜体"><i class="bi bi-type-italic"></i></button>
        <button type="button" class="btn btn-light border" onclick="execCmd('underline')" title="下線"><i class="bi bi-type-underline"></i></button>
        <button type="button" class="btn btn-light border" onclick="formatStrikeThrough(); return false;" title="取り消し線を追加"><i class="bi bi-type-strikethrough"></i> 取消線</button>
        <button type="button" class="btn btn-outline-danger border" onclick="removeStrikeThrough(); return false;" title="取り消し線を解除"><i class="bi bi-dash-circle me-1"></i> 取消線解除</button>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-light border" onclick="execCmd('justifyLeft')" title="文章や選択テキストを左揃えにする"><i class="bi bi-text-left"></i> 左揃え</button>
        <button type="button" class="btn btn-light border" onclick="execCmd('justifyCenter')" title="文章や選択テキストを中央揃えにする"><i class="bi bi-text-center"></i> 中央揃え</button>
        <button type="button" class="btn btn-light border" onclick="execCmd('justifyRight')" title="文章や選択テキストを右揃えにする"><i class="bi bi-text-right"></i> 右揃え</button>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-light border" onclick="execCmd('insertUnorderedList')" title="箇条書きリスト"><i class="bi bi-list-ul"></i></button>
        <button type="button" class="btn btn-light border" onclick="execCmd('insertOrderedList')" title="番号付きリスト"><i class="bi bi-list-ol"></i></button>
        <button type="button" class="btn btn-warning border text-dark fw-bold" onclick="exitListFormat()" title="リストを抜けて新しい段落行を作成"><i class="bi bi-box-arrow-down me-1"></i> リスト解除・新行作成</button>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-primary border text-white fw-bold shadow-sm" onclick="formatH5Sticky()" title="選択した文字や行を固定見出し(h5)にする"><i class="bi bi-pin-angle-fill me-1"></i> 固定見出しにする</button>
        <button type="button" class="btn btn-light border" onclick="resetToNormalText()" title="標準テキストに戻す・バナー枠を解除"><i class="bi bi-text-paragraph"></i> 標準テキストに戻す</button>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-outline-primary border" onclick="insertLink()" title="リンク（Webサイト・電話番号など）を挿入・修正"><i class="bi bi-link-45deg"></i> リンク追加/修正</button>
        <button type="button" class="btn btn-outline-primary border" onclick="insertTable()" title="表（テーブル）を作成"><i class="bi bi-table"></i> 表を追加</button>
        <button type="button" class="btn btn-outline-danger border" onclick="insertAlertBox('danger')" title="警告枠（赤）を追加"><i class="bi bi-exclamation-triangle"></i> 赤枠バナー</button>
        <button type="button" class="btn btn-outline-success border text-dark" onclick="insertAlertBox('success')" title="完了・成功枠（緑）を追加"><i class="bi bi-check-circle"></i> 緑枠バナー</button>
        <button type="button" class="btn btn-outline-info border text-dark" onclick="insertAlertBox('info')" title="補足枠（青）を追加"><i class="bi bi-info-circle"></i> 青枠バナー</button>
        <div class="vr mx-1"></div>
        <!-- 丸青ナンバリング（円形青バッジ）挿入メニュー -->
        <div class="dropdown d-inline-block">
          <button class="btn btn-outline-primary border dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="青丸の中に白文字数字（①、②など）を挿入">
            <span class="circle-num-blue" style="width:20px;height:20px;font-size:0.75em;margin:0 2px 0 0;">1</span> 丸青数字挿入
          </button>
          <ul class="dropdown-menu shadow">
            <li><h6 class="dropdown-header">▼ ナンバリングを挿入</h6></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertCircleNum('1'); return false;"><span class="circle-num-blue">1</span> 丸青 1 を挿入</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertCircleNum('2'); return false;"><span class="circle-num-blue">2</span> 丸青 2 を挿入</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertCircleNum('3'); return false;"><span class="circle-num-blue">3</span> 丸青 3 を挿入</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertCircleNum('4'); return false;"><span class="circle-num-blue">4</span> 丸青 4 を挿入</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertCircleNum('5'); return false;"><span class="circle-num-blue">5</span> 丸青 5 を挿入</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="makeSelectionCircleNum(); return false;"><i class="bi bi-cursor-fill me-1"></i>選択した数字/文字を <span class="circle-num-blue">丸青</span> にする</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="promptCircleNum(); return false;"><i class="bi bi-pencil me-1"></i>自由な数字/文字で丸青アイコン作成...</a></li>
          </ul>
        </div>
        <div class="vr mx-1"></div>
        <!-- ボタン風バッジ挿入メニュー -->
        <div class="dropdown d-inline-block">
          <button class="btn btn-primary border dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false" onmousedown="saveSelection()">
            <i class="bi bi-tag-fill"></i> ボタンバッジ挿入
          </button>
          <ul class="dropdown-menu shadow">
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertBadge('bn', 'メッセージ'); return false;"><span class="bn">メッセージ</span> を挿入 (濃紺枠/白背景)</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertBadge('app-badge-blue', '了解'); return false;"><span class="app-badge app-badge-blue">了解</span> を挿入 (青)</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertBadge('app-badge-yellow', '迎車'); return false;"><span class="app-badge app-badge-yellow">迎車</span> を挿入 (黄/黒枠)</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertBadge('app-badge-black', '迎車'); return false;"><span class="app-badge app-badge-black">迎車</span> を挿入 (黒/オレンジ)</a></li>
            <li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="insertBadge('app-badge-blue', 'ナビ案内を確認'); return false;"><span class="app-badge app-badge-blue">ナビ案内を確認</span> を挿入 (青)</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">▼ 選択テキストをバッジ化 / カスタム作成</h6></li>
            <li><a class="dropdown-item py-1" href="javascript:void(0);" onclick="makeSelectionBadge('bn'); return false;">選択した文字を <span class="bn">濃紺枠バッジ</span> にする</a></li>
            <li><a class="dropdown-item py-1" href="javascript:void(0);" onclick="makeSelectionBadge('app-badge-blue'); return false;">選択した文字を <span class="app-badge app-badge-blue">青バッジ</span> にする</a></li>
            <li><a class="dropdown-item py-1" href="javascript:void(0);" onclick="makeSelectionBadge('app-badge-yellow'); return false;">選択した文字を <span class="app-badge app-badge-yellow">黄バッジ</span> にする</a></li>
            <li><a class="dropdown-item py-1" href="javascript:void(0);" onclick="makeSelectionBadge('app-badge-black'); return false;">選択した文字を <span class="app-badge app-badge-black">黒バッジ</span> にする</a></li>
            <li><a class="dropdown-item py-1" href="javascript:void(0);" onclick="insertCustomBadge(); return false;"><i class="bi bi-pencil me-1"></i>自由な文字でバッジ作成...</a></li>
          </ul>
        </div>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-secondary border text-white fw-bold" onclick="triggerImgUpload()"><i class="bi bi-image"></i> 写真を追加</button>
        <input type="file" id="native-img-upload" accept="image/*" style="display:none;" onchange="handleNativeImgUpload(this)">
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-outline-danger border fw-bold" onclick="cleanAllEmptyTags(true); return false;" title="エディター内の文字が入っていない不要な空枠・空タグを一括削除"><i class="bi bi-trash3-fill me-1"></i> 空枠一括削除</button>
        <button type="button" class="btn btn-dark border text-white fw-bold shadow-sm" onclick="toggleHtmlSourceMode(); return false;" id="btn-toggle-source" title="HTMLソースコードを直接確認・編集"><i class="bi bi-code-slash me-1"></i> <span id="source-mode-text">HTMLソース</span></button>
      </div>

      <!-- エディター本文領域 -->
      <div id="editor-body" contenteditable="true"></div>
      <textarea id="editor-html-source" class="form-control" style="display:none; min-height: 550px; font-size: 0.95rem; line-height: 1.6; background-color: #1e1e1e; color: #e0e0e0; border-radius: 0 0 8px 8px; padding: 16px; border: 1px solid #ced4da; border-top: none;" placeholder="HTMLソースコードを直接編集できます"></textarea>
    </div>

    <!-- 下部保存ボタン -->
    <div class="d-flex justify-content-between align-items-center py-3 mb-5 border-top">
      <a href="admin.php" class="btn btn-secondary btn-lg">キャンセルして一覧に戻る</a>
      <button class="btn btn-success btn-lg fw-bold px-5 shadow" id="btn-save-page-bottom">
        <i class="bi bi-check-circle-fill me-2"></i> 保存して一覧に戻る
      </button>
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
    const catId = <?= $cat_id ?>;
    const itemIndex = <?= $item_index ?>;

    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('btn-save-page').addEventListener('click', saveItemAndReturn);
      document.getElementById('btn-save-page-bottom').addEventListener('click', saveItemAndReturn);

      const editor = document.getElementById('editor-body');
      if (editor) {
        ['keyup', 'mouseup', 'touchend', 'input', 'focus', 'blur'].forEach(evtName => {
          editor.addEventListener(evtName, function() {
            saveSelection();
            cleanAllEmptyTags(false);
          });
        });

        // バッジ要素をダブルクリックで安全に削除できる機能
        editor.addEventListener('dblclick', function(e) {
          const targetBadge = e.target.closest('.app-badge, .circle-num-blue, .bn, .bb, .bgsya, .borange, .borange2, .bblack, .bwhite, .bman, .bnumber, .bnumber2, .bred, .bgreen, .bg, .by, .bm, .bbn, .mark_b');
          if (targetBadge && editor.contains(targetBadge)) {
            const badgeText = targetBadge.textContent.trim() || '空枠';
            if (confirm(`装飾枠「${badgeText}」を削除しますか？\n\n[OK]: この枠を削除\n[キャンセル]: そのまま残す`)) {
              targetBadge.remove();
              cleanAllEmptyTags(false);
            }
          }
        });
      }
      document.addEventListener('selectionchange', function() {
        if (document.activeElement && document.activeElement.id === 'editor-body') {
          saveSelection();
          cleanAllEmptyTags(false);
        }
      });

      loadDataAndInit();
    });

    function loadDataAndInit() {
      // 埋め込まれた初期データで即時フォーム初期化（ロード待ちゼロ）
      if (Array.isArray(checklistData) && checklistData.length > 0) {
        initFormValues();
      }

      fetch('data/checklist.json?v=' + new Date().getTime())
        .then(res => res.ok ? res.json() : null)
        .then(data => {
          if (Array.isArray(data) && data.length > 0) {
            checklistData = data;
            initFormValues();
          }
        })
        .catch(err => {
          console.warn('[Edit] 非同期通信スキップ（初期データ使用）:', err);
        });
    }

    // DOMParserによる堅牢なモーダルHTML分解・抽出
    function extractModalBodyContent(fullModalHtml) {
      if (!fullModalHtml || !fullModalHtml.trim()) {
        return { title: '', bodyHtml: '' };
      }

      try {
        const parser = new DOMParser();
        const doc = parser.parseFromString(fullModalHtml, 'text/html');

        let title = '';
        const titleElem = doc.querySelector('.modal-header .modal-title');
        if (titleElem) {
          title = titleElem.textContent.trim();
        }

        // modal-header は一括除去
        const headers = doc.querySelectorAll('.modal-header');
        headers.forEach(h => h.remove());

        // modal-body 要素を探してネスト解除
        const bodyElems = doc.querySelectorAll('.modal-body');
        if (bodyElems.length > 0) {
          let combinedHtml = '';
          bodyElems.forEach(bodyElem => {
            // ネストした modal-body を解体
            const innerBodies = bodyElem.querySelectorAll('.modal-body');
            innerBodies.forEach(ib => {
              while (ib.firstChild) {
                ib.parentNode.insertBefore(ib.firstChild, ib);
              }
              ib.remove();
            });
            combinedHtml += bodyElem.innerHTML;
          });
          return { title: title, bodyHtml: combinedHtml.trim() };
        }

        // modal-body がない場合は body 全体の innerHTML
        return { title: title, bodyHtml: doc.body.innerHTML.trim() };
      } catch (e) {
        console.error('DOMParser extraction error:', e);
        // フォールバック
        return { title: '', bodyHtml: fullModalHtml.replace(/<div class="modal-header[^">]*>.*?<\/div>\s*/gs, '').trim() };
      }
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

        // 過去のCMS画像削除ボタン（ゴミデータ）を完全除去
        container.querySelectorAll('.cms-image-delete-btn').forEach(b => b.remove());

        // 空になったバッジ・装飾枠（文字なしのゴミ枠・空タグ）を完全除去
        const emptySelector = '.app-badge, .circle-num-blue, .bn, .bb, .bgsya, .borange, .borange2, .bblack, .bwhite, .bman, .bnumber, .bnumber2, .bred, .bgreen, .bg, .by, .bm, .bbn, .mark_b, .mark_g, .mark_r, span, a, b, strong, i, u, s, strike, del, small';
        for (let pass = 0; pass < 3; pass++) {
          let passRemoved = 0;
          const badges = container.querySelectorAll(emptySelector);
          badges.forEach(b => {
            if (b.querySelector('img, button, table, input, textarea, svg, iframe')) return;
            const text = b.textContent.replace(/[\s\uFEFF\xA0\u200B\u200C\u200D\u3000]/g, '').trim();
            if (text === '') {
              b.remove();
              passRemoved++;
            }
          });
          if (passRemoved === 0) break;
        }

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

    function initFormValues() {
      const currentCat = checklistData.find(c => c.categoryId === catId);
      if (!currentCat) {
        alert('指定されたカテゴリが見つかりません');
        window.location.href = 'admin.php';
        return;
      }

      if (itemIndex === -1) {
        // 新規作成
        document.getElementById('page-title').innerHTML = '<i class="bi bi-plus-circle-fill"></i> 「' + escapeHtml(currentCat.categoryTitle) + '」に新規項目を追加';
        document.getElementById('edit-label').value = '';
        document.getElementById('edit-modal-title').value = '';
        document.getElementById('editor-body').innerHTML = '';
        cleanAllEmptyTags(false);
      } else {
        // 既存項目編集
        const item = currentCat.items[itemIndex];
        if (!item) {
          alert('指定された項目が見つかりません');
          window.location.href = 'admin.php';
          return;
        }

        document.getElementById('page-title').innerHTML = '<i class="bi bi-pencil-square"></i> 「' + escapeHtml(currentCat.categoryTitle) + '」の項目を編集';
        document.getElementById('edit-label').value = item.labelHtml.replace(/<[^>]*>/g, '') || '';

        const extracted = extractModalBodyContent(item.modalContentHtml || '');
        const modalTitle = extracted.title || item.labelHtml.replace(/<[^>]*>/g, '');

        document.getElementById('edit-modal-title').value = modalTitle;
        document.getElementById('editor-body').innerHTML = extracted.bodyHtml;
        cleanAllEmptyTags(false);
      }
    }

    // 保存して一覧にリダイレクト
    function saveItemAndReturn() {
      // HTMLソース編集モードの場合は最新内容をビジュアル側に反映
      if (isSourceMode) {
        const sourceTextarea = document.getElementById('editor-html-source');
        const editor = document.getElementById('editor-body');
        if (sourceTextarea && editor) {
          editor.innerHTML = sourceTextarea.value;
        }
      }
      // 保存直前の不要空タグクリーンアップ
      cleanAllEmptyTags(false);

      const label = document.getElementById('edit-label').value.trim();
      const modalTitle = document.getElementById('edit-modal-title').value.trim();
      const bodyHtml = document.getElementById('editor-body').innerHTML;

      if (!label) {
        alert('チェックボックスの表示名を入力してください');
        document.getElementById('edit-label').focus();
        return;
      }

      const currentCat = checklistData.find(c => c.categoryId === catId);
      if (!currentCat) return;

      let itemNum = itemIndex === -1 ? currentCat.items.length + 1 : (itemIndex + 1);
      let modalLabelId = `ModalLabel${catId}-${itemNum}`;

      if (itemIndex !== -1 && currentCat.items[itemIndex]) {
        const existingItem = currentCat.items[itemIndex];
        if (existingItem.targetModalId) {
          const m = existingItem.targetModalId.match(/\d+-\d+/);
          if (m) {
            modalLabelId = `ModalLabel${m[0]}`;
          }
        }
      }

      const fullModalHtml = cleanAndWrapModalContent(bodyHtml, modalLabelId, modalTitle || label);

      if (itemIndex === -1) {
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
        currentCat.items[itemIndex].labelHtml = escapeHtml(label);
        currentCat.items[itemIndex].modalContentHtml = fullModalHtml;
      }

      // 保存API実行
      const btn = document.getElementById('btn-save-page');
      const btnBottom = document.getElementById('btn-save-page-bottom');
      
      [btn, btnBottom].forEach(b => {
        if(b) {
          b.disabled = true;
          b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> 保存中...';
        }
      });

      // WAF（セキュリティ遮断 403）を完璧に回避するためBase64エンコードして送信
      const jsonString = JSON.stringify(checklistData);
      const b64Data = window.btoa(unescape(encodeURIComponent(jsonString)));

      fetch('api/save_checklist.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ data_b64: b64Data })
      })
      .then(async res => {
        const text = await res.text();
        try {
          return JSON.parse(text);
        } catch(e) {
          console.error('API Error Response:', text);
          throw new Error('サーバーから不正な応答（HTML）が返されました。一度ログインをやり直してください。');
        }
      })
      .then(data => {
        if (data && data.success) {
          alert('保存が完了しました！管理画面一覧に戻ります。');
          window.location.href = 'admin.php?t=' + new Date().getTime();
        } else {
          resetButtons();
          alert('保存失敗: ' + (data ? data.message : '不明なエラー'));
        }
      })
      .catch(err => {
        resetButtons();
        alert('エラーが発生しました:\n' + err.message);
      });

      function resetButtons() {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> 保存して一覧に戻る';
        }
        if (btnBottom) {
          btnBottom.disabled = false;
          btnBottom.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> 保存して一覧に戻る';
        }
      }
    }

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

    // HTMLソースコード編集モードフラグ
    let isSourceMode = false;

    // HTMLソース直接編集モード（ビジュアル ⇄ HTML切り替え）
    window.toggleHtmlSourceMode = function() {
      const editor = document.getElementById('editor-body');
      const sourceTextarea = document.getElementById('editor-html-source');
      const btn = document.getElementById('btn-toggle-source');
      if (!editor || !sourceTextarea) return;

      isSourceMode = !isSourceMode;

      if (isSourceMode) {
        // ビジュアル → HTMLソース表示
        cleanAllEmptyTags(false);
        sourceTextarea.value = formatHtmlString(editor.innerHTML);
        editor.style.display = 'none';
        sourceTextarea.style.display = 'block';
        sourceTextarea.focus();

        if (btn) {
          btn.className = 'btn btn-warning border text-dark fw-bold shadow-sm';
          btn.innerHTML = '<i class="bi bi-eye-fill me-1"></i> <span id="source-mode-text">ビジュアル編集に戻る</span>';
        }
      } else {
        // HTMLソース → ビジュアル表示
        editor.innerHTML = sourceTextarea.value;
        cleanAllEmptyTags(false);
        sourceTextarea.style.display = 'none';
        editor.style.display = 'block';
        editor.focus();

        if (btn) {
          btn.className = 'btn btn-dark border text-white fw-bold shadow-sm';
          btn.innerHTML = '<i class="bi bi-code-slash me-1"></i> <span id="source-mode-text">HTMLソース</span>';
        }
      }
    };

    // 読みやすいHTML整形ヘルパー
    function formatHtmlString(html) {
      if (!html) return '';
      return html
        .replace(/>\s*</g, '>\n<')
        .replace(/(<\/p>|<\/li>|<\/h5>|<\/div>|<\/tr>|<\/table>|<hr[^>]*>)/gi, '$1\n')
        .trim();
    }

    // エディター内の文字のない空バッジ枠・不要な空タグを一掃・完全消去
    window.cleanAllEmptyTags = function(showNotification = false) {
      const editor = document.getElementById('editor-body');
      if (!editor) return 0;

      // 過去のCMS画像削除ボタン（ゴミデータ）を完全消去
      let removedCount = 0;
      const cmsBtns = editor.querySelectorAll('.cms-image-delete-btn');
      cmsBtns.forEach(b => {
        b.remove();
        removedCount++;
      });

      const selector = '.app-badge, .circle-num-blue, .bn, .bb, .bgsya, .borange, .borange2, .bblack, .bwhite, .bman, .bnumber, .bnumber2, .bred, .bgreen, .bg, .by, .bm, .bbn, .mark_b, .mark_g, .mark_r, .app-badge-blue, .app-badge-yellow, .app-badge-black, .app-badge-green, .app-badge-red, span, a, b, strong, i, u, s, strike, del, small';
      // 多重ネストや隣接タグを完全に消すためループ処理（最大3回走査）
      for (let pass = 0; pass < 3; pass++) {
        let passRemoved = 0;
        const elements = editor.querySelectorAll(selector);
        elements.forEach(el => {
          // 内部に img, button, table, input などの重要要素がある場合は削除しない
          if (el.querySelector('img, button, table, input, textarea, svg, iframe')) {
            return;
          }
          // テキスト内容から空白・ゼロ幅スペース・全角スペース・改行を除去
          const text = el.textContent.replace(/[\s\uFEFF\xA0\u200B\u200C\u200D\u3000]/g, '').trim();
          if (text === '') {
            el.remove();
            passRemoved++;
          }
        });
        removedCount += passRemoved;
        if (passRemoved === 0) break;
      }

      // HTMLソース編集モードが開いている場合は同期
      const sourceTextarea = document.getElementById('editor-html-source');
      if (sourceTextarea && sourceTextarea.style.display !== 'none') {
        sourceTextarea.value = editor.innerHTML;
      }

      if (showNotification) {
        if (removedCount > 0) {
          alert(`不要な空枠・空タグを ${removedCount} 個削除しました！`);
        } else {
          alert('不要な空枠・空タグは見つかりませんでした。（エディターは正常な状態です）');
        }
      }

      return removedCount;
    };

    // 互換用エイリアス
    window.removeEmptyBadges = function() {
      cleanAllEmptyTags(false);
    };

    // 取り消し線の追加 (<s> / strikethrough)
    window.formatStrikeThrough = function() {
      restoreSelection();
      execCmd('strikeThrough');
      saveSelection();
    };

    // 取り消し線の解除（<s>, <strike>, <del>, text-decoration: line-through の除去）
    window.removeStrikeThrough = function() {
      restoreSelection();
      const editor = document.getElementById('editor-body');
      if (!editor) return;

      // 1. 標準コマンドで StrikeThrough 解除を試行
      execCmd('strikeThrough');

      // 2. 選択範囲内およびその親要素に含まれる s, strike, del, line-through 要素を確実に除去・展開
      const sel = window.getSelection();
      if (sel && sel.rangeCount > 0) {
        const range = sel.getRangeAt(0);
        let container = range.commonAncestorContainer;
        if (container.nodeType === 3) container = container.parentNode;

        // s, strike, del タグ内の要素を解除
        const strikeNodes = container.querySelectorAll ? container.querySelectorAll('s, strike, del, [style*="line-through"]') : [];
        strikeNodes.forEach(node => {
          if (editor.contains(node)) {
            if (node.style && node.style.textDecoration && node.style.textDecoration.includes('line-through')) {
              node.style.textDecoration = node.style.textDecoration.replace('line-through', '').trim();
              if (!node.style.textDecoration) {
                node.removeAttribute('style');
              }
            } else {
              const parent = node.parentNode;
              while (node.firstChild) {
                parent.insertBefore(node.firstChild, node);
              }
              node.remove();
            }
          }
        });

        // 親要素自体が s, strike, del の場合
        const parentStrike = container.closest('s, strike, del');
        if (parentStrike && editor.contains(parentStrike)) {
          const parentNode = parentStrike.parentNode;
          while (parentStrike.firstChild) {
            parentNode.insertBefore(parentStrike.firstChild, parentStrike);
          }
          parentStrike.remove();
        }
      }
      saveSelection();
    };

    // 選択範囲・カーソル行を固定見出し(h5.kokuban.sticky-top)に変換
    window.formatH5Sticky = function() {
      restoreSelection();
      execCmd('formatBlock', 'h5');
      const editor = document.getElementById('editor-body');
      if (editor) {
        const h5s = editor.querySelectorAll('h5');
        h5s.forEach(h5 => {
          if (!h5.classList.contains('speechBubble') && !h5.classList.contains('mark_b')) {
            h5.classList.add('kokuban', 'sticky-top');
          }
        });
      }
      saveSelection();
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
          range.deleteContents(); // 選択されている部分を削除（置換）
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

    // ツールバーコマンド実行
    window.execCmd = function(command, value = null) {
      if (command === 'insertHTML') {
        insertHTMLAtCursor(value);
        return;
      }
      restoreSelection();
      document.execCommand(command, false, value);
      saveSelection();
    };

    // リンク挿入
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

    // 表（テーブル）挿入
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

    // 丸青ナンバリング挿入
    window.insertCircleNum = function(num) {
      const html = `<span class="circle-num-blue">${escapeHtml(num)}</span>&nbsp;`;
      insertHTMLAtCursor(html);
    };

    // 選択範囲を丸青ナンバリングにする
    window.makeSelectionCircleNum = function() {
      let selectedText = getSelectedTextFromEditor();
      if (!selectedText) {
        selectedText = prompt('丸青アイコンの中に表示する数字または文字を入力してください:', '1');
        if (!selectedText) return;
      }
      const html = `<span class="circle-num-blue">${escapeHtml(selectedText)}</span>&nbsp;`;
      insertHTMLAtCursor(html);
    };

    // 自由入力で丸青ナンバリングを作成
    window.promptCircleNum = function() {
      const num = prompt('青丸の中に入れる数字または文字を入力してください:', '1');
      if (!num) return;
      insertCircleNum(num);
    };

    // バッジ挿入
    window.insertBadge = function(badgeClass, defaultText) {
      const selectedText = getSelectedTextFromEditor();
      const text = selectedText || defaultText;
      const badgeHtml = `<span class="app-badge ${badgeClass}">${escapeHtml(text)}</span>&nbsp;`;
      insertHTMLAtCursor(badgeHtml);
    };

    // 選択テキストのバッジ化
    window.makeSelectionBadge = function(badgeClass) {
      restoreSelection();
      let selectedText = '';
      const sel = window.getSelection();
      if (sel && sel.rangeCount > 0 && !sel.isCollapsed) {
        selectedText = sel.toString().trim();
      }
      if (!selectedText && savedRange) {
        selectedText = savedRange.toString().trim();
      }

      if (!selectedText) {
        selectedText = prompt('バッジ装飾する文字を入力してください:', '迎車');
        if (!selectedText) return;
      }
      const badgeHtml = `<span class="app-badge ${badgeClass}">${escapeHtml(selectedText)}</span>&nbsp;`;
      insertHTMLAtCursor(badgeHtml);
    };

    // カスタムバッジ
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

    // リスト解除・新段落（新しい行）作成
    window.exitListFormat = function() {
      restoreSelection();
      const editor = document.getElementById('editor-body');
      if (!editor) return;

      const sel = window.getSelection();
      let exitedList = false;

      if (sel && sel.rangeCount > 0) {
        const range = sel.getRangeAt(0);
        let node = range.commonAncestorContainer;
        if (node.nodeType === 3) node = node.parentNode;

        const listNode = node.closest('ul, ol');
        if (listNode && editor.contains(listNode)) {
          // リストの直後に新しい空段落を作成
          const p = document.createElement('p');
          p.innerHTML = '<br>';
          if (listNode.nextSibling) {
            listNode.parentNode.insertBefore(p, listNode.nextSibling);
          } else {
            listNode.parentNode.appendChild(p);
          }

          // カーソルを新しい段落に移動
          const newRange = document.createRange();
          newRange.setStart(p, 0);
          newRange.collapse(true);
          sel.removeAllRanges();
          sel.addRange(newRange);
          saveSelection();
          exitedList = true;
        }
      }

      if (!exitedList) {
        insertHTMLAtCursor('<p><br></p>');
      }
    };

    // 写真挿入 (リスト巻き込み防止・安全ブロック配置)
    window.triggerImgUpload = function() {
      document.getElementById('native-img-upload').click();
    };

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
          // リスト内にいる場合はまずリストから脱出
          restoreSelection();
          const sel = window.getSelection();
          if (sel && sel.rangeCount > 0) {
            let node = sel.getRangeAt(0).commonAncestorContainer;
            if (node.nodeType === 3) node = node.parentNode;
            const listNode = node.closest('ul, ol');
            const editor = document.getElementById('editor-body');
            if (listNode && editor && editor.contains(listNode)) {
              const p = document.createElement('p');
              p.innerHTML = '<br>';
              if (listNode.nextSibling) {
                listNode.parentNode.insertBefore(p, listNode.nextSibling);
              } else {
                listNode.parentNode.appendChild(p);
              }
              const newRange = document.createRange();
              newRange.setStart(p, 0);
              newRange.collapse(true);
              sel.removeAllRanges();
              sel.addRange(newRange);
            }
          }

          // 前後に十分な空行を設けて写真を配置
          const imgHtml = `<p><br></p><div class="my-3 text-center"><img src="${escapeHtml(data.url)}" class="img-fluid rounded shadow-sm" alt="挿入画像" style="max-width:100%; height:auto;"></div><p><br></p>`;
          insertHTMLAtCursor(imgHtml);
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
  </script>
</body>
</html>
