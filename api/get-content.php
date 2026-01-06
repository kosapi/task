<?php
/**
 * コンテンツデータAPI
 * CMSで編集したデータをフロントエンドに提供
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// 新しい構造のファイルを優先的に読み込む
$content_file = DATA_DIR . '/content_new_structure.json';
if (file_exists($content_file)) {
    $content_data = read_json_file($content_file);
} else {
    // 従来のcontent.jsonを読み込む
    $content_data = get_content_data();
}

// データが空の場合はデフォルト値を返す
if (empty($content_data)) {
    $content_data = [
        'slogans' => [
            '横臥者を 早期発見 ハイビーム',
            '来ないだろう 決めつけないで 目で確認',
            '発進時 シートベルトの お声がけ',
            '自転車・二輪車の 急な飛び出し 死角から',
            '降りて見る そのひと手間が 防ぐ事故',
            '誤発進 防ぐ こまめな Pレンジ',
            '交差点 止まる心と 待つ気持ち'
        ],
        'checklist' => []
    ];
}

// スローガンが7つ未満の場合は空文字で埋める
if (isset($content_data['slogans']) && count($content_data['slogans']) < 7) {
    while (count($content_data['slogans']) < 7) {
        $content_data['slogans'][] = '';
    }
}

// JSONレスポンスを返す
echo json_encode($content_data, JSON_UNESCAPED_UNICODE);
