<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$content_data = get_content_data();
$slogans = $content_data['slogans'] ?? [];

// スローガンが7つ未満の場合は空文字で埋める
while (count($slogans) < 7) {
    $slogans[] = '';
}

// 7つを超える場合は切り詰める
$slogans = array_slice($slogans, 0, 7);

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error_message = 'セキュリティエラーが発生しました。';
    } else {
        // スローガンを7つ固定で取得
        $new_slogans = [];
        if (isset($_POST['slogans']) && is_array($_POST['slogans'])) {
            for ($i = 0; $i < 7; $i++) {
                $slogan = isset($_POST['slogans'][$i]) ? trim($_POST['slogans'][$i]) : '';
                $new_slogans[] = $slogan;
            }
        }
        
        // バックアップを作成
        create_backup();
        
        // データを保存
        $content_data['slogans'] = $new_slogans;
        if (save_content_data($content_data)) {
            $success_message = 'スローガンを保存しました。';
            $slogans = $new_slogans;
        } else {
            $error_message = '保存に失敗しました。';
        }
    }
}

// 曜日ラベル
$weekdays = ['日', '月', '火', '水', '木', '金', '土'];

$csrf_token = generate_csrf_token();

render_admin_header('スローガン管理');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">スローガン管理</h1>
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

<div class="alert alert-info">
    <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>使い方</h5>
    <p class="mb-2">メインサイトの「本日の安全標語」に表示されるスローガンを管理できます。</p>
    <ul class="mb-0">
        <li>各曜日ごとに異なるスローガンを設定できます（日〜土の7つ）</li>
        <li>編集後「保存」をクリックすると、すぐにメインサイトに反映されます</li>
        <li>スローガンは自動でバックアップされます</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">曜日別スローガン</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="slogansForm">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
            
            <div id="slogansContainer">
                <?php foreach ($slogans as $index => $slogan): ?>
                    <div class="slogan-item mb-3">
                        <div class="input-group">
                            <span class="input-group-text" style="min-width: 50px;">
                                <?php echo h($weekdays[$index]); ?>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="slogans[]" 
                                   value="<?php echo h($slogan); ?>" 
                                   placeholder="<?php echo h($weekdays[$index]); ?>曜日のスローガンを入力"
                                   required>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save me-2"></i>保存
                </button>
                <a href="/task/admin/index.php" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>戻る
                </a>
            </div>
        </form>
    </div>
</div>

<?php render_admin_footer(); ?>
