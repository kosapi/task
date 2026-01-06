<?php
// 画像パス修正スクリプト
$file = 'index.html';
$content = file_get_contents($file);

// バックアップ作成
file_put_contents($file . '.backup_' . date('YmdHis'), $content);

// 置換マップ
$replacements = [
    'src="bound_time_v2025_v2.jpg"' => 'src="img/bound_time_v2025_v2.jpg"',
    'src="nairinsa.jpg"' => 'src="img/nairinsa.jpg"',
    'src="daiya_v1.png"' => 'src="img/daiya_v1.png"',
    'src="%E6%95%B4%E5%82%99%E5%B7%A5%E5%A0%B4%E5%89%8D.jpg"' => 'src="img/%E6%95%B4%E5%82%99%E5%B7%A5%E5%A0%B4%E5%89%8D.jpg"',
    'src="jyoumuhoukoku_002_v2.jpg"' => 'src="img/jyoumuhoukoku_002_v2.jpg"',
    'src="chuijikou_v2.jpg"' => 'src="img/chuijikou_v2.jpg"',
    'src="receipt_start_v2.jpg"' => 'src="img/receipt_start_v2.jpg"',
    'src="jyoumuhoukoku_004_v3.jpg"' => 'src="img/jyoumuhoukoku_004_v2.jpg"',
    'src="jyomuin_v3.png"' => 'src="img/jyomuin_v3.png"',
    'src="leftkeep_v1.png"' => 'src="img/leftkeep_v1.png"',
    'src="%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC.png"' => 'src="img/%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC.png"',
    'src="jyoumuhoukoku_005_v2.jpg"' => 'src="img/jyoumuhoukoku_005_v2.jpg"',
    'src="noukin_03_v2.jpg"' => 'src="img/noukin_03_v2.jpg"',
    'src="uriagehyou_01_v2.jpg"' => 'src="img/uriagehyou_01_v2.jpg"',
    'src="uriagehyou_02_v2.jpg"' => 'src="img/uriagehyou_02_v2.jpg"',
    'src="noukin_02_v2.jpg"' => 'src="img/noukin_02_v2.jpg"',
    'src="noukin_04_v2.jpg"' => 'src="img/noukin_04_v2.jpg"',
    'src="cancel_01_v2.jpg"' => 'src="img/cancel_01_v2.jpg"',
    'src="noukin_05_v2.jpg"' => 'src="img/noukin_05_v2.jpg"',
    'src="kuten_v2.jpg"' => 'src="img/kuten_v2.jpg"',
    'src="hutan_v5.jpg"' => 'src="img/hutan_v5.jpg"',
    'src="syutokoseisan_ilttpan_v2.jpg"' => 'src="img/syutokoseisan_ilttpan_v2.jpg"',
    'src="syutokoseisan_chuou_v2.jpg"' => 'src="img/syutokoseisan_chuou_v2.jpg"',
    'src="syutokoseisan_kawaguti_v2.jpg"' => 'src="img/syutokoseisan_kawaguti_v2.jpg"',
    'src="etcmeisai_v2.jpg"' => 'src="img/etcmeisai_v2.jpg"',
    'src="noukin_06_v2.jpg"' => 'src="img/noukin_06_v2.jpg"',
    'src="noukin_07_v2.jpg"' => 'src="img/noukin_07_v2.jpg"',
    'src="noukin_08_v2.jpg"' => 'src="img/noukin_08_v2.jpg"',
    'src="noukin_09_v2.jpg"' => 'src="img/noukin_09_v2.jpg"',
    'src="noukin_10_v2.jpg"' => 'src="img/noukin_10_v2.jpg"',
    'src="jyoumuhoukoku_006_v3.jpg"' => 'src="img/jyoumuhoukoku_006_v2.jpg"',
    'src="kyori_v5.jpg"' => 'src="img/kyori_v4.jpg"',
    'src="untennipou2_002_v1.jpg"' => 'src="img/untennipou2_002_v1.jpg"',
    'src="untennipou2_003_v1.jpg"' => 'src="img/untennipou2_003_v1.jpg"',
    'src="untennipou2_004_v1.jpg"' => 'src="img/untennipou2_004_v1.jpg"',
    'src="jyoumuhoukoku_007_v2.jpg"' => 'src="img/jyoumuhoukoku_007_v2.jpg"',
    'src="receipt_out_v2.jpg"' => 'src="img/receipt_out_v2.jpg"',
    'src="ect_v2.jpg"' => 'src="img/ect_v2.jpg"',
    'src="sun.png"' => 'src="img/sun.png"',
    'src="weather.png"' => 'src="img/weather.png"',
    'src="umbrella.png"' => 'src="img/umbrella.png"',
    'src="tome.png"' => 'src="img/tome.png"',
    'src="%E7%B9%81%E5%BF%99%E6%9C%9F%E7%94%BB%E9%9D%A2.jpg"' => 'src="img/%E7%B9%81%E5%BF%99%E6%9C%9F%E7%94%BB%E9%9D%A2.jpg"',
    'src="%E9%85%8D%E8%BB%8A%E4%BE%9D%E9%A0%BC.jpg"' => 'src="img/%E9%85%8D%E8%BB%8A%E4%BE%9D%E9%A0%BC.jpg"',
    'src="%E7%84%A1%E7%B7%9A%E5%AF%BE%E5%BF%9C.jpg"' => 'src="img/%E7%84%A1%E7%B7%9A%E5%AF%BE%E5%BF%9C.jpg"',
    'src="%E8%BF%8E%E8%BB%8A%E5%9C%B0%E5%A4%89%E6%9B%B4.jpg"' => 'src="img/%E8%BF%8E%E8%BB%8A%E5%9C%B0%E5%A4%89%E6%9B%B4.jpg"',
    'src="%E3%83%A2%E3%83%BC%E3%83%89%E7%B5%82%E4%BA%86.jpg"' => 'src="img/%E3%83%A2%E3%83%BC%E3%83%89%E7%B5%82%E4%BA%86.jpg"',
    'src="%E9%85%8D%E8%BB%8A%E3%82%AD%E3%83%A3%E3%83%B3%E3%82%BB%E3%83%AB_v2.jpg"' => 'src="img/%E9%85%8D%E8%BB%8A%E3%82%AD%E3%83%A3%E3%83%B3%E3%82%BB%E3%83%AB_v2.jpg"',
    'src="accident.png"' => 'src="img/accident.png"',
    'src="バック駐車.jpg"' => 'src="img/バック駐車.jpg"',
    'src="%E3%83%90%E3%83%83%E3%82%AF%E9%A7%90%E8%BB%8A.jpg"' => 'src="img/%E3%83%90%E3%83%83%E3%82%AF%E9%A7%90%E8%BB%8A.jpg"',
];

// 置換実行
$count = 0;
foreach ($replacements as $search => $replace) {
    $new_content = str_replace($search, $replace, $content, $replaced);
    if ($replaced > 0) {
        $content = $new_content;
        $count += $replaced;
        echo "$search → $replace ($replaced箇所)\n";
    }
}

// ファイル保存
file_put_contents($file, $content);
echo "\n合計 $count 箇所を置換しました。\n";
echo "バックアップファイル: {$file}.backup_" . date('YmdHis') . "\n";
?>
