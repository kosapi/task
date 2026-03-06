<?php
/**
 * スローガン保存デバッグスクリプト
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$debug_info = [];

// テストスローガンを作成
$test_slogans = [
    "テスト1 日曜日",
    "テスト2 月曜日",
    "テスト3 火曜日",
    "テスト4 水曜日",
    "テスト5 木曜日",
    "テスト6 金曜日",
    "テスト7 土曜日"
];

// 1. JSONファイルに保存
$content_data = ['slogans' => $test_slogans, 'checklist' => []];
$debug_info['save_json'] = save_content_data($content_data) ? 'OK' : 'FAIL';

// 2. 保存後のJSONを確認
$check_json = get_content_data();
$debug_info['json_saved'] = ($check_json['slogans'][0] === $test_slogans[0]) ? 'OK' : 'FAIL';
$debug_info['json_content'] = $check_json['slogans'][0] ?? 'EMPTY';

// 3. index.htmlの現在の内容を確認
$index_file = dirname(__DIR__) . '/index.html';
$html_before = file_get_contents($index_file);
$debug_info['index_html_exists'] = file_exists($index_file) ? 'OK' : 'FAIL';
$debug_info['index_html_readable'] = is_readable($index_file) ? 'OK' : 'FAIL';
$debug_info['index_html_writable'] = is_writable($index_file) ? 'OK' : 'FAIL';

// 4. index.htmlの更新を試みる
$backup_dir = dirname(__DIR__) . '/data/backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}
$backup_file = $backup_dir . '/index_debug_' . date('Y-m-d_H-i-s') . '.html';
$debug_info['backup_created'] = copy($index_file, $backup_file) ? 'OK' : 'FAIL';

// 5. slogans-dataスクリプトの更新テスト
$slogans_json = json_encode($test_slogans, JSON_UNESCAPED_UNICODE);
$html_updated = preg_replace(
    '/(<script[^>]*id=["\']slogans-data["\'][^>]*>)(.*?)(<\/script>)/isu',
    '$1' . $slogans_json . '$3',
    $html_before,
    1,
    $json_replace_count
);
$debug_info['json_script_replaced'] = $json_replace_count;

// 6. 曜日ブロックの更新テスト
$weekday_ids = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$total_day_replacements = 0;
foreach ($weekday_ids as $index => $weekday_id) {
    $escaped_slogan = htmlspecialchars($test_slogans[$index], ENT_QUOTES, 'UTF-8');
    $pattern = '/(<div\\b[^>]*\\bid=["\']' . preg_quote($weekday_id, '/') . '["\'][^>]*>.*?<p\\b[^>]*class=["\'][^"\']*\\btext-center\\b[^"\']*["\'][^>]*>)(.*?)(<\\/p>)/isu';
    $html_updated = preg_replace(
        $pattern,
        '$1' . $escaped_slogan . '$3',
        $html_updated,
        1,
        $day_replace_count
    );
    $debug_info['day_replaced_' . $weekday_id] = $day_replace_count;
    $total_day_replacements += $day_replace_count;
}
$debug_info['total_day_replacements'] = $total_day_replacements;

// 7. 実際に書き込み
$temp_file = $index_file . '.tmp.debug';
$write_result = file_put_contents($temp_file, $html_updated);
$debug_info['temp_write'] = ($write_result !== false) ? 'OK' : 'FAIL';
$debug_info['temp_write_bytes'] = $write_result;

// 8. 一時ファイルが存在するか確認
if (file_exists($temp_file)) {
    $debug_info['temp_file_exists'] = 'OK';
    $temp_content = file_get_contents($temp_file);
    $debug_info['temp_contains_test'] = strpos($temp_content, 'テスト1') !== false ? 'OK' : 'FAIL';
}

// 9. rename実行
if (file_exists($temp_file)) {
    $rename_result = @rename($temp_file, $index_file);
    $debug_info['rename_result'] = $rename_result ? 'OK' : 'FAIL';
}

// 10. 書き込み後の確認
$html_after = file_get_contents($index_file);
$debug_info['html_contains_test'] = strpos($html_after, 'テスト1') !== false ? 'OK' : 'FAIL';

render_admin_header('スローガン保存デバッグ');
?>

<div class="container mt-5">
    <h2>スローガン保存デバッグ結果</h2>
    
    <table class="table table-striped">
        <tbody>
            <?php foreach ($debug_info as $key => $value): ?>
                <tr>
                    <td><strong><?php echo h($key); ?></strong></td>
                    <td>
                        <?php 
                        if (is_bool($value)) {
                            echo $value ? '✓ TRUE' : '✗ FALSE';
                        } else {
                            echo h((string)$value);
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-4">
        <a href="/task/admin/slogans.php" class="btn btn-primary">スローガン管理に戻る</a>
        <a href="/task/admin/index.php" class="btn btn-outline-secondary">ダッシュボードに戻る</a>
    </div>
</div>

<?php render_admin_footer(); ?>
