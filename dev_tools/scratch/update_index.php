<?php
$orig = file_get_contents('c:/xampp/htdocs/task/dev_tools/index_backup_original.html');
$subModals = file_get_contents('c:/xampp/htdocs/task/dev_tools/scratch/extracted_sub_modals.html');

$scriptTag = '<script src="js/checklist-render.js?v=20260329_001" defer=""></script>' . "\n";
if (strpos($orig, 'js/checklist-render.js') === false) {
    $orig = str_replace('<script src="js/init.js', $scriptTag . '  <script src="js/init.js', $orig);
}

// modal-fix.css のキャッシュ回避
$orig = str_replace('css/modal-fix.css?v=20260131_001', 'css/modal-fix.css?v=20260805_full100', $orig);
$orig = str_replace('css/modal-fix.css', 'css/modal-fix.css?v=20260805_full100', $orig);
$orig = str_replace('js/nested-modals.js?v=20260131_001', 'js/nested-modals.js?v=20260805_nest002', $orig);
$orig = str_replace('js/nested-modals.js?v=20260805_nest001', 'js/nested-modals.js?v=20260805_nest002', $orig);

$startPos = strpos($orig, '<div class="accordion" id="accordion">');
$endPos = strrpos($orig, '<!-- フッター -->');

if ($startPos !== false && $endPos !== false) {
    $before = substr($orig, 0, $startPos);
    $after = substr($orig, $endPos);
    
    // フッター内の更新日を本日の日付に更新
    $todayStr = date('Y年m月d日');
    $after = preg_replace('/(<small>更新日:\s*<time>)(.*?)(<\/time><\/small>)/u', '${1}' . $todayStr . '${3}', $after);

    $carouselHtml = <<<HTML

      <button type="button" id="clear" class="btn btn-primary rounded-circle p-0 position-absolute start-0" style="width:4rem;height:4rem; margin: 15px;"><i class="bi bi-eraser"></i></button>
      <a href="#Modal16" role="button" id="saveButton" data-bs-toggle="modal" data-bs-target="#Modal16" class="btn btn-success rounded-circle position-absolute start-50" style="width:3.5rem;height:3.5rem; margin: 15px 0 0 0; display: flex; align-items: center; justify-content: center;" title="シェア"><i class="bi bi-share" style="font-size: 1.5rem;"></i></a>

      <!-- 横スクロールリンクボタン群 -->
      <div class="button-carousel-container">
        <div class="button-carousel-track">
          <a class="carousel-button" id="bc1" href="https://teito.link/ruby">23区地名読み方</a>
          <a class="carousel-button" id="bc11" href="https://teito.link/sale">営&nbsp;収&nbsp;管&nbsp;理</a>
          <a class="carousel-button" id="bc12" href="https://teito.link/sales_data">営業ダッシュボード</a>
          <a class="carousel-button" id="bc2" href="https://teito.link/time">経過時間集計</a>
          <a class="carousel-button" id="bc3" href="https://teito.link/working">月間拘束時間集計</a>
          <a class="carousel-button" id="bc1_1" href="https://teito.link/kind/#section0">在高計算機</a>
          <a class="carousel-button" id="bc2_1" href="https://teito.link/tip">チェップ集計</a>
          <a class="carousel-button" id="bc4" href="https://www.google.com/maps/d/edit?mid=1D92qhMO-dhPBB2wqwdAs_RusJ6Av9iI&amp;usp=sharing">LPスタンド地図</a>
          <a class="carousel-button" id="bc5" href="https://www.google.com/maps/d/u/0/edit?mid=1mFUsHyNbXHVWuzaUPdlbMWbUZpGLQjVZ&amp;usp=sharing">銀座乗禁地図</a>
          <a class="carousel-button" id="bc6" href="https://teito.link/maps/">地図とストリートビュー</a>
          <a class="carousel-button" id="bc7" href="https://teito.link/road/">道路名地図</a>
          <a class="carousel-button" id="bc9" href="https://sites.google.com/view/teito">帝都板橋サイト</a>
        </div>
      </div>

      <div class="modal fade" id="Modal16" tabindex="-1" aria-labelledby="ModalLabe16" aria-hidden="true" data-original-parent="accordion">
        <div class="modal-dialog modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="ModalLabe16">シェア用QRコード</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-inline-block text-wrap text-center">
              <p class="mb-3">このページをシェアするためのQRコード</p>
              <div id="qrcode-container" style="display: inline-block;"></div>
              <p class="mt-3 small text-muted">スマートフォンのカメラでQRコードを読み取ってください</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 独立サブモーダル群（チケット/福祉券、キャンセル処理、ETC明細書等） -->
      {$subModals}

HTML;

    $newHtml = $before . '<div class="accordion" id="accordion"></div>' . "\n" . $carouselHtml . "\n      " . $after;
    file_put_contents('c:/xampp/htdocs/task/index.html', $newHtml);
    echo "index.html rebuild with sub-modals complete! Date updated to {$todayStr}\n";
}
