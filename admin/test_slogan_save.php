<?php
/**
 * スローガン保存テスト
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$test_result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_save'])) {
    $test_slogans = [
        '【テスト1】日曜日のテスト',
        '【テスト2】月曜日のテスト',
        '【テスト3】火曜日のテスト',
        '【テスト4】水曜日のテスト',
        '【テスト5】木曜日のテスト',
        '【テスト6】金曜日のテスト',
        '【テスト7】土曜日のテスト'
    ];
    
    $content_data = ['slogans' => $test_slogans, 'checklist' => []];
    
    if (save_content_data($content_data)) {
        $test_result = '✓ JSON に保存しました';
    } else {
        $test_result = '✗ JSON 保存に失敗しました';
    }
}

$current_slogans = get_content_data()['slogans'] ?? [];

render_admin_header('スローガンテスト保存');
?>

<div class="container mt-5">
    <h2>スローガン保存テスト</h2>
    
    <?php if ($test_result): ?>
        <div class="alert alert-info">
            <h4><?php echo $test_result; ?></h4>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">テスト実行</h5>
        </div>
        <div class="card-body">
            <p>下のボタンをクリックして、テストスローガンを JSON に保存してください。</p>
            <form method="POST">
                <button type="submit" name="test_save" class="btn btn-danger btn-lg">
                    テストスローガン（【テスト1】〜【テスト7】）を保存
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">現在の JSON データ（content.json）</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <tbody>
                    <?php foreach ($current_slogans as $i => $slogan): ?>
                        <tr>
                            <td><strong><?php echo ['日', '月', '火', '水', '木', '金', '土'][$i]; ?></strong></td>
                            <td><code><?php echo h($slogan); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-3">
                <p><small class="text-muted">上のテストボタンを押すと、ここの内容が【テスト1】などに変わります。</small></p>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">API レスポンス（/task/api/get-content.php）</h5>
        </div>
        <div class="card-body">
            <p><a href="/task/api/get-content.php" target="_blank" class="btn btn-sm btn-outline-primary">API にアクセス</a></p>
            <small class="text-muted">新しいタブで API にアクセスして、最新の JSON が返されているか確認してください。</small>
        </div>
    </div>

    <div class="mt-4">
        <a href="/task/admin/slogans.php" class="btn btn-primary">スローガン管理に戻る</a>
        <a href="/task/admin/index.php" class="btn btn-outline-secondary">ダッシュボード</a>
    </div>
</div>

<?php render_admin_footer(); ?>
