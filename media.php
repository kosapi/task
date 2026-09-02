<?php
session_start();

// キャッシュ無効化
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 管理画面パスワード設定（admin.phpと共通）
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
  <title>メディア一覧・画像管理 - ワークチェックリスト</title>

  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #114400;
      --primary-hover: #0c3300;
      --accent-color: #2563eb;
      --bg-color: #f8fafc;
      --card-bg: #ffffff;
      --text-main: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
    }

    body {
      background-color: var(--bg-color);
      font-family: 'Plus Jakarta Sans', 'Kosugi Maru', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      color: var(--text-main);
      min-height: 100vh;
      padding-bottom: 60px;
    }

    .header-bar {
      background-color: var(--primary-color) !important;
      box-shadow: 0 4px 12px rgba(17, 68, 0, 0.15);
    }

    .stat-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 16px 20px;
      border: 1px solid var(--border-color);
      box-shadow: 0 2px 6px rgba(0,0,0,0.03);
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .upload-zone {
      background: #ffffff;
      border: 2px dashed #94a3b8;
      border-radius: 16px;
      padding: 30px 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.25s ease;
    }
    .upload-zone:hover, .upload-zone.dragover {
      border-color: #2563eb;
      background-color: #eff6ff;
    }

    .media-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
      position: relative;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    .media-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
      border-color: #cbd5e1;
    }

    .media-thumb-wrapper {
      position: relative;
      width: 100%;
      height: 180px;
      background: #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      cursor: pointer;
    }
    .media-thumb {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      transition: transform 0.3s;
    }
    .media-thumb-wrapper:hover .media-thumb {
      transform: scale(1.05);
    }

    .media-badge {
      position: absolute;
      top: 8px;
      left: 8px;
      z-index: 2;
      font-size: 11px;
      font-weight: 700;
      padding: 4px 8px;
      border-radius: 6px;
      backdrop-filter: blur(4px);
    }
    .media-badge.used {
      background: rgba(22, 101, 52, 0.9);
      color: #fff;
    }
    .media-badge.unused {
      background: rgba(100, 116, 139, 0.85);
      color: #fff;
    }

    .media-info {
      padding: 12px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .media-name {
      font-size: 13.5px;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 4px;
      word-break: break-all;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .media-meta {
      font-size: 11.5px;
      color: var(--text-muted);
      margin-bottom: 8px;
    }
    .media-usage-tags {
      font-size: 11px;
      max-height: 48px;
      overflow-y: auto;
      margin-bottom: 10px;
    }
    .usage-pill {
      display: inline-block;
      background: #e0f2fe;
      color: #0369a1;
      border-radius: 4px;
      padding: 2px 6px;
      margin: 2px 2px 2px 0;
      white-space: nowrap;
      font-size: 10.5px;
    }

    .media-actions {
      border-top: 1px solid var(--border-color);
      padding: 8px 12px;
      background: #fafafa;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 6px;
    }

    .toast-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 2000;
    }
  </style>
</head>
<body>

<?php if (!$is_logged_in): ?>
  <!-- ログイン画面 -->
  <div class="container" style="max-width: 420px; margin-top: 100px;">
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header text-white text-center py-4 rounded-top-4" style="background-color: var(--primary-color);">
        <h4 class="mb-0 fw-bold"><i class="bi bi-images me-2"></i>メディア管理 ログイン</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($error_msg): ?>
          <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        <form method="POST" action="media.php">
          <div class="mb-3">
            <label class="form-label fw-bold">管理者パスワード</label>
            <input type="password" name="password" class="form-control form-control-lg" placeholder="パスワードを入力" required autofocus>
          </div>
          <button type="submit" class="btn btn-success btn-lg w-100 fw-bold" style="background-color: var(--primary-color); border:none;">ログイン</button>
          <div class="text-center mt-3">
            <a href="admin.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> チェックリスト管理画面へ戻る</a>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php else: ?>

  <!-- 管理者ヘッダーバー -->
  <header class="header-bar text-white py-2 px-3 sticky-top mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">
        <a href="admin.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
          <i class="bi bi-arrow-left me-1"></i> 管理画面へ戻る
        </a>
        <h1 class="h5 mb-0 fw-bold d-flex align-items-center gap-2">
          <i class="bi bi-images"></i> メディア・画像管理ライブラリ
        </h1>
      </div>
      <div class="d-flex align-items-center gap-2">
        <a href="index.html" target="_blank" class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-success">
          <i class="bi bi-box-arrow-up-right me-1"></i> 本番画面
        </a>
        <a href="media.php?action=logout" class="btn btn-outline-light btn-sm rounded-pill px-3">
          <i class="bi bi-box-arrow-right me-1"></i> ログアウト
        </a>
      </div>
    </div>
  </header>

  <main class="container-fluid px-4">
    <!-- 統計＆アップロードゾーン -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-sm-6">
        <div class="stat-card">
          <div class="stat-icon bg-primary bg-opacity-10 text-primary">
            <i class="bi bi-image"></i>
          </div>
          <div>
            <div class="text-muted small">総画像数</div>
            <div class="h4 mb-0 fw-bold" id="stat-total-count">-</div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="stat-card">
          <div class="stat-icon bg-success bg-opacity-10 text-success">
            <i class="bi bi-check2-circle"></i>
          </div>
          <div>
            <div class="text-muted small">項目内で使用中</div>
            <div class="h4 mb-0 fw-bold" id="stat-used-count">-</div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="stat-card">
          <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
            <i class="bi bi-slash-circle"></i>
          </div>
          <div>
            <div class="text-muted small">未使用の画像</div>
            <div class="h4 mb-0 fw-bold" id="stat-unused-count">-</div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="stat-card">
          <div class="stat-icon bg-warning bg-opacity-10 text-warning">
            <i class="bi bi-hdd-stack"></i>
          </div>
          <div>
            <div class="text-muted small">総容量</div>
            <div class="h4 mb-0 fw-bold" id="stat-total-size">-</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ドラッグ＆ドロップ 新規アップロードエリア -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body p-3">
        <div class="upload-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
          <input type="file" id="file-input" multiple accept="image/*" style="display: none;" onchange="handleFileSelect(this.files)">
          <i class="bi bi-cloud-arrow-up text-primary display-5 d-block mb-2"></i>
          <h5 class="fw-bold mb-1">新しい画像をアップロード</h5>
          <p class="text-muted small mb-0">ここに画像をドラッグ＆ドロップ、またはクリックしてファイルを選択（JPG, PNG, GIF, WEBP対応 / 複数選択可）</p>
        </div>
      </div>
    </div>

    <!-- 検索・フィルターツールバー -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body p-3 d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <!-- フィルターボタン -->
        <div class="btn-group" role="group">
          <button type="button" class="btn btn-outline-secondary active" id="filter-all" onclick="setFilter('all')">
            すべて (<span id="filter-count-all">0</span>)
          </button>
          <button type="button" class="btn btn-outline-success" id="filter-used" onclick="setFilter('used')">
            <i class="bi bi-check-circle me-1"></i> 使用中のみ (<span id="filter-count-used">0</span>)
          </button>
          <button type="button" class="btn btn-outline-secondary" id="filter-unused" onclick="setFilter('unused')">
            <i class="bi bi-slash-circle me-1"></i> 未使用のみ (<span id="filter-count-unused">0</span>)
          </button>
        </div>

        <!-- 検索バー -->
        <div class="input-group" style="max-width: 360px;">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" id="search-input" class="form-control bg-light border-start-0" placeholder="ファイル名や項目名で検索..." oninput="handleSearch()">
          <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('search-input').value=''; handleSearch();">
            <i class="bi bi-x"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- 画像一覧グリッド -->
    <div class="row g-3" id="media-grid">
      <!-- JavaScriptで動的生成 -->
      <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="text-muted mt-2">画像を読み込み中...</div>
      </div>
    </div>
  </main>

  <!-- 画像詳細・拡大プレビューモーダル -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header bg-dark text-white py-3">
          <h5 class="modal-title fw-bold" id="previewModalTitle">画像詳細</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-4">
            <div class="col-md-7 text-center bg-light rounded-3 p-3 d-flex align-items-center justify-content-center" style="min-height: 300px;">
              <img id="previewModalImg" src="" alt="" class="img-fluid rounded shadow-sm" style="max-height: 450px;">
            </div>
            <div class="col-md-5 d-flex flex-direction-column justify-content-between">
              <div>
                <h6 class="fw-bold text-muted mb-1">ファイル名</h6>
                <p class="fw-bold text-break mb-3" id="previewModalFilename">-</p>

                <h6 class="fw-bold text-muted mb-1">サイズ・解像度</h6>
                <p class="mb-3" id="previewModalDimensions">-</p>

                <h6 class="fw-bold text-muted mb-1">更新日時</h6>
                <p class="mb-3" id="previewModalDate">-</p>

                <h6 class="fw-bold text-muted mb-1">使用箇所</h6>
                <div id="previewModalUsages" class="mb-3">
                  <span class="text-muted small">未使用</span>
                </div>
              </div>

              <div class="d-flex flex-column gap-2 pt-3 border-top">
                <button class="btn btn-outline-primary w-100 rounded-pill" onclick="copyImageUrl(currentPreviewImage?.url)">
                  <i class="bi bi-clipboard me-1"></i> 画像URLをコピー
                </button>
                <button class="btn btn-warning w-100 rounded-pill text-dark fw-bold" onclick="openReplaceModal(currentPreviewImage?.name)">
                  <i class="bi bi-arrow-repeat me-1"></i> この画像を差し替え（上書き）
                </button>
                <button class="btn btn-outline-danger w-100 rounded-pill" onclick="deleteImage(currentPreviewImage?.name)">
                  <i class="bi bi-trash me-1"></i> 画像を削除
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 画像差し替え（上書きアップロード）モーダル -->
  <div class="modal fade" id="replaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header bg-warning text-dark py-3">
          <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>画像の差し替え（上書き）</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <p class="small text-muted mb-3">
            高画質化した画像や修正後の画像を選択してください。ファイル名を維持したまま上書きされ、チェックリスト側の表示も自動で更新されます。
          </p>
          <div class="alert alert-secondary py-2 small mb-3">
            対象ファイル: <strong id="replaceTargetFilename" class="text-primary">-</strong>
          </div>
          <input type="file" id="replaceFileInput" accept="image/*" class="form-control form-control-lg mb-3">
          <div id="replacePreviewContainer" class="text-center d-none mb-3">
            <div class="small text-muted mb-1">差し替えプレビュー:</div>
            <img id="replacePreviewImg" src="" style="max-height: 180px;" class="img-fluid rounded border">
          </div>
          <button type="button" class="btn btn-warning w-100 py-2 fw-bold text-dark rounded-pill" id="replaceSubmitBtn" onclick="submitReplace()">
            上書き差し替えを実行
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- トースト通知 -->
  <div class="toast-container">
    <div id="liveToast" class="toast align-items-center text-white bg-dark border-0 rounded-3 shadow" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">完了しました</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let allImages = [];
    let currentFilter = 'all';
    let currentPreviewImage = null;
    let targetReplaceFilename = '';

    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const replaceModal = new bootstrap.Modal(document.getElementById('replaceModal'));
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });

    function showToast(msg, isSuccess = true) {
      document.getElementById('toastMessage').textContent = msg;
      toastEl.className = `toast align-items-center text-white border-0 rounded-3 shadow ${isSuccess ? 'bg-success' : 'bg-danger'}`;
      toast.show();
    }

    // 1. 画像一覧をAPIから読み込み
    function loadImages() {
      fetch('api/media_manager.php?action=list')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            allImages = data.images;
            updateStats(data);
            renderGrid();
          } else {
            showToast(data.message || '読み込みに失敗しました', false);
          }
        })
        .catch(err => {
          console.error(err);
          showToast('通信エラーが発生しました', false);
        });
    }

    // 統計の更新
    function updateStats(data) {
      const used = allImages.filter(img => img.usedCount > 0).length;
      const unused = allImages.length - used;
      
      document.getElementById('stat-total-count').textContent = allImages.length + ' 枚';
      document.getElementById('stat-used-count').textContent = used + ' 枚';
      document.getElementById('stat-unused-count').textContent = unused + ' 枚';
      document.getElementById('stat-total-size').textContent = (data.totalSize > 1048576 ? (data.totalSize / 1048576).toFixed(1) + ' MB' : (data.totalSize / 1024).toFixed(0) + ' KB');

      document.getElementById('filter-count-all').textContent = allImages.length;
      document.getElementById('filter-count-used').textContent = used;
      document.getElementById('filter-count-unused').textContent = unused;
    }

    // フィルターの切り替え
    function setFilter(filter) {
      currentFilter = filter;
      document.getElementById('filter-all').className = `btn btn-outline-secondary ${filter === 'all' ? 'active' : ''}`;
      document.getElementById('filter-used').className = `btn btn-outline-success ${filter === 'used' ? 'active' : ''}`;
      document.getElementById('filter-unused').className = `btn btn-outline-secondary ${filter === 'unused' ? 'active' : ''}`;
      renderGrid();
    }

    // 検索処理
    function handleSearch() {
      renderGrid();
    }

    // グリッド描画
    function renderGrid() {
      const query = document.getElementById('search-input').value.trim().toLowerCase();
      const grid = document.getElementById('media-grid');

      let filtered = allImages.filter(img => {
        // フィルター判定
        if (currentFilter === 'used' && img.usedCount === 0) return false;
        if (currentFilter === 'unused' && img.usedCount > 0) return false;

        // 検索判定
        if (query) {
          const matchName = img.name.toLowerCase().includes(query);
          const matchUsage = img.usedIn.some(u => u.label.toLowerCase().includes(query) || u.category.toLowerCase().includes(query));
          return matchName || matchUsage;
        }
        return true;
      });

      if (filtered.length === 0) {
        grid.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-image text-muted display-4 d-block mb-3"></i>
            <h6 class="text-muted fw-bold">該当する画像が見つかりませんでした</h6>
          </div>
        `;
        return;
      }

      grid.innerHTML = filtered.map(img => {
        const isUsed = img.usedCount > 0;
        const usageBadges = img.usedIn.slice(0, 2).map(u => `<span class="usage-pill text-truncate" style="max-width: 130px;" title="${escapeHtml(u.category + ': ' + u.label)}">${escapeHtml(u.label)}</span>`).join('');
        const moreUsage = img.usedCount > 2 ? `<span class="usage-pill">+${img.usedCount - 2}</span>` : '';

        return `
          <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="media-card">
              <div class="media-thumb-wrapper" onclick="openPreview('${escapeHtml(img.name)}')">
                <span class="media-badge ${isUsed ? 'used' : 'unused'}">
                  ${isUsed ? '<i class="bi bi-check-circle-fill me-1"></i>使用中 (' + img.usedCount + ')' : '<i class="bi bi-slash-circle me-1"></i>未使用'}
                </span>
                <img src="${img.url}" alt="${escapeHtml(img.name)}" class="media-thumb" loading="lazy">
              </div>
              <div class="media-info">
                <div class="media-name" title="${escapeHtml(img.name)}">${escapeHtml(img.name)}</div>
                <div class="media-meta">${img.width > 0 ? img.width + 'x' + img.height + ' px · ' : ''}${img.sizeFormatted}</div>
                <div class="media-usage-tags">
                  ${isUsed ? usageBadges + moreUsage : '<span class="text-muted" style="font-size: 11px;">項目で使用されていません</span>'}
                </div>
              </div>
              <div class="media-actions">
                <button class="btn btn-light btn-sm rounded-circle p-1" title="詳細・プレビュー" onclick="openPreview('${escapeHtml(img.name)}')">
                  <i class="bi bi-arrows-fullscreen text-secondary" style="font-size: 14px;"></i>
                </button>
                <button class="btn btn-light btn-sm rounded-circle p-1" title="画像を差し替え（上書き）" onclick="openReplaceModal('${escapeHtml(img.name)}')">
                  <i class="bi bi-arrow-repeat text-warning" style="font-size: 14px;"></i>
                </button>
                <button class="btn btn-light btn-sm rounded-circle p-1" title="URLをコピー" onclick="copyImageUrl('${img.url}')">
                  <i class="bi bi-clipboard text-primary" style="font-size: 14px;"></i>
                </button>
                <button class="btn btn-light btn-sm rounded-circle p-1" title="削除" onclick="deleteImage('${escapeHtml(img.name)}')">
                  <i class="bi bi-trash text-danger" style="font-size: 14px;"></i>
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    // プレビューモーダルを開く
    function openPreview(name) {
      const img = allImages.find(i => i.name === name);
      if (!img) return;
      currentPreviewImage = img;

      document.getElementById('previewModalTitle').textContent = img.name;
      document.getElementById('previewModalFilename').textContent = img.name;
      document.getElementById('previewModalImg').src = img.url;
      document.getElementById('previewModalDimensions').textContent = `${img.width > 0 ? img.width + ' × ' + img.height + ' px · ' : ''}${img.sizeFormatted}`;
      document.getElementById('previewModalDate').textContent = img.dateFormatted;

      const usageContainer = document.getElementById('previewModalUsages');
      if (img.usedCount > 0) {
        usageContainer.innerHTML = img.usedIn.map(u => `
          <div class="p-2 mb-1 rounded bg-light border d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-secondary me-1">${escapeHtml(u.category)}</span>
              <strong class="small">${escapeHtml(u.label)}</strong>
            </div>
            ${u.itemId ? `<a href="edit.php?item=${encodeURIComponent(u.itemId)}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 12px;">編集</a>` : ''}
          </div>
        `).join('');
      } else {
        usageContainer.innerHTML = '<span class="badge bg-secondary">チェックリスト項目内で未使用</span>';
      }

      previewModal.show();
    }

    // 画像URLのコピー
    function copyImageUrl(url) {
      if (!url) return;
      const fullUrl = location.origin + (url.startsWith('/') ? url : '/task/' + url);
      navigator.clipboard.writeText(fullUrl).then(() => {
        showToast('画像URLをクリップボードにコピーしました！');
      }).catch(() => {
        showToast('コピーに失敗しました', false);
      });
    }

    // ドラッグ＆ドロップ アップロード処理
    const dropZone = document.getElementById('drop-zone');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, preventDefaults, false);
      document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
    });

    dropZone.addEventListener('drop', e => {
      const files = e.dataTransfer.files;
      handleFileSelect(files);
    });

    function handleFileSelect(files) {
      if (!files || files.length === 0) return;

      const uploadPromises = Array.from(files).map(file => {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('action', 'upload');

        return fetch('api/media_manager.php?action=upload', {
          method: 'POST',
          body: formData
        }).then(res => res.json());
      });

      Promise.all(uploadPromises).then(results => {
        const successCount = results.filter(r => r.success).length;
        if (successCount > 0) {
          showToast(`${successCount}枚の画像をアップロードしました！`);
          loadImages();
        } else {
          showToast('アップロードに失敗しました', false);
        }
      }).catch(err => {
        console.error(err);
        showToast('アップロード中にエラーが発生しました', false);
      });
    }

    // 差し替えモーダルを開く
    function openReplaceModal(name) {
      previewModal.hide();
      targetReplaceFilename = name;
      document.getElementById('replaceTargetFilename').textContent = name;
      document.getElementById('replaceFileInput').value = '';
      document.getElementById('replacePreviewContainer').classList.add('d-none');
      replaceModal.show();
    }

    document.getElementById('replaceFileInput').addEventListener('change', function(e) {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          document.getElementById('replacePreviewImg').src = evt.target.result;
          document.getElementById('replacePreviewContainer').classList.remove('d-none');
        };
        reader.readAsDataURL(this.files[0]);
      }
    });

    // 差し替え実行
    function submitReplace() {
      const fileInput = document.getElementById('replaceFileInput');
      if (!fileInput.files || !fileInput.files[0]) {
        alert('差し替え用の画像ファイルを選択してください');
        return;
      }

      const formData = new FormData();
      formData.append('image', fileInput.files[0]);
      formData.append('target_filename', targetReplaceFilename);
      formData.append('action', 'replace');

      const submitBtn = document.getElementById('replaceSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = '差し替え中...';

      fetch('api/media_manager.php?action=replace', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = '上書き差し替えを実行';
        if (data.success) {
          replaceModal.hide();
          showToast(`画像「${targetReplaceFilename}」を差し替えました！`);
          loadImages();
        } else {
          alert('エラー: ' + data.message);
        }
      })
      .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = '上書き差し替えを実行';
        console.error(err);
        alert('通信エラーが発生しました');
      });
    }

    // 画像削除
    function deleteImage(name) {
      const img = allImages.find(i => i.name === name);
      const isUsed = img && img.usedCount > 0;

      let confirmMsg = `画像「${name}」を削除してもよろしいですか？`;
      if (isUsed) {
        confirmMsg = `⚠️ 警告: この画像は ${img.usedCount} 箇所のチェック項目で使用されています！\n削除すると項目内で画像が表示されなくなります。\n\n本当に削除しますか？`;
      }

      if (!confirm(confirmMsg)) return;

      const formData = new FormData();
      formData.append('filename', name);
      formData.append('action', 'delete');

      fetch('api/media_manager.php?action=delete', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          previewModal.hide();
          showToast(`画像「${name}」を削除しました`);
          loadImages();
        } else {
          alert('削除エラー: ' + data.message);
        }
      })
      .catch(err => {
        console.error(err);
        alert('通信エラーが発生しました');
      });
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // 初期化実行
    document.addEventListener('DOMContentLoaded', loadImages);
  </script>
<?php endif; ?>

</body>
</html>
