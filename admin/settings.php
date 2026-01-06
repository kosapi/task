<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$success_message = '';
$error_message = '';

// バックアップ一覧を取得
$backup_dir = DATA_DIR . '/backups';
$backups = [];
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '/content_*.json');
    foreach ($files as $file) {
        $backups[] = [
            'path' => $file,
            'name' => basename($file),
            'size' => filesize($file),
            'modified' => filemtime($file)
        ];
    }
    usort($backups, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

// バックアップ作成
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error_message = 'セキュリティエラーが発生しました。';
    } else {
        if (create_backup()) {
            $success_message = 'バックアップを作成しました。';
            header('Location: /task/admin/settings.php?backup=1');
            exit;
        } else {
            $error_message = 'バックアップの作成に失敗しました。';
        }
    }
}

// パスワード変更（将来実装用のプレースホルダー）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $error_message = 'パスワード変更機能は config.php を直接編集して実装してください。';
}

if (isset($_GET['backup'])) {
    $success_message = 'バックアップを作成しました。';
}

$csrf_token = generate_csrf_token();

render_admin_header('設定');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">設定</h1>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo h($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo h($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>セキュリティ</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>管理者アカウント</h6>
                    <p class="text-muted small">ユーザー名: <?php echo h(ADMIN_USERNAME); ?></p>
                    <p class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        パスワードを変更するには、<code>config.php</code> ファイルの <code>ADMIN_PASSWORD</code> を編集してください。
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6>セッション設定</h6>
                    <p class="text-muted small">セッション有効期間: <?php echo SESSION_LIFETIME / 60; ?>分</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>アップロード設定</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>最大ファイルサイズ</h6>
                    <p class="text-muted small"><?php echo MAX_UPLOAD_SIZE / 1024 / 1024; ?>MB</p>
                </div>
                
                <div class="mb-3">
                    <h6>許可されるファイル形式</h6>
                    <p class="text-muted small"><?php echo implode(', ', array_map('strtoupper', ALLOWED_EXTENSIONS)); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-archive me-2"></i>バックアップ管理</h5>
        <form method="POST" action="" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
            <button type="submit" name="create_backup" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>バックアップを作成
            </button>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($backups)): ?>
            <p class="text-muted text-center py-4">
                バックアップがありません。
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ファイル名</th>
                            <th>サイズ</th>
                            <th>作成日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><code><?php echo h($backup['name']); ?></code></td>
                                <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                <td><?php echo date('Y/m/d H:i:s', $backup['modified']); ?></td>
                                <td>
                                    <a href="/data/backups/<?php echo h($backup['name']); ?>" download class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i>ダウンロード
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>システム情報</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">PHP バージョン</dt>
                    <dd class="col-sm-8"><?php echo phpversion(); ?></dd>
                    
                    <dt class="col-sm-4">データディレクトリ</dt>
                    <dd class="col-sm-8"><code><?php echo h(DATA_DIR); ?></code></dd>
                    
                    <dt class="col-sm-4">アップロードディレクトリ</dt>
                    <dd class="col-sm-8"><code><?php echo h(UPLOADS_DIR); ?></code></dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">タイムゾーン</dt>
                    <dd class="col-sm-8"><?php echo date_default_timezone_get(); ?></dd>
                    
                    <dt class="col-sm-4">現在時刻</dt>
                    <dd class="col-sm-8"><?php echo date('Y/m/d H:i:s'); ?></dd>
                    
                    <dt class="col-sm-4">セッション名</dt>
                    <dd class="col-sm-8"><?php echo SESSION_NAME; ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>
