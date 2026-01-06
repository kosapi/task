<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$content_data = get_content_data();
$slogans_count = count($content_data['slogans'] ?? []);
$checklist_count = count($content_data['checklist'] ?? []);

// アップロード画像数
$image_files = glob(UPLOADS_DIR . '/*');
$images_count = count($image_files);

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
                        <h2 class="mb-0"><?php echo $checklist_count; ?></h2>
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
                </ul>
            </div>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>
