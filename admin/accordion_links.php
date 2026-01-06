<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$content_data = get_content_data();
$checklist = $content_data['checklist'] ?? [];

// index.htmlからアコーディオンのタイトルを直接抽出
function extractAccordionTitles() {
    $index_html_path = __DIR__ . '/../index.html';
    if (!file_exists($index_html_path)) {
        return [];
    }
    
    $html_content = file_get_contents($index_html_path);
    $accordion_titles = [];
    
    // 正規表現でアコーディオンボタンとそのタイトルを抽出
    // パターン: data-bs-target="#collapse数字" の後の > から </button> までのテキスト
    preg_match_all(
        '/data-bs-target="#(collapse\d+)"[^>]*>\s*([^<]+)\s*<\/button>/u',
        $html_content,
        $matches,
        PREG_SET_ORDER
    );
    
    foreach ($matches as $match) {
        $collapse_id = $match[1];  // collapse0, collapse1 などの実際のID
        $title = trim($match[2]);
        // HTML エンティティをデコード
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $accordion_titles[$collapse_id] = $title;
    }
    
    // ソート（数値抽出して並び替え）
    uksort($accordion_titles, function($a, $b) {
        preg_match('/\d+/', $a, $matches_a);
        preg_match('/\d+/', $b, $matches_b);
        return intval($matches_a[0]) - intval($matches_b[0]);
    });
    
    return $accordion_titles;
}

// index.htmlからモーダル情報を抽出
function extractModalInfo() {
    $index_html_path = __DIR__ . '/../index.html';
    if (!file_exists($index_html_path)) {
        return [];
    }
    
    $html_content = file_get_contents($index_html_path);
    $modals = [];
    
    // 各collapseセクションを特定（実際のID: collapse0, collapse1など）
    preg_match_all(
        '/<div id="(collapse\d+)" class="accordion-collapse collapse".*?>(.*?)<\/div>\s*<\/div>\s*(?=<div class="accordion-item"|<\/div>\s*<\/div>\s*<\/form>)/su',
        $html_content,
        $accordion_matches,
        PREG_SET_ORDER
    );
    
    foreach ($accordion_matches as $accordion_match) {
        $collapse_id = $accordion_match[1];  // collapse0, collapse1などの実ID
        $accordion_content = $accordion_match[2];
        
        // このアコーディオン内のモーダルリンクを探す
        // モーダルの形式: data-bs-target="#ModalXXX" や data-bs-target="ModalXXX"
        preg_match_all(
            '/<a[^>]+data-bs-target="[#]*([^"]+)"[^>]*>(?:<p>)?([^<]+)(?:<\/p>)?<\/a>/u',
            $accordion_content,
            $modal_matches,
            PREG_SET_ORDER
        );
        
        foreach ($modal_matches as $modal_match) {
            $modal_id = $modal_match[1];
            $modal_title = trim($modal_match[2]);
            $modal_title = html_entity_decode($modal_title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            if (!isset($modals[$collapse_id])) {
                $modals[$collapse_id] = [];
            }
            
            $modals[$collapse_id][] = [
                'id' => $modal_id,
                'title' => $modal_title
            ];
        }
    }
    
    return $modals;
}

// アコーディオンリンク情報を生成
$accordion_titles = extractAccordionTitles();
$modal_info = extractModalInfo();
$accordion_links = [];

$accordion_index = 0;  // 表示順序用のインデックス
foreach ($accordion_titles as $collapse_id => $title) {
    $modals = $modal_info[$collapse_id] ?? [];
    
    $accordion_links[] = [
        'index' => $accordion_index,
        'collapse_id' => $collapse_id,  // 実際のID: collapse0, collapse1など
        'heading_id' => 'heading' . preg_replace('/\D/', '', $collapse_id),  // heading0, heading1など
        'title' => $title,
        'category' => '',
        'items_count' => count($modals),
        'modals' => $modals
    ];
    
    $accordion_index++;
}

$csrf_token = generate_csrf_token();

// ベースURLを取得（環境に応じて自動切り替え）
$base_url = get_base_url();

render_admin_header('アコーディオンリンク管理');
?>

<style>
.link-card {
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
}
.link-card:hover {
    border-left-color: #0d6efd;
    background-color: #f8f9fa;
}
.copy-btn {
    opacity: 0.7;
    transition: opacity 0.2s ease;
}
.copy-btn:hover {
    opacity: 1;
}
.copy-success {
    animation: copySuccess 0.5s ease;
}
@keyframes copySuccess {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
.link-preview {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    word-break: break-all;
}
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-link-45deg me-2"></i>アコーディオンリンク管理
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/task/" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye me-1"></i>サイトを表示
        </a>
    </div>
</div>

<div class="alert alert-info mb-4">
    <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>アコーディオン・モーダルリンクについて</h5>
    <p class="mb-2">各アコーディオンセクションやその中のモーダルに直接リンクできるURLを生成・管理できます。</p>
    <hr>
    <p class="mb-0 small">
        <strong>💡 使い方：</strong> リンクをコピーして共有すると、そのセクションやモーダルが自動的に開いた状態でページが表示されます。<br>
        <i class="bi bi-chevron-down me-1"></i>ボタンをクリックすると、アコーディオン内のモーダル一覧が表示されます。
    </p>
</div>

<?php if (empty($accordion_links)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
            <h5 class="mt-3 text-muted">アコーディオンがありません</h5>
            <p class="text-muted">ライブエディタでコンテンツを追加してください。</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>アコーディオンリンク一覧
                        <span class="badge bg-light text-dark ms-2"><?php echo count($accordion_links); ?>件</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($accordion_links as $link): ?>
                            <div class="list-group-item link-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <span class="badge bg-secondary me-2">#<?php echo $link['index']; ?></span>
                                            <?php echo h($link['title']); ?>
                                        </h6>
                                        <?php if (!empty($link['category'])): ?>
                                            <span class="badge bg-info text-dark me-2"><?php echo h($link['category']); ?></span>
                                        <?php endif; ?>
                                        <small class="text-muted"><?php echo $link['items_count']; ?>個のモーダル</small>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary copy-btn" 
                                                onclick="copyLink('<?php echo h($base_url . '#' . $link['collapse_id']); ?>', this)"
                                                title="アコーディオンリンクをコピー">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                        <a href="<?php echo h($base_url . '#' . $link['collapse_id']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-success"
                                           title="新しいタブで開く">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <?php if (!empty($link['modals'])): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="collapse"
                                                data-bs-target="#modals-<?php echo $link['index']; ?>"
                                                title="モーダル一覧を表示">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="link-preview mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg me-1"></i><?php echo h($base_url . '#' . $link['collapse_id']); ?>
                                    </small>
                                </div>
                                
                                <?php if (!empty($link['modals'])): ?>
                                <div class="collapse" id="modals-<?php echo $link['index']; ?>">
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted fw-bold d-block mb-2">
                                            <i class="bi bi-window me-1"></i>このアコーディオン内のモーダル:
                                        </small>
                                            <div class="list-group list-group-flush">
                                            <?php foreach ($link['modals'] as $modal): ?>
                                            <div class="list-group-item p-2 bg-light">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-truncate flex-grow-1 me-2">
                                                        <i class="bi bi-card-text me-1"></i><?php echo h($modal['title']); ?>
                                                    </small>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary copy-btn" 
                                                                onclick="copyLink('<?php echo h($base_url . '#' . $modal['id']); ?>', this)"
                                                                title="モーダルリンクをコピー">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                        <a href="<?php echo h($base_url . '#' . $modal['id']); ?>" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-success"
                                                           title="新しいタブで開く">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="link-preview mt-1" style="font-size: 0.75rem; padding: 4px 8px;">
                                                    <small class="text-muted">
                                                        <?php echo h($base_url . '#' . $modal['id']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>使い方ガイド</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary mb-2"><i class="bi bi-folder2-open me-1"></i>アコーディオンリンク</h6>
                    <ol class="ps-3 mb-3 small">
                        <li class="mb-1">コピーボタンでリンクをコピー</li>
                        <li class="mb-1">メールやチャットで共有</li>
                        <li class="mb-1">アコーディオンが自動的に開きます</li>
                    </ol>
                    
                    <h6 class="text-info mb-2"><i class="bi bi-window me-1"></i>モーダルリンク</h6>
                    <ol class="ps-3 mb-0 small">
                        <li class="mb-1"><i class="bi bi-chevron-down"></i> ボタンでモーダル一覧を表示</li>
                        <li class="mb-1">各モーダルのリンクをコピー</li>
                        <li class="mb-1">アコーディオンとモーダルが同時に開きます</li>
                    </ol>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>統計情報</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">アコーディオン数:</small>
                        <strong><?php echo count($accordion_links); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">総モーダル数:</small>
                        <strong><?php echo array_sum(array_column($accordion_links, 'items_count')); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">総リンク数:</small>
                        <strong><?php echo count($accordion_links) + array_sum(array_column($accordion_links, 'items_count')); ?></strong>
                    </div>
                    <hr>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>リンクはindex.htmlと自動的に同期されます
                    </small>
                </div>
            </div>

            <div class="card border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i>注意事項</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        • リンクはアコーディオンの順序に基づいています<br>
                        • チェックリストを並び替えると、リンクが変更される可能性があります<br>
                        • リンク共有前に確認することをお勧めします
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- コピー成功トースト -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="copyToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>リンクをコピーしました！
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// リンクをクリップボードにコピー
function copyLink(url, button) {
    navigator.clipboard.writeText(url).then(function() {
        // ボタンのアイコンを一時的に変更
        const icon = button.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'bi bi-check2';
        button.classList.add('copy-success');
        
        // トーストを表示
        const toastEl = document.getElementById('copyToast');
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        // 1.5秒後に元に戻す
        setTimeout(function() {
            icon.className = originalClass;
            button.classList.remove('copy-success');
        }, 1500);
    }, function(err) {
        // コピー失敗時
        alert('コピーに失敗しました: ' + err);
    });
}

// ページ読み込み時のアニメーション
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.link-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 50);
    });
});
</script>

<?php render_admin_footer(); ?>
