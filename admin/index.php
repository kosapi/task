<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$content_data = get_content_data();
$slogans_count = count($content_data['slogans'] ?? []);
$checklist_count = count($content_data['checklist'] ?? []);

// システム情報取得
$system_info = get_system_info();

// CSRFトークン（設定のバックアップPOSTで使用）
$csrf_token = generate_csrf_token();

// リンク数（アコーディオン + モーダルの総数）
$links_count = 0;
$index_html_path = dirname(__DIR__) . '/index.html';
if (file_exists($index_html_path)) {
    $html_content = file_get_contents($index_html_path);
    // アコーディオンタイトル（ボタン）を抽出して数える
    $accordion_titles = [];
    if (preg_match_all('/data-bs-target="#(collapse\d+)"[^>]*>\s*([^<]+)\s*<\/button>/u', $html_content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $accordion_titles[$m[1]] = trim(html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
    }
    $accordion_count = count($accordion_titles);

    // 各collapseセクション内のモーダルリンクを抽出して数える
    $modals_total = 0;
    if (preg_match_all('/<div id="(collapse\d+)" class="accordion-collapse collapse".*?>(.*?)<\/div>\s*<\/div>\s*(?=<div class="accordion-item"|<\/div>\s*<\/div>\s*<\/form>)/su', $html_content, $accordion_matches, PREG_SET_ORDER)) {
        foreach ($accordion_matches as $am) {
            $accordion_content = $am[2];
            if (preg_match_all('/<a[^>]+data-bs-target="[#]*([^"]+)"[^>]*>(?:<p>)?([^<]+)(?:<\/p>)?<\/a>/u', $accordion_content, $modal_matches, PREG_SET_ORDER)) {
                $modals_total += count($modal_matches);
            }
        }
    }

    $links_count = $accordion_count + $modals_total;
}

// 画像数（root, img, uploads を合算）
$images_count = 0;
$allowed_exts = ALLOWED_EXTENSIONS;

// ルート直下
$root_dir = dirname(__DIR__);
$root_files = glob($root_dir . '/*');
foreach ($root_files as $file) {
    if (is_file($file)) {
        $ext = get_file_extension($file);
        if (in_array($ext, $allowed_exts) && !in_array($ext, ['php', 'html', 'css', 'js'])) {
            $images_count++;
        }
    }
}

// uploads フォルダ
$upload_files = glob(UPLOADS_DIR . '/*');
foreach ($upload_files as $file) {
    if (is_file($file)) {
        $ext = get_file_extension($file);
        if (in_array($ext, $allowed_exts)) {
            $images_count++;
        }
    }
}

// img フォルダ
$img_dir = dirname(__DIR__) . '/img';
if (is_dir($img_dir)) {
    $img_files = glob($img_dir . '/*');
    foreach ($img_files as $file) {
        if (is_file($file)) {
            $ext = get_file_extension($file);
            if (in_array($ext, $allowed_exts)) {
                $images_count++;
            }
        }
    }
}

render_admin_header('ダッシュボード');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">ダッシュボード</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/task/" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye me-1"></i>サイトを表示
            </a>
            <a href="/task/debug-accordion-links.html" target="_blank" class="btn btn-sm btn-outline-danger" title="アコーディオンリンク デバッグ">
                <i class="bi bi-bug me-1"></i>デバッグ
            </a>
            <form method="POST" action="/task/admin/settings.php" class="d-inline ms-2">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                <button type="submit" name="create_backup" class="btn btn-sm btn-outline-primary" title="今すぐバックアップ">
                    <i class="bi bi-archive me-1"></i>今すぐバックアップ
                </button>
            </form>
        </div>
    </div>
</div>

<div class="alert alert-success mb-4">
    <h5 class="alert-heading"><i class="bi bi-check2-circle me-2"></i>CMSが正常に動作しています</h5>
    <p class="mb-2">このCMSでメインサイトのコンテンツをリアルタイムで管理できます。</p>
    <hr>
    <p class="mb-0 small">
        <strong>💡 ヒント：</strong> スローガンや画像を編集すると、すぐにメインサイトに反映されます。
        <a href="/task/" target="_blank" class="alert-link">サイトを開いて</a>確認してください。
    </p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">スローガン</h6>
                        <h2 class="mb-0"><?php echo $slogans_count; ?></h2>
                    </div>
                    <i class="bi bi-chat-quote" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="/task/admin/slogans.php" class="stretched-link text-white text-decoration-none">
                    <small>管理 <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">チェックリスト</h6>
                        <h2 class="mb-0"><?php echo $checklist_count; ?></h2>
                    </div>
                    <i class="bi bi-list-check" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="/task/admin/live_editor.php" class="stretched-link text-white text-decoration-none">
                    <small>編集 <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">リンク</h6>
                        <h2 class="mb-0"><?php echo $links_count; ?></h2>
                    </div>
                    <i class="bi bi-link-45deg" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="/task/admin/accordion_links.php" class="stretched-link text-white text-decoration-none">
                    <small>管理 <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">画像</h6>
                        <h2 class="mb-0"><?php echo $images_count; ?></h2>
                    </div>
                    <i class="bi bi-images" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="/task/admin/images.php" class="stretched-link text-white text-decoration-none">
                    <small>管理 <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">お知らせ</h6>
                        <h2 class="mb-0">?</h2>
                    </div>
                    <i class="bi bi-megaphone" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="/task/admin/notices.php" class="stretched-link text-white text-decoration-none">
                    <small>管理 <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ✨ 使い方ガイドセクション -->
<div class="alert alert-light border border-info mt-4 mb-4" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);">
    <div class="d-flex align-items-start">
        <i class="bi bi-lightbulb text-info me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
        <div>
            <h5 class="alert-heading mb-2"><i class="bi bi-book me-1"></i>はじめにお読みください</h5>
            <p class="mb-2">
                チェックリスト、画像、モーダルの追加方法がわかりやすくまとめられています。
            </p>
            <div>
                <a href="/task/admin/guide.html" class="btn btn-sm btn-outline-info" target="_blank">
                    <i class="bi bi-play-circle me-1"></i>ビジュアルガイド
                </a>
                <a href="/task/admin/guide-markdown.html" class="btn btn-sm btn-outline-secondary" target="_blank">
                    <i class="bi bi-file-text me-1"></i>テキスト版
                </a>
            </div>
            <span class="ms-2 badge bg-info">NEW</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">クイックアクション</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="/task/admin/slogans.php" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-chat-quote text-primary me-2"></i>
                                <strong>スローガンを編集</strong>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                    <a href="/task/admin/accordion_links.php" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-link-45deg text-warning me-2"></i>
                                <strong>アコーディオンリンクを管理</strong>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                    <a href="/task/admin/images.php" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-upload text-info me-2"></i>
                                <strong>画像をアップロード</strong>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                    <a href="/task/admin/live_editor.php" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-pencil-square text-secondary me-2"></i>
                                <strong>ライブエディタで編集</strong>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                    <a href="/task/admin/notices.php" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-megaphone text-dark me-2"></i>
                                <strong>お知らせを管理</strong>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">システム情報</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-person text-muted me-2"></i>
                        <small>ユーザー: <?php echo h($_SESSION['username']); ?></small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock text-muted me-2"></i>
                        <small>ログイン: <?php echo date('Y/m/d H:i', $_SESSION['login_time']); ?></small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-server text-muted me-2"></i>
                        <small>PHP: <?php echo phpversion(); ?></small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-file-earmark-text text-muted me-2"></i>
                        <small>JSON容量: <?php echo h($system_info['content_size_formatted']); ?></small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-pencil-square text-muted me-2"></i>
                        <small>最終更新: <?php echo h($system_info['content_modified_formatted']); ?></small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-archive text-muted me-2"></i>
                        <small>バックアップ: <?php echo $system_info['backup_count']; ?>個</small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock-history text-muted me-2"></i>
                        <small>最新BK: <?php echo h($system_info['latest_backup_formatted']); ?></small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>
