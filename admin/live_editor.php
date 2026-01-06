<?php
// デバッグ用エラー表示
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

// index.htmlを読み込む
$indexPath = __DIR__ . '/../index.html';

// エラーハンドリング追加
if (!file_exists($indexPath)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not Found Error: index.html が見つかりません (' . $indexPath . ')']);
        exit;
    }
    // 通常のページ表示時
    render_admin_header('ライブ編集');
    echo '<div class="container mt-5"><div class="alert alert-danger"><h4>エラー</h4><p>index.html が見つかりません。</p><p>パス: ' . htmlspecialchars($indexPath) . '</p></div></div>';
    render_admin_footer();
    exit;
}

$html = file_get_contents($indexPath);
if ($html === false) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not Found Error: index.html の読み込みに失敗しました']);
        exit;
    }
    // 通常のページ表示時
    render_admin_header('ライブ編集');
    echo '<div class="container mt-5"><div class="alert alert-danger"><h4>エラー</h4><p>index.html の読み込みに失敗しました。</p></div></div>';
    render_admin_footer();
    exit;
}

// DOMユーティリティ
function load_dom($html) {
    if (empty($html)) {
        throw new Exception('load_dom: HTMLコンテンツが空です');
    }
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    // 文字化け対策
    $html = preg_replace('/<meta[^>]+charset=[^>]+>/i', '', $html);
    $result = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    if (!$result) {
        throw new Exception('load_dom: HTML解析に失敗しました');
    }
    return $dom;
}

function save_dom_html($dom) {
    $html = $dom->saveHTML();
    // 全てのXML宣言を除去（複数あっても対応）
    $html = preg_replace('/<\?xml[^>]*>\s*/i', '', $html);
    return $html;
}

function set_inner_html(DOMNode $node, $html) {
    // 子要素を全削除
    while ($node->firstChild) {
        $node->removeChild($node->firstChild);
    }
    if ($html === '' || $html === null) return;
    $tmp = new DOMDocument('1.0', 'UTF-8');
    $tmp->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    foreach ($tmp->getElementsByTagName('body')->item(0)->childNodes as $child) {
        $import = $node->ownerDocument->importNode($child, true);
        $node->appendChild($import);
    }
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 画像リスト取得
    if ($_POST['action'] === 'get_images') {
        header('Content-Type: application/json');
        
        $images = [
            'img' => [],
            'uploads' => [],
            'root' => []
        ];
        
        // img フォルダの画像
        $imgDir = __DIR__ . '/../img';
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $images['img'][] = [
                        'name' => $file,
                        'path' => 'img/' . $file,
                        'url' => '/task/img/' . rawurlencode($file),  // 相対パスで対応
                        'size' => filesize($imgDir . '/' . $file)
                    ];
                }
            }
        }
        
        // uploads フォルダの画像
        $uploadsDir = __DIR__ . '/../uploads';
        if (is_dir($uploadsDir)) {
            $files = scandir($uploadsDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $images['uploads'][] = [
                        'name' => $file,
                        'path' => 'uploads/' . $file,
                        'url' => '/task/uploads/' . rawurlencode($file),
                        'size' => filesize($uploadsDir . '/' . $file)
                    ];
                }
            }
        }
        
        // ルート直下の画像
        $rootDir = __DIR__ . '/..';
        $files = scandir($rootDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $filePath = $rootDir . '/' . $file;
            if (!is_file($filePath)) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $images['root'][] = [
                    'name' => $file,
                    'path' => $file,
                    'url' => '/task/' . rawurlencode($file),
                    'size' => filesize($filePath)
                ];
            }
        }
        
        echo json_encode(['success' => true, 'images' => $images]);
        exit;
    }
    
    // HTML保存
    if ($_POST['action'] === 'save_html') {
        header('Content-Type: application/json');
        
        $newHtml = $_POST['html'] ?? '';
        
        if (empty($newHtml)) {
            echo json_encode(['success' => false, 'error' => 'HTMLコンテンツが空です']);
            exit;
        }
        
        try {
            // 余分なXML宣言を除去（クリーンアップ）
            $newHtml = preg_replace('/<\?xml[^>]*>\s*/i', '', $newHtml);
            
            // body 開きタグを正規化（contenteditable, spellcheck, style, modal-open クラスを除去）
            $newHtml = preg_replace_callback(
                '/<body\s+[^>]*>/i',
                function($m) {
                    $tag = $m[0];
                    // contenteditable, spellcheck を除去
                    $tag = preg_replace('/\s+contenteditable\s*=\s*["\']?[^"\'>\s]+["\']?/', '', $tag);
                    $tag = preg_replace('/\s+spellcheck\s*=\s*["\']?[^"\'>\s]+["\']?/', '', $tag);
                    // style から overlay 関連を除去
                    $tag = preg_replace('/\s+style\s*=\s*"[^"]*(?:overflow\s*:\s*hidden|padding-right)[^"]*"/', '', $tag);
                    // modal-open クラスを除去
                    $tag = preg_replace('/\s+class\s*=\s*"[^"]*\bmodal-open\b[^"]*"/', '', $tag);
                    $tag = preg_replace('/\s+class\s*=\s*""/', '', $tag);
                    return $tag;
                },
                $newHtml
            );
            
            // modal-backdrop を除去（正確に）
            $newHtml = preg_replace('/<div\s+class\s*=\s*"modal-backdrop[^"]*"\s*><\/div>\s*/i', '', $newHtml);
            
            // モーダルから show クラスを除去（正確に）
            $newHtml = preg_replace_callback(
                '/<div\s+class\s*=\s*"([^"]*\bmodal\b[^"]*)"\s+([^>]*)/i',
                function($m) {
                    $class = preg_replace('/\s+show\b/', '', $m[1]);
                    return '<div class="' . $class . '" ' . $m[2];
                },
                $newHtml
            );
            
            // モーダルから display: block style を除去
            $newHtml = preg_replace_callback(
                '/style\s*=\s*"([^"]*\bmodal\b[^"]*)"/i',
                function($m) {
                    $style = preg_replace('/display\s*:\s*block[^;]*;?\s*/', '', $m[1]);
                    if (trim($style)) {
                        return 'style="' . $style . '"';
                    }
                    return '';
                },
                $newHtml
            );
            
            // モーダルに aria-hidden="true" を設定
            $newHtml = preg_replace_callback(
                '/(<div\s+class\s*=\s*"[^"]*\bmodal\b[^"]*"\s+[^>]*)(?:aria-modal\s*=\s*"true"|role\s*=\s*"dialog")?([^>]*>)/i',
                function($m) {
                    $hasAriaHidden = stripos($m[1] . $m[2], 'aria-hidden') !== false;
                    if (!$hasAriaHidden) {
                        return $m[1] . $m[2];
                    }
                    return $m[0];
                },
                $newHtml
            );
            
            // collapse から show クラスを除去
            $newHtml = preg_replace_callback(
                '/(<div\s+[^>]*\baccordion-collapse\s+collapse)\s+show\b([^>]*>)/i',
                function($m) {
                    return $m[1] . $m[2];
                },
                $newHtml
            );
            
            // フッターの更新日を本日の日付に変更
            $today = date('Y年m月d日', strtotime('today'));
            $newHtml = preg_replace(
                '/<time>[^<]*<\/time>/',
                '<time>' . htmlspecialchars($today) . '</time>',
                $newHtml
            );
            
            // バックアップを作成
            $backupDir = __DIR__ . '/../data/backups';
            if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
            copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');
            
            // 新しいHTMLを保存
            if (file_put_contents($indexPath, $newHtml) !== false) {
                echo json_encode(['success' => true, 'message' => 'HTMLを保存しました']);
            } else {
                echo json_encode(['success' => false, 'error' => 'ファイルの書き込みに失敗しました']);
            }
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    // ネストされたアコーディオンをチェック＆削除
    if ($_POST['action'] === 'check_nested_accordions') {
        header('Content-Type: application/json');
        
        try {
            $dom = load_dom($html);
            $xp = new DOMXPath($dom);
            
            // メインアコーディオンを取得
            $mainAccordion = $xp->query('//div[@id="accordion"]')->item(0);
            
            if (!$mainAccordion) {
                echo json_encode(['success' => true, 'found' => 0, 'message' => 'メインアコーディオンが見つかりません']);
                exit;
            }
            
            // ネストされたアコーディオンを検索（メインアコーディオン以外のaccordionクラス）
            $nestedAccordions = $xp->query('.//div[contains(@class, "accordion")]', $mainAccordion);
            $removedCount = 0;
            $removedIds = [];
            
            foreach ($nestedAccordions as $nested) {
                // メインアコーディオン自体は除外
                if ($nested !== $mainAccordion && $nested->getAttribute('id') !== 'accordion') {
                    $nestedId = $nested->getAttribute('id') ?: 'ID無し';
                    $parent = $nested->parentNode;
                    if ($parent) {
                        $parent->removeChild($nested);
                        $removedIds[] = $nestedId;
                        $removedCount++;
                    }
                }
            }
            
            if ($removedCount > 0) {
                // バックアップを作成
                $backupDir = __DIR__ . '/../data/backups';
                if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
                copy($indexPath, $backupDir . '/index_cleanup_' . date('Y-m-d_H-i-s') . '.html');
                
                // 修正後のHTMLを保存
                $html = save_dom_html($dom);
                file_put_contents($indexPath, $html);
                
                echo json_encode([
                    'success' => true, 
                    'found' => $removedCount, 
                    'removed' => $removedIds,
                    'message' => "ネストされたアコーディオン {$removedCount}個を削除しました"
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'found' => 0, 
                    'message' => 'ネストされたアコーディオンは見つかりませんでした'
                ]);
            }
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    // バックアップ一覧取得
    if ($_POST['action'] === 'get_backups') {
        $backupDir = '../data/backups';
        if (!file_exists($backupDir)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'backups' => []]);
            exit;
        }
        
        $files = scandir($backupDir, SCANDIR_SORT_DESCENDING);
        $backups = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file[0] === '.') continue;
            if (preg_match('/^index_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.html$/', $file, $m)) {
                $backups[] = [
                    'file' => $file,
                    'timestamp' => $m[1],
                    'time' => str_replace('_', ' ', $m[1])
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'backups' => $backups]);
        exit;
    }
    
    // バックアップから復元
    if ($_POST['action'] === 'restore_backup') {
        $backupFile = $_POST['backup_file'] ?? '';
        
        // ファイル名に ../ などが含まれていないか確認（セキュリティ）
        if (preg_match('/[\/\\\\]/', $backupFile)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => '無効なファイル名です']);
            exit;
        }
        
        $backupPath = '../data/backups/' . $backupFile;
        if (!file_exists($backupPath)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'バックアップファイルが見つかりません']);
            exit;
        }
        
        try {
            // 現在のファイルをバックアップ
            $backupDir = '../data/backups';
            if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
            copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');
            
            // バックアップから復元
            copy($backupPath, $indexPath);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'バックアップから復元しました']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    if ($_POST['action'] === 'update_modal_title') {
        $modalId = $_POST['modal_id'] ?? '';
        $newTitle = $_POST['title'] ?? '';

        $backupDir = '../data/backups';
        if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
        copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');

        $dom = load_dom($html);
        $xp = new DOMXPath($dom);
        $modal = $xp->query('//div[@id="' . $modalId . '"][contains(@class, "modal")]')->item(0);
        if ($modal) {
            $titleNode = $xp->query('.//h5[contains(@class, "modal-title")]', $modal)->item(0);
            if ($titleNode) {
                set_inner_html($titleNode, htmlspecialchars($newTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                $html = save_dom_html($dom);
                file_put_contents($indexPath, $html);
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'タイトル要素が見つかりません']);
        exit;
    }

    if ($_POST['action'] === 'update_modal_body') {
        $modalId = $_POST['modal_id'] ?? '';
        $newBody = $_POST['body'] ?? '';

        $backupDir = '../data/backups';
        if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
        copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');

        $dom = load_dom($html);
        $xp = new DOMXPath($dom);
        $modal = $xp->query('//div[@id="' . $modalId . '"][contains(@class, "modal")]')->item(0);
        if ($modal) {
            $bodyNode = $xp->query('.//div[contains(@class, "modal-body")]', $modal)->item(0);
            if ($bodyNode) {
                set_inner_html($bodyNode, $newBody);
                $html = save_dom_html($dom);
                file_put_contents($indexPath, $html);
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => '本文要素が見つかりません']);
        exit;
    }

    if ($_POST['action'] === 'update_item_text') {
        $modalId = $_POST['modal_id'] ?? '';
        $newText = $_POST['text'] ?? '';

        $backupDir = '../data/backups';
        if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
        copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');
        
        $dom = load_dom($html);
        $xp = new DOMXPath($dom);
        // aタグ優先、次にbuttonも検索
        $link = $xp->query('//a[@data-bs-target="#' . $modalId . '"]')->item(0);
        if (!$link) {
            $link = $xp->query('//button[@data-bs-target="#' . $modalId . '"]')->item(0);
        }
        if ($link) {
            set_inner_html($link, htmlspecialchars($newText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $html = save_dom_html($dom);
            file_put_contents($indexPath, $html);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'リンクが見つかりません']);
        exit;
    }

    // 汎用: XPathでノードのinnerHTMLを書き換え
    if ($_POST['action'] === 'update_by_xpath') {
        $xpathExpr = $_POST['xpath'] ?? '';
        $newHtml = $_POST['html'] ?? '';

        $backupDir = '../data/backups';
        if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
        copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');

        $dom = load_dom($html);
        $xp = new DOMXPath($dom);
        $node = $xp->query($xpathExpr)->item(0);
        if ($node) {
            set_inner_html($node, $newHtml);
            $html = save_dom_html($dom);
            file_put_contents($indexPath, $html);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'XPathに一致する要素が見つかりません']);
        exit;
    }

    // 汎用: XPathで属性を更新
    if ($_POST['action'] === 'update_attr_by_xpath') {
        $xpathExpr = $_POST['xpath'] ?? '';
        $attr = $_POST['attr'] ?? '';
        $value = $_POST['value'] ?? '';

        $backupDir = '../data/backups';
        if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
        copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');

        $dom = load_dom($html);
        $xp = new DOMXPath($dom);
        $node = $xp->query($xpathExpr)->item(0);
        if ($node && $node instanceof DOMElement) {
            if ($value === '' && $node->hasAttribute($attr)) {
                $node->removeAttribute($attr);
            } else {
                $node->setAttribute($attr, $value);
            }
            $html = save_dom_html($dom);
            file_put_contents($indexPath, $html);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => '属性更新対象が見つかりません']);
        exit;
    }

    // 画像アップロード（ライブ編集用）
    if ($_POST['action'] === 'upload_image') {
        header('Content-Type: application/json');

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => '画像ファイルを選択してください']);
            exit;
        }

        $file = $_FILES['image'];
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            echo json_encode(['success' => false, 'error' => '画像サイズが大きすぎます（最大 ' . floor(MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB）']);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        if (!in_array($ext, $allowedImages, true)) {
            echo json_encode(['success' => false, 'error' => '画像形式は jpg / png / gif / webp / svg のみ利用できます']);
            exit;
        }

        if (!is_dir(UPLOADS_DIR)) {
            mkdir(UPLOADS_DIR, 0777, true);
        }

        $safeName = generate_safe_filename($file['name']);
        $targetPath = rtrim(UPLOADS_DIR, '/\\') . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'error' => '画像の保存に失敗しました']);
            exit;
        }

        $url = '/task/uploads/' . $safeName;
        echo json_encode(['success' => true, 'url' => $url, 'filename' => $safeName]);
        exit;
    }

    // モーダル内容取得（最新版のindex.htmlを参照）
    if ($_POST['action'] === 'get_modal_content') {
        header('Content-Type: application/json');
        $modalId = $_POST['modal_id'] ?? '';
        if (!$modalId) {
            echo json_encode(['success' => false, 'error' => 'modal_idが指定されていません']);
            exit;
        }
        try {
            $latestHtml = file_get_contents($indexPath);
            if ($latestHtml === false) {
                throw new Exception('index.htmlの読み込みに失敗しました');
            }
            $dom = load_dom($latestHtml);
            $xp = new DOMXPath($dom);

            $modal = $xp->query('//div[@id="' . $modalId . '"][contains(@class, "modal")]')->item(0);
            if (!$modal) {
                echo json_encode(['success' => false, 'error' => '指定のモーダルが見つかりません']);
                exit;
            }

            $titleNode = $xp->query('.//h5[contains(@class, "modal-title")]', $modal)->item(0);
            $bodyNode = $xp->query('.//div[contains(@class, "modal-body")]', $modal)->item(0);
            $titleText = $titleNode ? $titleNode->textContent : '';
            $bodyHtml = '';
            if ($bodyNode) {
                foreach ($bodyNode->childNodes as $child) {
                    $bodyHtml .= $bodyNode->ownerDocument->saveHTML($child);
                }
            }

            // トリガーのテキスト（a優先、buttonも検索）
            $linkNode = $xp->query('//a[@data-bs-target="#' . $modalId . '"]')->item(0);
            if (!$linkNode) {
                $linkNode = $xp->query('//button[@data-bs-target="#' . $modalId . '"]')->item(0);
            }
            $linkText = $linkNode ? $linkNode->textContent : '';

            echo json_encode([
                'success' => true,
                'modal_id' => $modalId,
                'title' => $titleText,
                'body' => $bodyHtml,
                'link_text' => $linkText
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    // アコーディオン追加
    if ($_POST['action'] === 'add_accordion') {
        header('Content-Type: application/json');
        
        $title = $_POST['title'] ?? '新しいアコーディオン';
        $title = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        
        try {
            // デバッグ: 最初のチェック
            if (empty($html)) {
                throw new Exception('Step 1: HTMLコンテンツが空です（load失敗）');
            }
            
            // デバッグ: HTMLサイズ確認
            if (strlen($html) < 1000) {
                throw new Exception('Step 2: HTMLファイルが小さすぎます（サイズ: ' . strlen($html) . '）');
            }
            
            $backupDir = __DIR__ . '/../data/backups';
            if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
            
            // バックアップ作成
            $backup_file = $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html';
            if (!copy($indexPath, $backup_file)) {
                throw new Exception('Step 3: バックアップ作成失敗');
            }

            $dom = load_dom($html);
            $xp = new DOMXPath($dom);
            
            // ID="accordion" のメインアコーディオンを見つける
            $mainAccordion = $xp->query('//div[@id="accordion"]')->item(0);
            
            if (!$mainAccordion) {
                throw new Exception('メインアコーディオン要素が見つかりません');
            }
            
            // ★ネストされたアコーディオン構造を検出して防止
            $nestedAccordions = $xp->query('.//div[contains(@class, "accordion")]', $mainAccordion);
            if ($nestedAccordions->length > 0) {
                // ネストされたアコーディオンが存在する場合は削除
                foreach ($nestedAccordions as $nested) {
                    // メインアコーディオン自体は除外
                    if ($nested !== $mainAccordion && $nested->getAttribute('id') !== 'accordion') {
                        $parent = $nested->parentNode;
                        if ($parent) {
                            $parent->removeChild($nested);
                            error_log('警告: ネストされたアコーディオンを検出し削除しました: ' . $nested->getAttribute('id'));
                        }
                    }
                }
                // 修正後のHTMLを保存
                $html = save_dom_html($dom);
                file_put_contents($indexPath, $html);
            }
            
            // 新しいアコーディオンIDを生成
            $newId = 'accordion_' . uniqid();
            $collapseId = $newId . '_collapse';
            
            // 新しいアコーディオン要素を作成
            $newItem = $dom->createElement('div');
            $newItem->setAttribute('class', 'accordion-item');
            $newItem->setAttribute('id', $newId);  // ← アコーディオンアイテムに ID を付与
            
            // ヘッダー要素を作成
            $header = $dom->createElement('h2');
            $header->setAttribute('class', 'accordion-header');
            $header->setAttribute('id', 'heading_' . $newId);
            
            $button = $dom->createElement('button');
            $button->setAttribute('class', 'accordion-button collapsed shadow text-reset fw-bold');
            $button->setAttribute('type', 'button');
            $button->setAttribute('data-bs-toggle', 'collapse');
            $button->setAttribute('data-bs-target', '#' . $collapseId);
            $button->setAttribute('aria-expanded', 'false');
            $button->setAttribute('aria-controls', $collapseId);
            $button->textContent = $title;
            
            $header->appendChild($button);
            $newItem->appendChild($header);
            
            // コンテンツ要素を作成
            $collapse = $dom->createElement('div');
            $collapse->setAttribute('id', $collapseId);
            $collapse->setAttribute('class', 'accordion-collapse collapse');
            $collapse->setAttribute('aria-labelledby', 'heading_' . $newId);
            $collapse->setAttribute('data-bs-parent', '#accordion');
            
            $body = $dom->createElement('div');
            $body->setAttribute('class', 'accordion-body');
            
            $p = $dom->createElement('p');
            $p->textContent = 'ここに内容を追加してください';
            $body->appendChild($p);
            
            $collapse->appendChild($body);
            $newItem->appendChild($collapse);
            
            // 最後のアコーディオンアイテムを見つけて、その直後に新しいアイテムを挿入
            $allItems = $xp->query('.//div[contains(@class, "accordion-item")]', $mainAccordion);
            
            if ($allItems->length > 0) {
                // 最後のアイテムを取得
                $lastItem = $allItems->item($allItems->length - 1);
                
                // 最後のアイテムの直後に挿入
                if ($lastItem->nextSibling) {
                    $result = @$mainAccordion->insertBefore($newItem, $lastItem->nextSibling);
                    if (!$result) {
                        throw new Exception('insertBefore failed: アコーディオン要素を挿入できません');
                    }
                } else {
                    $result = @$mainAccordion->appendChild($newItem);
                    if (!$result) {
                        throw new Exception('appendChild failed (after lastItem): アコーディオン要素を追加できません');
                    }
                }
            } else {
                // アイテムがない場合はmainAccordionに直接追加
                $result = @$mainAccordion->appendChild($newItem);
                if (!$result) {
                    throw new Exception('appendChild failed (no items): アコーディオン要素を追加できません');
                }
            }
            
            $html = save_dom_html($dom);
            if (!file_put_contents($indexPath, $html)) {
                throw new Exception('ファイルの書き込みに失敗しました');
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'アコーディオンを追加しました']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            $error_msg = $e->getMessage();
            $error_code = $e->getCode();
            $error_file = basename($e->getFile());
            $error_line = $e->getLine();
            
            // ログにも記録
            error_log("Accordion add error: {$error_msg} (Code: {$error_code}) at {$error_file}:{$error_line}");
            error_log('Trace: ' . $e->getTraceAsString());
            
            echo json_encode([
                'success' => false,
                'error' => $error_msg,
                'code' => $error_code,
                'location' => "{$error_file}:{$error_line}"
            ]);
            exit;
        }
    }

    // アコーディオン削除
    if ($_POST['action'] === 'delete_accordion_by_index') {
        $index = intval($_POST['accordion_index'] ?? -1);
        
        if ($index < 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'アコーディオンのインデックスが指定されていません']);
            exit;
        }
        
        try {
            $backupDir = '../data/backups';
            if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
            copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');

            $dom = load_dom($html);
            $xp = new DOMXPath($dom);
            
            // すべてのアコーディオンアイテムを取得
            $accordionItems = $xp->query('//div[@class="accordion-item"]');
            
            if ($index >= $accordionItems->length) {
                throw new Exception('アコーディオン: インデックス ' . $index . ' が範囲外です');
            }
            
            $toDelete = $accordionItems->item($index);
            
            // 要素を削除
            $toDelete->parentNode->removeChild($toDelete);
            
            $html = save_dom_html($dom);
            if (!file_put_contents($indexPath, $html)) {
                throw new Exception('ファイルの書き込みに失敗しました');
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($_POST['action'] === 'delete_accordion') {
        $accordionId = $_POST['accordion_id'] ?? '';
        
        if (empty($accordionId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'アコーディオンIDが指定されていません']);
            exit;
        }
        
        try {
            $backupDir = '../data/backups';
            if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
            copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');

            $dom = load_dom($html);
            $xp = new DOMXPath($dom);
            
            // 方法1: 指定されたIDのアコーディオンアイテムを見つけて削除
            $toDelete = $xp->query('//div[@id="' . $accordionId . '"]')->item(0);
            
            // 方法2: IDが見つからない場合、クラスでアコーディオンを探す
            if (!$toDelete) {
                // すべてのアコーディオンアイテムを取得
                $accordionItems = $xp->query('//div[@class="accordion-item"]');
                
                // 指定されたIDのアコーディオンを探す
                foreach ($accordionItems as $item) {
                    if ($item->getAttribute('id') === $accordionId) {
                        $toDelete = $item;
                        break;
                    }
                }
            }
            
            if (!$toDelete) {
                throw new Exception('アコーディオン ID: ' . $accordionId . ' が見つかりません');
            }
            
            // 要素を削除
            $toDelete->parentNode->removeChild($toDelete);
            
            $html = save_dom_html($dom);
            if (!file_put_contents($indexPath, $html)) {
                throw new Exception('ファイルの書き込みに失敗しました');
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'アコーディオンを削除しました']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    // モーダル追加
    if ($_POST['action'] === 'add_modal') {
        $linkText = $_POST['link_text'] ?? '新しい項目';
        $title = $_POST['modal_title'] ?? '新しいモーダル';
        $body = $_POST['modal_body'] ?? '<p>ここに内容を入力してください</p>';
        $targetAccordion = $_POST['target_accordion'] ?? '';
        
        $backupDir = '../data/backups';
        if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
        copy($indexPath, $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html');

        $dom = load_dom($html);
        $xp = new DOMXPath($dom);
        
        // 新しいモーダルIDを生成
        $newModalId = 'ModalNew_' . time();
        
        // モーダルテンプレート生成
        $modalTemplate = <<<HTML
<div class="modal fade" id="{$newModalId}" tabindex="-1" aria-labelledby="{$newModalId}Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title kokuban" id="{$newModalId}Label">{$title}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {$body}
      </div>
    </div>
  </div>
</div>
HTML;
        
        // リンクテンプレート生成
        $linkTemplate = <<<HTML
<div class="form-check">
  <a href="#{$newModalId}" class="link-primary" data-bs-toggle="modal" data-bs-target="#{$newModalId}">{$linkText}</a>
</div>
HTML;
        
        // アコーディオン内の最後に挿入するか、最後のモーダルの後ろに追加
        if ($targetAccordion) {
            $targetNode = $xp->query('//div[@id="' . $targetAccordion . '"]//div[contains(@class, "accordion-body")]')->item(0);
            if ($targetNode) {
                $frag = $dom->createDocumentFragment();
                $frag->appendXML($linkTemplate);
                $targetNode->appendChild($frag);
            }
        }
        
        // bodyの最後にモーダル追加
        $body = $xp->query('//body')->item(0);
        if ($body) {
            $modalFrag = $dom->createDocumentFragment();
            $modalFrag->appendXML($modalTemplate);
            $body->appendChild($modalFrag);
            
            $html = save_dom_html($dom);
            file_put_contents($indexPath, $html);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'モーダルを追加しました', 'modal_id' => $newModalId]);
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'モーダルの挿入位置が見つかりません']);
        exit;
    }
}

render_admin_header('ライブ編集');
?>

<style>
    .editor-container {
        display: flex;
        gap: 15px;
        height: calc(100vh - 200px);
        min-height: 600px;
    }
    .editor-pane, .preview-pane, .image-browser-pane {
        display: flex;
        flex-direction: column;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: white;
    }
    .editor-pane {
        flex: 2;
    }
    .preview-pane {
        flex: 2;
    }
    .image-browser-pane {
        flex: 1;
        min-width: 280px;
        max-width: 350px;
    }
    .pane-header {
        padding: 12px 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pane-header i {
        margin-right: 8px;
    }
    .pane-content {
        flex: 1;
        overflow: auto;
        padding: 0;
        display: flex;
        flex-direction: column;
    }
    .pane-header small {
        transition: all 0.3s ease;
    }
    #htmlEditor {
        width: 100%;
        height: 100%;
        flex: 1;
        border: none;
        padding: 15px;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.6;
        resize: none;
        background: #f8f9fa;
    }
    /* 選択範囲を強調表示 */
    #htmlEditor::selection {
        background: #0d6efd;
        color: white;
    }
    #htmlEditor::-moz-selection {
        background: #0d6efd;
        color: white;
    }
    #preview {
        width: 100%;
        height: 100%;
        flex: 1;
        border: none;
        background: white;
    }
    .save-indicator {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        display: none;
    }
    .toolbar {
        padding: 12px 0;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .line-numbers {
        color: #6c757d;
        font-size: 12px;
        margin-left: 5px;
    }
    
    /* 画像ブラウザスタイル */
    .image-browser-content {
        padding: 10px;
    }
    .folder-section {
        margin-bottom: 15px;
    }
    .folder-title {
        font-weight: 600;
        color: #495057;
        padding: 8px 10px;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 8px;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .folder-title:hover {
        background: #e9ecef;
    }
    .folder-title i {
        transition: transform 0.2s;
    }
    .folder-title.collapsed i {
        transform: rotate(-90deg);
    }
    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 8px;
        padding: 5px;
    }
    .image-item {
        position: relative;
        aspect-ratio: 1;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8f9fa;
    }
    .image-item:hover {
        border-color: #667eea;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        z-index: 10;
    }
    .image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .image-item-name {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        font-size: 10px;
        padding: 2px 4px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    .image-preview-tooltip {
        position: fixed;
        z-index: 10000;
        pointer-events: none;
        display: none;
        background: white;
        border: 2px solid #667eea;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        max-width: 400px;
    }
    .image-preview-tooltip img {
        max-width: 100%;
        max-height: 300px;
        display: block;
        border-radius: 4px;
    }
    .image-preview-info {
        margin-top: 8px;
        font-size: 12px;
        color: #6c757d;
    }
    .folder-content {
        max-height: 400px;
        overflow-y: auto;
        transition: max-height 0.3s ease;
    }
    .folder-content.collapsed {
        max-height: 0;
        overflow: hidden;
    }
    .no-images {
        padding: 20px;
        text-align: center;
        color: #6c757d;
        font-size: 14px;
    }
    .image-search {
        margin-bottom: 10px;
    }
    .image-search input {
        font-size: 14px;
        padding: 6px 10px;
    }
    .image-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    #htmlEditor.drag-over {
        background: #e7f3ff !important;
        border: 2px dashed #667eea;
    }
    .drag-indicator {
        position: absolute;
        background: #667eea;
        height: 2px;
        width: 100%;
        pointer-events: none;
        display: none;
        z-index: 1000;
    }
    .insertion-preview {
        background: rgba(102, 126, 234, 0.1);
        border-left: 3px solid #667eea;
        padding-left: 5px;
    }
    /* ビジュアルエディタ */
    .editor-mode-toggle .btn {
        min-width: 120px;
    }
    #htmlEditorWrapper, #visualEditorWrapper {
        height: 100%;
        flex: 1;
        display: flex;
    }
    #visualEditorWrapper {
        display: none;
        flex-direction: column;
    }
    .visual-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
    }
    .visual-toolbar .btn {
        padding: 4px 10px;
    }
    .visual-frame {
        width: 100%;
        height: 100%;
        flex: 1;
        border: 0;
        background: white;
    }
</style>

<div class="container-fluid mt-4">
    <h2 class="mb-3">
        <i class="bi bi-pencil-square me-2"></i>HTMLライブ編集
    </h2>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>使い方:</strong> 
                <ul class="mb-0 mt-2">
                    <li><strong>画像ブラウザ:</strong> 左側の画像をクリックするとエディタに挿入されます。画像にホバーすると拡大プレビューが表示されます</li>
                    <li><strong>HTML/ビジュアル編集:</strong> 中央上部の切替ボタンでモード変更。ビジュアル編集中に画像を選択すると、その場に挿入できます</li>
                    <li><strong>クリック編集:</strong> プレビュー内の要素をクリックすると、左側のエディタで対応するコードが選択されます</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- 保存通知 -->
    <div class="save-indicator">
        <div class="alert alert-success mb-0 shadow">
            <i class="bi bi-check-circle me-2"></i>保存しました！
        </div>
    </div>
    
    <!-- ツールバー -->
    <div class="toolbar mb-3">
        <button class="btn btn-primary" id="saveBtn">
            <i class="bi bi-save me-1"></i>保存
        </button>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-secondary" id="undoBtn" disabled title="元に戻す (Ctrl+Z)">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button class="btn btn-outline-secondary" id="redoBtn" disabled title="やり直し (Ctrl+Y)">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
        <button class="btn btn-outline-secondary" id="reloadBtn">
            <i class="bi bi-arrow-clockwise me-1"></i>再読み込み
        </button>
        <button class="btn btn-outline-warning" id="restoreBackupBtn">
            <i class="bi bi-clock-history me-1"></i>バックアップから復元
        </button>
        <div class="dropdown ms-2">
            <button class="btn btn-outline-info dropdown-toggle" type="button" id="insertAlertDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-chat-left-text me-1"></i>アラート挿入
            </button>
            <ul class="dropdown-menu" aria-labelledby="insertAlertDropdown">
                <li><a class="dropdown-item insert-alert-item" data-alert-type="info" href="#">情報（info）</a></li>
                <li><a class="dropdown-item insert-alert-item" data-alert-type="success" href="#">成功（success）</a></li>
                <li><a class="dropdown-item insert-alert-item" data-alert-type="warning" href="#">注意（warning）</a></li>
                <li><a class="dropdown-item insert-alert-item" data-alert-type="danger" href="#">警告（danger）</a></li>
                <li><a class="dropdown-item insert-alert-item" data-alert-type="secondary" href="#">グレー（secondary）</a></li>
            </ul>
        </div>
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="検索...">
            <button class="btn btn-outline-secondary" id="searchBtn">
                <i class="bi bi-search"></i>
            </button>
            <button class="btn btn-outline-secondary" id="jumpToBtn" title="編集箇所へジャンプ">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
        <div class="form-check form-switch ms-3">
            <input class="form-check-input" type="checkbox" id="autoPreviewSwitch" checked>
            <label class="form-check-label" for="autoPreviewSwitch">自動プレビュー</label>
        </div>
        <span class="text-muted ms-auto" id="historyStatus" title="Undo可能回数 / Redo可能回数">
            <i class="bi bi-clock-history me-1"></i>
            <span id="undoCount">0</span> / <span id="redoCount">0</span>
        </span>
        <span class="text-muted line-numbers" id="lineCount">行数: 0</span>
    </div>
    
    <!-- エディタとプレビュー -->
    <div class="editor-container">
        <!-- 左側: 画像ブラウザ -->
        <div class="image-browser-pane">
            <div class="pane-header">
                <span><i class="bi bi-images"></i>画像ブラウザ</span>
                <button class="btn btn-sm btn-outline-light" id="refreshImagesBtn" title="画像リストを更新">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div class="pane-content">
                <div class="image-browser-content">
                    <div class="image-search">
                        <input type="text" class="form-control form-control-sm" id="imageSearchInput" placeholder="画像を検索...">
                    </div>
                    <div id="imageList">
                        <div class="text-center p-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">読み込み中...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 中央: HTMLエディタ -->
        <div class="editor-pane">
            <div class="pane-header">
                <span><i class="bi bi-code-slash"></i>エディタ</span>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm editor-mode-toggle" role="group" aria-label="Editor mode">
                        <button class="btn btn-light active" id="htmlModeBtn"><i class="bi bi-code-slash me-1"></i>HTML</button>
                        <button class="btn btn-outline-light" id="visualModeBtn"><i class="bi bi-brush me-1"></i>プレビュー編集</button>
                    </div>
                    <small id="editorModeLabel">HTMLモード</small>
                </div>
            </div>
            <div class="pane-content">
                <div id="htmlEditorWrapper">
                    <textarea id="htmlEditor" spellcheck="false"><?php echo $html; ?></textarea>
                </div>
                <div id="visualEditorWrapper">
                    <div class="visual-toolbar">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-secondary" id="visualReloadBtn" title="HTMLを読み込んで編集"><i class="bi bi-download me-1"></i>読み込む</button>
                            <button class="btn btn-outline-primary" id="visualApplyBtn" title="ビジュアル編集をHTMLに反映"><i class="bi bi-upload me-1"></i>HTMLに反映</button>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <button class="btn btn-outline-success" id="visualInsertImageBtn"><i class="bi bi-image me-1"></i>画像を挿入</button>
                        </div>
                    </div>
                    <iframe id="visualEditorFrame" class="visual-frame" sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-modals"></iframe>
                </div>
            </div>
        </div>
        
        <!-- 右側: プレビュー -->
        <div class="preview-pane">
            <div class="pane-header">
                <span><i class="bi bi-eye"></i>リアルタイムプレビュー</span>
                <button class="btn btn-sm btn-outline-light" id="refreshPreviewBtn">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div class="pane-content">
                <iframe id="preview" sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-modals"></iframe>
            </div>
        </div>
    </div>
    
    <!-- 画像プレビューツールチップ -->
    <div class="image-preview-tooltip" id="imagePreviewTooltip">
        <img src="" alt="プレビュー">
        <div class="image-preview-info">
            <div><strong>ファイル名:</strong> <span class="preview-filename"></span></div>
            <div><strong>パス:</strong> <span class="preview-path"></span></div>
        </div>
    </div>
</div>

<!-- バックアップ復元モーダル -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">バックアップから復元</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="backupListContainer">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">読み込み中...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 画像挿入設定モーダル -->
<div class="modal fade" id="imageInsertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image me-2"></i>画像を挿入</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="insertPreviewImage" src="" alt="" style="max-width: 100%; max-height: 200px; border-radius: 8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label">ファイル名</label>
                    <input type="text" class="form-control" id="insertImageName" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">パス</label>
                    <input type="text" class="form-control" id="insertImagePath" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">代替テキスト (alt)</label>
                    <input type="text" class="form-control" id="insertImageAlt" placeholder="画像の説明を入力">
                </div>
                <div class="mb-3">
                    <label class="form-label">CSSクラス</label>
                    <input type="text" class="form-control" id="insertImageClass" value="img-fluid" placeholder="例: img-fluid rounded">
                </div>
                <div class="mb-3">
                    <label class="form-label">挿入位置</label>
                    <select class="form-select" id="insertPosition">
                        <option value="cursor">カーソル位置</option>
                        <option value="line-end">現在の行の最後</option>
                        <option value="new-line">新しい行に挿入</option>
                        <option value="replace">選択範囲を置換</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="confirmInsertBtn">
                    <i class="bi bi-check-lg me-1"></i>挿入
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ジャンプモーダル -->
<div class="modal fade" id="jumpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bullseye me-2"></i>編集箇所へジャンプ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>編集したい箇所を選択すると、エディタ内の該当行にジャンプします
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3"><i class="bi bi-card-heading me-2"></i>モーダル（詳細説明）</h6>
                        <div class="list-group" id="modalList">
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3"><i class="bi bi-list-nested me-2"></i>アコーディオン（大項目）</h6>
                        <div class="list-group" id="accordionList">
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
console.log('✓ JavaScript loading started');

const htmlEditor = document.getElementById('htmlEditor');
const preview = document.getElementById('preview');
const saveBtn = document.getElementById('saveBtn');
const reloadBtn = document.getElementById('reloadBtn');
const restoreBackupBtn = document.getElementById('restoreBackupBtn');
const autoPreviewSwitch = document.getElementById('autoPreviewSwitch');
const refreshPreviewBtn = document.getElementById('refreshPreviewBtn');
const lineCount = document.getElementById('lineCount');
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const jumpToBtn = document.getElementById('jumpToBtn');
const undoBtn = document.getElementById('undoBtn');
const redoBtn = document.getElementById('redoBtn');
const htmlModeBtn = document.getElementById('htmlModeBtn');
const visualModeBtn = document.getElementById('visualModeBtn');
const editorModeLabel = document.getElementById('editorModeLabel');
const htmlEditorWrapper = document.getElementById('htmlEditorWrapper');
const visualEditorWrapper = document.getElementById('visualEditorWrapper');
const visualEditorFrame = document.getElementById('visualEditorFrame');
const visualApplyBtn = document.getElementById('visualApplyBtn');
const visualReloadBtn = document.getElementById('visualReloadBtn');
const visualInsertImageBtn = document.getElementById('visualInsertImageBtn');
const alertInsertItems = document.querySelectorAll('.insert-alert-item');

let updateTimeout = null;
let visualSyncTimeout = null;

// ============ Undo/Redo履歴管理 ============
const MAX_HISTORY = 50; // 最大履歴数
let editHistory = [];
let historyIndex = -1;
let isRestoringHistory = false;
let lastSavedContent = htmlEditor.value;
let isVisualMode = false;

// テキストエリアへカーソル位置に挿入
function insertTextAtCursor(textarea, text) {
    const start = textarea.selectionStart ?? 0;
    const end = textarea.selectionEnd ?? 0;
    const original = textarea.value;
    textarea.value = original.slice(0, start) + text + original.slice(end);
    const newPos = start + text.length;
    textarea.selectionStart = textarea.selectionEnd = newPos;
    textarea.focus();
    updateLineCount();
    saveToHistory(textarea.value);
    if (autoPreviewSwitch.checked) {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(updatePreview, 300);
    }
}

// Bootstrapアラートを挿入
function insertAlertSnippet(type) {
    const message = prompt('アラートに表示するテキストを入力してください', 'お知らせの本文をここに入力');
    if (message === null) return;
    const snippet = `\n<div class="alert alert-${type} mt-3" role="alert">\n  ${message}\n</div>\n`;
    insertTextAtCursor(htmlEditor, snippet);
}

// 行数カウント更新
function updateLineCount() {
    const lines = htmlEditor.value.split('\n').length;
    lineCount.textContent = `行数: ${lines}`;
}

// 編集履歴に追加
function saveToHistory(content) {
    if (isRestoringHistory) return;
    
    // 現在の内容と同じなら保存しない
    if (historyIndex >= 0 && editHistory[historyIndex] === content) {
        return;
    }
    
    // 現在位置より後ろの履歴を削除（新しい編集が入った場合）
    if (historyIndex < editHistory.length - 1) {
        editHistory = editHistory.slice(0, historyIndex + 1);
    }
    
    // 新しい履歴を追加
    editHistory.push(content);
    
    // 最大履歴数を超えたら古いものを削除
    if (editHistory.length > MAX_HISTORY) {
        editHistory.shift();
    } else {
        historyIndex++;
    }
    
    updateHistoryButtons();
}

// Undo/Redoボタンの状態を更新
function updateHistoryButtons() {
    const canUndo = historyIndex > 0;
    const canRedo = historyIndex < editHistory.length - 1;
    
    undoBtn.disabled = !canUndo;
    redoBtn.disabled = !canRedo;
    
    // 履歴カウンター更新
    document.getElementById('undoCount').textContent = historyIndex;
    document.getElementById('redoCount').textContent = editHistory.length - historyIndex - 1;
}

// Undo実行
function performUndo() {
    if (historyIndex <= 0) return;
    
    historyIndex--;
    isRestoringHistory = true;
    
    htmlEditor.value = editHistory[historyIndex];
    
    isRestoringHistory = false;
    updateHistoryButtons();
    updateLineCount();
    
    if (autoPreviewSwitch.checked) {
        updatePreview();
    }
    
    showNotification('元に戻しました', 'info');
}

// Redo実行
function performRedo() {
    if (historyIndex >= editHistory.length - 1) return;
    
    historyIndex++;
    isRestoringHistory = true;
    
    htmlEditor.value = editHistory[historyIndex];
    
    isRestoringHistory = false;
    updateHistoryButtons();
    updateLineCount();
    
    if (autoPreviewSwitch.checked) {
        updatePreview();
    }
    
    showNotification('やり直しました', 'info');
}

// プレビュー更新
function updatePreview() {
    let htmlContent = htmlEditor.value;
    
    // CSSやJSの相対パスを修正するため、base要素を追加
    if (!htmlContent.includes('<base')) {
        htmlContent = htmlContent.replace(
            /<head>/i,
            '<head>\n  <base href="/task/">'
        );
    }
    
    const previewDoc = preview.contentDocument || preview.contentWindow.document;
    if (!previewDoc) {
        console.warn('Preview document not accessible');
        return;
    }
    
    previewDoc.open();
    previewDoc.write(htmlContent);
    previewDoc.close();
    
    // iframe読み込み完了を確実に待つ
    const setupHandlers = () => {
        if (previewDoc.readyState === 'complete') {
            setupPreviewClickHandlers();
        } else {
            setTimeout(setupHandlers, 100);
        }
    };
    
    // loadイベントとreadyStateの両方で確認
    preview.addEventListener('load', setupPreviewClickHandlers, { once: true });
    setTimeout(setupHandlers, 100);
}

// プレビュー内の要素クリックでエディタにジャンプ
function setupPreviewClickHandlers() {
    try {
        const previewDoc = preview.contentDocument || preview.contentWindow.document;
        if (!previewDoc || !previewDoc.body) {
            console.warn('Preview document or body not ready');
            return;
        }
        const previewWin = previewDoc.defaultView || preview.contentWindow;
        
        // 既存のイベントリスナーを削除するため、古いハンドラーをクリア
        // すべての要素にクリックイベントを追加
        const elements = previewDoc.querySelectorAll('*');
        console.log(`Setting up click handlers for ${elements.length} elements`);
        
        let handlerCount = 0;
        elements.forEach(el => {
            // body, html, head, script, styleは除外
            if (['BODY', 'HTML', 'HEAD', 'SCRIPT', 'STYLE', 'BASE', 'META', 'TITLE', 'LINK'].includes(el.tagName)) {
                return;
            }

            // Bootstrapのインタラクティブ要素を除外（トグル、アコーディオン、モーダル）
            const hasBootstrapToggle = el.hasAttribute('data-bs-toggle') || el.hasAttribute('data-bs-dismiss');
            const isAccordionBtn = el.classList.contains('accordion-button');
            const isAccordionHeader = el.classList.contains('accordion-header');
            const isModalCloseBtn = el.classList.contains('btn-close');
            const isModalChrome = el.classList.contains('modal') || el.classList.contains('modal-dialog') || el.classList.contains('modal-content') || el.classList.contains('modal-header') || el.classList.contains('modal-footer');
            const isFormControl = ['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON', 'LABEL'].includes(el.tagName);
            const isCheckbox = el.classList.contains('form-check-input');
            
            if (hasBootstrapToggle || isAccordionBtn || isAccordionHeader || isModalCloseBtn || isModalChrome || isFormControl || isCheckbox) {
                return;
            }
            
            el.style.cursor = 'pointer';
            el.setAttribute('data-clickable', 'true');
            
            // 既存のリスナーを削除してから新しいものを追加
            const clickHandler = function(e) {
                // バブリングで祖先に付いたハンドラが二重発火しないよう、実際にクリックされた要素だけ処理（テキストノードなら親要素で判定）
                const rawTarget = e.target;
                const normalizedTarget = rawTarget?.nodeType === Node.TEXT_NODE ? rawTarget.parentElement : rawTarget;
                if (normalizedTarget !== this) {
                    return;
                }

                const isBootstrapToggle = this.hasAttribute('data-bs-toggle') || this.hasAttribute('data-bs-dismiss');
                const href = this.getAttribute('href');
                const isExternalLink = this.tagName === 'A' && href && !href.startsWith('#');
                const isModalClose = this.classList.contains('btn-close') || this.getAttribute('data-bs-dismiss') === 'modal';
                const inOpenModal = !!this.closest('.modal.show');
                const isModalChrome = this.classList.contains('modal') || this.classList.contains('modal-dialog') || this.classList.contains('modal-content') || this.classList.contains('modal-header') || this.classList.contains('modal-body') || this.classList.contains('modal-footer');

                // 外部リンクだけはプレビュー遷移を止める
                if (isExternalLink) {
                    e.preventDefault();
                }
                
                console.log('Element clicked:', this.tagName, this.id || '', this.className || '');
                
                try {
                    // クリックされた要素の情報を取得
                    const elementInfo = getElementInfo(this);
                    console.log('Element info extracted:', elementInfo);
                    
                    // モーダルのクローズやモーダル枠のクリックはハイライトしない
                    if (isModalClose || (inOpenModal && isModalChrome)) {
                        return;
                    }

                    // エディタ内で検索してハイライト（UI動作を阻害しないため少し後で実行）
                    setTimeout(() => findAndHighlightInEditor(elementInfo), isBootstrapToggle ? 150 : 0);
                } catch (error) {
                    console.error('Error in click handler:', error);
                    console.error('Stack trace:', error.stack);
                }
            };
            
            el.addEventListener('click', clickHandler);
            handlerCount++;
        });
        
        console.log(`✓ Preview click handlers set up successfully (${handlerCount} elements)`);

        // モーダルが閉じられない場合のフォールバック（Bootstrapが効かない場合は強制的に閉じる）
        if (!previewDoc.__modalCloseFallbackAttached) {
            previewDoc.__modalCloseFallbackAttached = true;
            previewDoc.addEventListener('click', function modalCloseFallback(ev) {
                const target = ev.target;
                if (!target) return;
                const isCloseTrigger = target.classList?.contains('btn-close') || target.getAttribute?.('data-bs-dismiss') === 'modal';
                if (!isCloseTrigger) return;
                const modalEl = target.closest('.modal');
                if (!modalEl) return;

                const forceHide = () => {
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                    modalEl.setAttribute('aria-hidden', 'true');
                    modalEl.removeAttribute('aria-modal');
                    const body = previewDoc.body;
                    body?.classList?.remove('modal-open');
                    body?.style?.removeProperty('overflow');
                    body?.style?.removeProperty('padding-right');
                    const backdrops = previewDoc.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(b => b.remove());
                };

                try {
                    const bs = previewWin?.bootstrap || previewWin?.window?.bootstrap;
                    let hidByBs = false;
                    if (bs?.Modal) {
                        const instance = bs.Modal.getInstance(modalEl) || new bs.Modal(modalEl);
                        if (instance?.hide) {
                            instance.hide();
                            hidByBs = true;
                        }
                    }

                    // Bootstrapで閉じられなかった場合に備えて強制クローズ
                    setTimeout(() => {
                        const stillOpen = modalEl.classList.contains('show');
                        if (!hidByBs || stillOpen) {
                            forceHide();
                        }
                    }, 80);
                } catch (err) {
                    console.warn('Modal fallback failed, applying hard close:', err);
                    forceHide();
                }
            }, { capture: true });
        }
    } catch (e) {
        console.error('Preview click handlers setup failed:', e);
    }
}

// 要素の詳細情報を取得
function getElementInfo(element) {
    console.log('getElementInfo called for:', element.tagName);
    
    try {
        const tagName = element.tagName.toLowerCase();
        const id = element.id;
        const classes = Array.from(element.classList);
        const textContent = element.textContent.trim().substring(0, 50);
        
        // 元の親要素のID（動的に移動された要素用）
        const originalParent = element.getAttribute('data-original-parent');
        
        // 親要素の情報も取得（より正確な特定のため）
        let parentInfo = null;
        if (element.parentElement && element.parentElement.tagName !== 'BODY') {
            parentInfo = {
                tagName: element.parentElement.tagName.toLowerCase(),
                id: element.parentElement.id,
                classes: Array.from(element.parentElement.classList)
            };
        }
        
        // originalParentがある場合、それを親情報として使用
        if (originalParent) {
            parentInfo = {
                tagName: 'div',
                id: originalParent,
                classes: []
            };
        }
        
        // 属性を取得
        const attributes = {};
        for (let attr of element.attributes) {
            attributes[attr.name] = attr.value;
        }
        
        const info = {
            tagName,
            id,
            classes,
            textContent,
            parentInfo,
            attributes,
            originalParent  // 追加
        };
        
        console.log('Element info created:', info);
        return info;
    } catch (error) {
        console.error('Error in getElementInfo:', error);
        throw error;
    }
}

// エディタ内で検索してハイライト（改善版）
function findAndHighlightInEditor(elementInfo) {
    console.log('=== findAndHighlightInEditor START ===');
    console.log('elementInfo:', JSON.stringify(elementInfo, null, 2));
    
    try {
        const content = htmlEditor.value;
        const lowerContent = content.toLowerCase();
        
        console.log('HTML content length:', content.length);
        console.log('HTML editor exists:', !!htmlEditor);
        
        // 複数の検索パターンを優先順位順に試す
        const searchPatterns = [];
    
    // 0. originalParent情報がある場合（モーダル等、動的に移動された要素）
    if (elementInfo.originalParent && elementInfo.id) {
        console.log('🔍 Using originalParent search:', elementInfo.originalParent, 'for element:', elementInfo.id);
        // 元の親要素内でIDを検索
        searchPatterns.push({
            pattern: `id="${elementInfo.originalParent}"[\\s\\S]{0,2000}?id="${elementInfo.id}"`,
            priority: 0,
            isRegex: true,
            description: `Original parent search: #${elementInfo.id} in #${elementInfo.originalParent}`
        });
    }
    
    // 1. ID属性で検索（最も正確）
    if (elementInfo.id) {
        searchPatterns.push({
            pattern: `id="${elementInfo.id}"`,
            priority: 1,
            description: `ID: ${elementInfo.id}`
        });
        searchPatterns.push({
            pattern: `id='${elementInfo.id}'`,
            priority: 1,
            description: `ID: ${elementInfo.id}`
        });
    }
    
    // 2. data-bs-target属性（モーダル等）
    if (elementInfo.attributes['data-bs-target']) {
        searchPatterns.push({
            pattern: `data-bs-target="${elementInfo.attributes['data-bs-target']}"`,
            priority: 2,
            description: `Modal target: ${elementInfo.attributes['data-bs-target']}`
        });
    }
    
    // 3. クラス名で検索
    if (elementInfo.classes.length > 0) {
        // 最も特徴的なクラス（長いクラス名や特殊なクラス）を優先
        const uniqueClasses = elementInfo.classes.filter(c => 
            c.length > 5 && !['col-', 'btn-', 'text-', 'bg-', 'me-', 'ms-', 'mt-', 'mb-', 'py-', 'px-', 'd-'].some(prefix => c.startsWith(prefix))
        );
        
        if (uniqueClasses.length > 0) {
            searchPatterns.push({
                pattern: `class="${uniqueClasses[0]}`,
                priority: 3,
                description: `Class: ${uniqueClasses[0]}`
            });
        }
        
        // 複数クラスの組み合わせ
        if (elementInfo.classes.length >= 2) {
            const classPattern = elementInfo.classes.slice(0, 2).join(' ');
            searchPatterns.push({
                pattern: `class="${classPattern}`,
                priority: 4,
                description: `Classes: ${classPattern}`
            });
        }
        
        // 最初のクラスだけでも試す
        if (elementInfo.classes.length > 0) {
            searchPatterns.push({
                pattern: `class="${elementInfo.classes[0]}`,
                priority: 5,
                description: `First class: ${elementInfo.classes[0]}`
            });
        }
    }
    
    // 4. タグ名 + 親要素の組み合わせ
    if (elementInfo.parentInfo && elementInfo.parentInfo.id) {
        searchPatterns.push({
            pattern: `id="${elementInfo.parentInfo.id}"[\s\S]{0,500}?<${elementInfo.tagName}`,
            priority: 6,
            isRegex: true,
            description: `Tag in parent: ${elementInfo.tagName} in #${elementInfo.parentInfo.id}`
        });
    }
    
    // 5. テキストコンテンツで検索
    if (elementInfo.textContent && elementInfo.textContent.length > 10) {
        // 特殊文字をエスケープ
        const escapedText = elementInfo.textContent.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        searchPatterns.push({
            pattern: escapedText.substring(0, 30),
            priority: 7,
            description: `Text: ${elementInfo.textContent.substring(0, 30)}...`
        });
    }
    
    // 6. タグ名のみ（最終手段）
    searchPatterns.push({
        pattern: `<${elementInfo.tagName}`,
        priority: 8,
        description: `Tag: <${elementInfo.tagName}>`
    });
    
    console.log(`Trying ${searchPatterns.length} search patterns...`);
    
    console.log(`Trying ${searchPatterns.length} search patterns...`);
    
    // パターンを順に試す
    for (const {pattern, isRegex, description} of searchPatterns) {
        let index = -1;
        
        console.log(`Trying pattern: "${description}"`);
        
        if (isRegex) {
            const regex = new RegExp(pattern, 'i');
            const match = content.match(regex);
            if (match) {
                index = match.index;
            }
        } else {
            index = lowerContent.indexOf(pattern.toLowerCase());
        }
        
        if (index !== -1) {
            console.log(`✓ Match found with pattern: "${description}" at index ${index}`);
            console.log(`━━━ HTMLエディタに移動します ━━━`);
            highlightCodeInEditor(index, elementInfo.tagName, description);
            return true;
        } else {
            console.log(`✗ No match for pattern: "${description}"`);
        }
    }
    
    // 見つからなかった場合
    console.warn(`Could not find element in editor:`, elementInfo);
    showNotification(`要素が見つかりませんでした: ${elementInfo.tagName}`, 'warning');
    return false;
    
    } catch (error) {
        console.error('Error in findAndHighlightInEditor:', error);
        console.error('Stack:', error.stack);
        throw error;
    }
}

// エディタ内のコードをハイライト（改善版）
function highlightCodeInEditor(index, tagName, description) {
    console.log(`highlightCodeInEditor called: index=${index}, tagName=${tagName}, description=${description}`);
    
    // HTMLエディタラッパーが表示されていることを確認
    const htmlEditorWrapper = document.getElementById('htmlEditorWrapper');
    const visualEditorWrapper = document.getElementById('visualEditorWrapper');
    
    // ビジュアルエディタが表示中の場合は非表示にする
    if (visualEditorWrapper && visualEditorWrapper.style.display !== 'none') {
        console.log('ビジュアルエディタが表示中です。HTMLモードに切り替えます。');
        visualEditorWrapper.style.display = 'none';
        if (htmlEditorWrapper) {
            htmlEditorWrapper.style.display = 'block';
        }
        // HTMLモードボタンを更新
        const htmlModeBtn = document.getElementById('htmlModeBtn');
        const visualModeBtn = document.getElementById('visualModeBtn');
        if (htmlModeBtn) htmlModeBtn.classList.add('active');
        if (visualModeBtn) visualModeBtn.classList.remove('active');
        const editorModeLabel = document.getElementById('editorModeLabel');
        if (editorModeLabel) editorModeLabel.textContent = 'HTMLモード';
    }
    
    // HTMLエディタが非表示の場合は表示
    if (htmlEditorWrapper && htmlEditorWrapper.style.display === 'none') {
        console.log('HTMLエディタが非表示です。表示します。');
        htmlEditorWrapper.style.display = 'block';
    }
    
    highlightCodeInEditorImpl(index, tagName, description);
}

// 実際のハイライト処理
function highlightCodeInEditorImpl(index, tagName, description) {
    console.log(`highlightCodeInEditorImpl called: index=${index}`);
    
    const content = htmlEditor.value;
    
    // タグの開始位置を探す
    let startPos = index;
    let tempPos = startPos;
    while (tempPos > 0 && content[tempPos] !== '<') {
        tempPos--;
        if (tempPos < startPos - 200) break;
    }
    if (content[tempPos] === '<') {
        startPos = tempPos;
    }
    
    // タグ全体を選択する
    let endPos = findClosingTag(content, startPos, tagName);
    
    if (endPos === -1 || endPos - startPos > 5000) {
        endPos = content.indexOf('>', startPos);
        if (endPos !== -1) {
            endPos += 1;
        } else {
            endPos = startPos + 100;
        }
    }
    
    // 行番号を計算
    const lines = content.substring(0, startPos).split('\n');
    const lineNum = lines.length;
    const totalLines = content.split('\n').length;
    
    console.log(`Target: Line ${lineNum}, Char position: ${startPos}-${endPos}`);
    console.log(`Total lines: ${totalLines}`);
    
    // === 文字位置ベースの計算方法 ===
    
    const scrollHeight = htmlEditor.scrollHeight;
    const clientHeight = htmlEditor.clientHeight;
    const totalChars = content.length;
    
    // 文字位置の割合からスクロール位置を計算
    const charRatio = startPos / totalChars;
    const estimatedScroll = scrollHeight * charRatio;
    
    // 選択位置を画面の一番上に表示（調整なし）
    const targetScroll = Math.max(0, estimatedScroll);
    
    console.log(`Total chars: ${totalChars}, Char ratio: ${(charRatio * 100).toFixed(2)}%`);
    console.log(`Estimated scroll: ${estimatedScroll.toFixed(0)}px, Target scroll: ${targetScroll.toFixed(0)}px`);
    
    // 選択とスクロールを実行
    htmlEditor.focus();
    htmlEditor.scrollTop = targetScroll;
    htmlEditor.setSelectionRange(startPos, endPos);
    
    console.log(`✓ Scrolled to ${htmlEditor.scrollTop.toFixed(0)}px`);
    console.log(`✓ Selection set to ${startPos}-${endPos}`);
    
    // 確認
    setTimeout(() => {
        console.log(`After 50ms - Scroll: ${htmlEditor.scrollTop.toFixed(0)}px`);
        console.log(`Selection: start=${htmlEditor.selectionStart}, end=${htmlEditor.selectionEnd}`);
    }, 50);
    
    // 通知を表示
    showNotification(`${lineNum}行目に移動しました: ${description}`, 'success');
    showHighlightIndicator(lineNum, description);
}

// 終了タグを見つける（ネストを考慮）
function findClosingTag(content, startPos, tagName) {
    // 自己完結タグの場合
    const openTagEnd = content.indexOf('>', startPos);
    if (openTagEnd !== -1 && content[openTagEnd - 1] === '/') {
        return openTagEnd + 1;
    }
    
    // 終了タグを探す
    const openTag = `<${tagName}`;
    const closeTag = `</${tagName}>`;
    
    let pos = openTagEnd + 1;
    let depth = 1;
    
    while (pos < content.length && depth > 0) {
        const nextOpen = content.indexOf(openTag, pos);
        const nextClose = content.indexOf(closeTag, pos);
        
        if (nextClose === -1) {
            return -1; // 終了タグが見つからない
        }
        
        if (nextOpen !== -1 && nextOpen < nextClose) {
            // ネストされた同じタグが見つかった
            depth++;
            pos = nextOpen + openTag.length;
        } else {
            // 終了タグが見つかった
            depth--;
            if (depth === 0) {
                return nextClose + closeTag.length;
            }
            pos = nextClose + closeTag.length;
        }
        
        // 無限ループ防止
        if (pos - startPos > 10000) {
            return -1;
        }
    }
    
    return -1;
}

// ハイライト表示インジケーター（改善版）
function showHighlightIndicator(lineNum, description = '') {
    const indicator = document.querySelector('.editor-pane .pane-header small');
    if (indicator) {
        const originalText = indicator.textContent;
        const displayText = description ? `${lineNum}行目: ${description}` : `${lineNum}行目を選択`;
        indicator.textContent = displayText;
        indicator.style.color = '#ffc107';
        indicator.style.fontWeight = 'bold';
        
        // 通知も表示
        showNotification(`${lineNum}行目にジャンプしました`, 'info');
        
        setTimeout(() => {
            indicator.textContent = originalText;
            indicator.style.color = '';
            indicator.style.fontWeight = '';
        }, 3000);
    }
}

// ============ ビジュアル編集モード ============
function setEditorMode(mode) {
    if (mode === 'visual') {
        isVisualMode = true;
        htmlModeBtn.classList.remove('btn-light');
        htmlModeBtn.classList.add('btn-outline-light');
        htmlModeBtn.classList.remove('active');
        visualModeBtn.classList.remove('btn-outline-light');
        visualModeBtn.classList.add('btn-light');
        visualModeBtn.classList.add('active');
        htmlEditorWrapper.style.display = 'none';
        visualEditorWrapper.style.display = 'flex';
        editorModeLabel.textContent = 'プレビュー編集モード';
        loadVisualEditorFromHtml();
    } else {
        // HTMLモードに戻す際、ビジュアル編集内容を反映
        syncVisualToHtml(false);
        isVisualMode = false;
        htmlModeBtn.classList.add('btn-light');
        htmlModeBtn.classList.remove('btn-outline-light');
        htmlModeBtn.classList.add('active');
        visualModeBtn.classList.add('btn-outline-light');
        visualModeBtn.classList.remove('btn-light');
        visualModeBtn.classList.remove('active');
        visualEditorWrapper.style.display = 'none';
        htmlEditorWrapper.style.display = 'block';
        editorModeLabel.textContent = 'HTMLモード';
        if (autoPreviewSwitch.checked) {
            updatePreview();
        }
    }
}

function ensureBaseHref(htmlContent) {
    if (!htmlContent.includes('<base')) {
        return htmlContent.replace(/<head>/i, '<head>\n  <base href="/task/">');
    }
    return htmlContent;
}

function loadVisualEditorFromHtml() {
    let htmlContent = ensureBaseHref(htmlEditor.value);
    const doc = visualEditorFrame.contentDocument || visualEditorFrame.contentWindow.document;
    if (!doc) return;
    doc.open();
    doc.write(htmlContent);
    doc.close();
    if (doc.body) {
        doc.body.setAttribute('contenteditable', 'true');
        doc.body.spellcheck = false;
        doc.body.style.outline = 'none';
        doc.body.addEventListener('input', () => {
            clearTimeout(visualSyncTimeout);
            visualSyncTimeout = setTimeout(() => {
                if (isVisualMode) {
                    syncVisualToHtml();
                }
            }, 500);
        });
    }
}

function syncVisualToHtml(updatePreviewNow = true) {
    if (!isVisualMode) return;
    const doc = visualEditorFrame.contentDocument || visualEditorFrame.contentWindow.document;
    if (!doc || !doc.documentElement) return;
    const serialized = doc.documentElement.outerHTML;
    htmlEditor.value = serialized;
    updateLineCount();
    saveToHistory(htmlEditor.value);
    if (updatePreviewNow && autoPreviewSwitch.checked) {
        updatePreview();
    }
}

function insertImageIntoVisual(path, alt = '', cssClass = 'img-fluid') {
    const doc = visualEditorFrame.contentDocument || visualEditorFrame.contentWindow.document;
    if (!doc || !doc.body) return;
    const img = doc.createElement('img');
    img.src = path;
    img.alt = alt;
    img.className = cssClass;
    const sel = doc.getSelection();
    if (sel && sel.rangeCount > 0) {
        const range = sel.getRangeAt(0);
        range.deleteContents();
        range.insertNode(img);
        range.setStartAfter(img);
        range.collapse(true);
        sel.removeAllRanges();
        sel.addRange(range);
    } else {
        doc.body.appendChild(img);
    }
    syncVisualToHtml();
    showNotification('ビジュアルに画像を挿入しました', 'success');
}

// 自動プレビュー更新（デバウンス付き）
let historyTimeout = null;
htmlEditor.addEventListener('input', function() {
    updateLineCount();
    
    // 履歴保存（デバウンス: 1秒後に保存）
    clearTimeout(historyTimeout);
    historyTimeout = setTimeout(() => {
        saveToHistory(htmlEditor.value);
    }, 1000);
    
    if (autoPreviewSwitch.checked) {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(updatePreview, 500);
    }
});

// 手動プレビュー更新
refreshPreviewBtn.addEventListener('click', updatePreview);

// エディタモード切り替え
htmlModeBtn.addEventListener('click', () => setEditorMode('html'));
visualModeBtn.addEventListener('click', () => setEditorMode('visual'));

// ビジュアルエディタ操作
visualApplyBtn.addEventListener('click', () => {
    syncVisualToHtml();
    if (!isVisualMode) return;
    setEditorMode('html');
});
visualReloadBtn.addEventListener('click', loadVisualEditorFromHtml);
visualInsertImageBtn.addEventListener('click', () => {
    // 画像ブラウザから選んでもらうため、左ペインを利用
    document.getElementById('imageList')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    showNotification('画像を選択してください（クリックで挿入）', 'info');
});

// アラート挿入
alertInsertItems.forEach((item) => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        const type = item.dataset.alertType || 'info';
        insertAlertSnippet(type);
    });
});

// Undo/Redoボタン
undoBtn.addEventListener('click', performUndo);
redoBtn.addEventListener('click', performRedo);

// 保存
saveBtn.addEventListener('click', async function() {
    let htmlContent = htmlEditor.value;
    
    // 保存前にランタイム状態をクリーンアップ
    htmlContent = htmlContent.replace(/<body[^>]*contenteditable[^>]*>/gi, '<body>');
    htmlContent = htmlContent.replace(/<div[^>]*class="modal-backdrop[^>]*><\/div>/gi, '');
    htmlContent = htmlContent.replace(/class="([^"]*\s)?show(\s[^"]*)?"/, 'class="$1$2"');
    htmlContent = htmlContent.replace(/class="\s*"/g, '');
    
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>保存中...';
    
    try {
        const response = await fetch('live_editor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=save_html&html=' + encodeURIComponent(htmlContent)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 保存成功通知
            const indicator = document.querySelector('.save-indicator');
            indicator.style.display = 'block';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);

            // 明示的な完了通知
            alert('✓ 変更が保存されました');
            
            // 保存後の内容を記録
            lastSavedContent = htmlContent;
        } else {
            alert('✗ 保存に失敗しました: ' + (result.error || '不明なエラー'));
        }
    } catch (error) {
        alert('✗ 保存エラー: ' + error.message);
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>保存';
    }
});

// ============ 未保存の変更を検出 ============
// ページを離れる前に未保存の変更があればアラートを表示
window.addEventListener('beforeunload', function(e) {
    const currentContent = htmlEditor.value;
    if (currentContent !== lastSavedContent) {
        // 標準的なメッセージを表示（ブラウザによって異なる）
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// 再読み込み
reloadBtn.addEventListener('click', function() {
    if (confirm('編集内容を破棄して、保存済みのファイルを再読み込みしますか？')) {
        location.reload();
    }
});

// バックアップから復元
restoreBackupBtn.addEventListener('click', async function() {
    const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
    modal.show();
    
    try {
        const response = await fetch('live_editor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_backups'
        });
        
        const result = await response.json();
        const container = document.getElementById('backupListContainer');
        
        if (result.success && result.backups.length > 0) {
            let html = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>注意: 復元前に現在のページは自動でバックアップされます</div>';
            html += '<div class="list-group">';
            
            result.backups.forEach(backup => {
                html += `
                    <button type="button" class="list-group-item list-group-item-action" onclick="restoreFromBackup('${backup.file}')">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><i class="bi bi-clock-history me-2"></i>${backup.time}</h6>
                            <small class="text-muted">${backup.file}</small>
                        </div>
                    </button>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="alert alert-info">利用可能なバックアップがありません</div>';
        }
    } catch (error) {
        document.getElementById('backupListContainer').innerHTML = 
            '<div class="alert alert-danger">エラー: ' + error.message + '</div>';
    }
});

// バックアップから復元実行
async function restoreFromBackup(backupFile) {
    if (!confirm('このバックアップから復元してもよろしいですか？')) {
        return;
    }
    
    try {
        const response = await fetch('live_editor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=restore_backup&backup_file=' + encodeURIComponent(backupFile)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('バックアップから復元しました。ページを再読み込みします。');
            location.reload();
        } else {
            alert('復元に失敗: ' + (result.error || '不明なエラー'));
        }
    } catch (error) {
        alert('通信エラー: ' + error.message);
    }
}

// キーボードショートカット
htmlEditor.addEventListener('keydown', function(e) {
    // Ctrl+S で保存
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveBtn.click();
    }
    // Ctrl+F で検索
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        searchInput.focus();
    }
    // Ctrl+Z でUndo
    if (e.ctrlKey && e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        performUndo();
    }
    // Ctrl+Y または Ctrl+Shift+Z でRedo
    if ((e.ctrlKey && e.key === 'y') || (e.ctrlKey && e.shiftKey && e.key === 'z')) {
        e.preventDefault();
        performRedo();
    }
});

// 検索機能
function searchInEditor() {
    const query = searchInput.value;
    if (!query) return;
    
    const content = htmlEditor.value;
    const lines = content.split('\n');
    
    for (let i = 0; i < lines.length; i++) {
        if (lines[i].toLowerCase().includes(query.toLowerCase())) {
            // 行番号にジャンプ
            const position = lines.slice(0, i).join('\n').length;
            htmlEditor.focus();
            htmlEditor.setSelectionRange(position, position + lines[i].length);
            htmlEditor.scrollTop = (i / lines.length) * htmlEditor.scrollHeight - htmlEditor.clientHeight / 2;
            return;
        }
    }
    alert('見つかりませんでした: ' + query);
}

searchBtn.addEventListener('click', searchInEditor);
searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchInEditor();
    }
});

// ジャンプ機能
jumpToBtn.addEventListener('click', async function() {
    const modal = new bootstrap.Modal(document.getElementById('jumpModal'));
    modal.show();
    
    const content = htmlEditor.value;
    const lines = content.split('\n');
    
    // モーダルを検索
    const modalMatches = [];
    const modalRegex = /<div[^>]+id="(Modal[^"]+)"[^>]*class="[^"]*modal[^"]*"/gi;
    let match;
    while ((match = modalRegex.exec(content)) !== null) {
        const modalId = match[1];
        const lineNum = content.substring(0, match.index).split('\n').length;
        
        // タイトルを探す
        const titleRegex = new RegExp(`id="${modalId}"[\\s\\S]{0,500}?modal-title[^>]*>([^<]+)`, 'i');
        const titleMatch = content.match(titleRegex);
        const title = titleMatch ? titleMatch[1].trim() : modalId;
        
        modalMatches.push({ id: modalId, title: title, line: lineNum });
    }
    
    // アコーディオンを検索
    const accordionMatches = [];
    const accordionRegex = /<div[^>]+class="[^"]*accordion-item[^"]*"[^>]*>/gi;
    while ((match = accordionRegex.exec(content)) !== null) {
        const lineNum = content.substring(0, match.index).split('\n').length;
        
        // タイトルを探す（次のaccordion-buttonを検索）
        const titleRegex = /accordion-button[^>]*>([^<]+)/i;
        const afterContent = content.substring(match.index, match.index + 500);
        const titleMatch = afterContent.match(titleRegex);
        const title = titleMatch ? titleMatch[1].trim() : `アコーディオン ${lineNum}行目`;
        
        accordionMatches.push({ title: title, line: lineNum });
    }
    
    // モーダルリスト表示
    const modalList = document.getElementById('modalList');
    if (modalMatches.length > 0) {
        modalList.innerHTML = modalMatches.map(m => 
            `<button type="button" class="list-group-item list-group-item-action" onclick="jumpToLine(${m.line})">
                <div class="d-flex justify-content-between align-items-center">
                    <span><strong>${m.title}</strong></span>
                    <small class="text-muted">${m.line}行目</small>
                </div>
            </button>`
        ).join('');
    } else {
        modalList.innerHTML = '<div class="text-muted p-3">モーダルが見つかりません</div>';
    }
    
    // アコーディオンリスト表示
    const accordionList = document.getElementById('accordionList');
    if (accordionMatches.length > 0) {
        accordionList.innerHTML = accordionMatches.map(a => 
            `<button type="button" class="list-group-item list-group-item-action" onclick="jumpToLine(${a.line})">
                <div class="d-flex justify-content-between align-items-center">
                    <span>${a.title}</span>
                    <small class="text-muted">${a.line}行目</small>
                </div>
            </button>`
        ).join('');
    } else {
        accordionList.innerHTML = '<div class="text-muted p-3">アコーディオンが見つかりません</div>';
    }
});

// 指定行にジャンプ
function jumpToLine(lineNum) {
    const lines = htmlEditor.value.split('\n');
    const position = lines.slice(0, lineNum - 1).join('\n').length;
    
    htmlEditor.focus();
    htmlEditor.setSelectionRange(position, position + (lines[lineNum - 1] || '').length);
    htmlEditor.scrollTop = ((lineNum - 1) / lines.length) * htmlEditor.scrollHeight - htmlEditor.clientHeight / 2;
    
    // モーダルを閉じる
    bootstrap.Modal.getInstance(document.getElementById('jumpModal')).hide();
}

// エディタへのドラッグ&ドロップ設定
function setupEditorDragDrop() {
    const editor = document.getElementById('htmlEditor');
    
    editor.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('drag-over');
    });
    
    editor.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('drag-over');
    });
    
    editor.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('drag-over');
        
        try {
            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
            if (data.path && data.name) {
                // ドロップ位置にカーソルを移動
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // スクロール位置を考慮
                const scrollTop = this.scrollTop;
                const scrollLeft = this.scrollLeft;
                
                // おおよその位置を計算（完全に正確ではないが、近い位置に配置）
                const charWidth = 8; // おおよその文字幅
                const lineHeight = 22; // おおよその行の高さ
                const lineNum = Math.floor((y + scrollTop) / lineHeight);
                const charNum = Math.floor((x + scrollLeft) / charWidth);
                
                const lines = this.value.split('\n');
                let position = 0;
                for (let i = 0; i < lineNum && i < lines.length; i++) {
                    position += lines[i].length + 1; // +1 for newline
                }
                position += Math.min(charNum, lines[lineNum] ? lines[lineNum].length : 0);
                
                // カーソル位置を設定
                this.selectionStart = position;
                this.selectionEnd = position;
                this.focus();
                
                // 挿入モーダルを表示
                pendingImageData = data;
                document.getElementById('insertPreviewImage').src = data.url;
                document.getElementById('insertImageName').value = data.name;
                document.getElementById('insertImagePath').value = data.path;
                document.getElementById('insertImageAlt').value = data.name.replace(/\.[^.]+$/, '');
                document.getElementById('insertImageClass').value = 'img-fluid';
                document.getElementById('insertPosition').value = 'cursor';
                
                const modal = new bootstrap.Modal(document.getElementById('imageInsertModal'));
                modal.show();
            }
        } catch (err) {
            console.error('Drop error:', err);
            showNotification('ドロップに失敗しました', 'danger');
        }
    });
}

// 初期化
updateLineCount();
updatePreview();
loadImages();
setupEditorDragDrop();

// 初期状態を履歴に保存
saveToHistory(htmlEditor.value);
updateHistoryButtons();

// ============ 画像ブラウザ機能 ============
let allImages = null;
const imagePreviewTooltip = document.getElementById('imagePreviewTooltip');
const imageSearchInput = document.getElementById('imageSearchInput');
const refreshImagesBtn = document.getElementById('refreshImagesBtn');

// 画像リストを読み込み
async function loadImages() {
    try {
        const response = await fetch('live_editor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_images'
        });
        
        const result = await response.json();
        
        if (result.success) {
            allImages = result.images;
            displayImages(allImages);
        } else {
            document.getElementById('imageList').innerHTML = 
                '<div class="alert alert-danger m-2">画像の読み込みに失敗しました</div>';
        }
    } catch (error) {
        document.getElementById('imageList').innerHTML = 
            '<div class="alert alert-danger m-2">エラー: ' + error.message + '</div>';
    }
}

// 画像を表示
function displayImages(images, searchQuery = '') {
    const container = document.getElementById('imageList');
    let html = '';
    
    // フォルダごとに表示
    const folders = [
        { key: 'img', title: 'img フォルダ', icon: 'folder' },
        { key: 'uploads', title: 'uploads フォルダ', icon: 'cloud-upload' },
        { key: 'root', title: 'ルート', icon: 'house' }
    ];
    
    folders.forEach(folder => {
        const folderImages = images[folder.key] || [];
        
        // 検索フィルタリング
        const filteredImages = searchQuery 
            ? folderImages.filter(img => img.name.toLowerCase().includes(searchQuery.toLowerCase()))
            : folderImages;
        
        if (filteredImages.length === 0 && !searchQuery) return;
        
        html += `
            <div class="folder-section">
                <div class="folder-title" data-folder="${folder.key}">
                    <span><i class="bi bi-${folder.icon} me-2"></i>${folder.title} (${filteredImages.length})</span>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="folder-content" data-folder="${folder.key}">
        `;
        
        if (filteredImages.length > 0) {
            html += '<div class="image-grid">';
            filteredImages.forEach(img => {
                html += `
                    <div class="image-item" 
                         draggable="true"
                         data-path="${img.path}" 
                         data-url="${img.url}"
                         data-name="${img.name}"
                         data-size="${img.size}">
                        <img src="${img.url}" alt="${img.name}" loading="lazy">
                        <div class="image-item-name" title="${img.name}">${img.name}</div>
                    </div>
                `;
            });
            html += '</div>';
        } else {
            html += '<div class="no-images">画像が見つかりません</div>';
        }
        
        html += `
                </div>
            </div>
        `;
    });
    
    if (html === '') {
        html = '<div class="no-images"><i class="bi bi-images me-2"></i>画像がありません</div>';
    }
    
    container.innerHTML = html;
    
    // イベントハンドラーを設定
    setupImageBrowserEvents();
}

// 画像ブラウザのイベントハンドラー
function setupImageBrowserEvents() {
    // フォルダの開閉
    document.querySelectorAll('.folder-title').forEach(title => {
        title.addEventListener('click', function() {
            const folder = this.dataset.folder;
            const content = document.querySelector(`.folder-content[data-folder="${folder}"]`);
            const icon = this.querySelector('.bi-chevron-down, .bi-chevron-right');
            
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                icon.className = 'bi bi-chevron-down';
            } else {
                content.classList.add('collapsed');
                icon.className = 'bi bi-chevron-right';
            }
        });
    });
    
    // 画像アイテムのイベント
    document.querySelectorAll('.image-item').forEach(item => {
        // クリック: 挿入モーダルを表示
        item.addEventListener('click', function() {
            showImageInsertModal(this, isVisualMode ? 'visual' : 'html');
        });
        
        // ドラッグ開始
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', JSON.stringify({
                path: this.dataset.path,
                name: this.dataset.name,
                url: this.dataset.url
            }));
            this.classList.add('dragging');
        });
        
        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
        
        // ホバー: プレビュー表示
        item.addEventListener('mouseenter', function(e) {
            showImagePreview(this, e);
        });
        
        item.addEventListener('mousemove', function(e) {
            updateImagePreviewPosition(e);
        });
        
        item.addEventListener('mouseleave', function() {
            hideImagePreview();
        });
    });
}

// 画像挿入設定モーダルを表示
let pendingImageData = null;
let pendingImageTarget = 'html';

function showImageInsertModal(imageItem, targetMode = null) {
    const path = imageItem.dataset.path;
    const name = imageItem.dataset.name;
    const url = imageItem.dataset.url;
    
    pendingImageData = { path, name, url };
    pendingImageTarget = targetMode || (isVisualMode ? 'visual' : 'html');
    
    document.getElementById('insertPreviewImage').src = url;
    document.getElementById('insertImageName').value = name;
    document.getElementById('insertImagePath').value = path;
    document.getElementById('insertImageAlt').value = name.replace(/\.[^.]+$/, ''); // 拡張子を除く
    document.getElementById('insertImageClass').value = 'img-fluid';
    
    const positionSelect = document.getElementById('insertPosition');
    if (pendingImageTarget === 'visual') {
        positionSelect.value = 'cursor';
        positionSelect.disabled = true;
    } else {
        positionSelect.disabled = false;
        // 選択範囲があるかチェック
        const editor = document.getElementById('htmlEditor');
        const hasSelection = editor.selectionStart !== editor.selectionEnd;
        if (hasSelection) {
            positionSelect.value = 'replace';
        } else {
            positionSelect.value = 'cursor';
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('imageInsertModal'));
    modal.show();
}

// 画像挿入を確定
document.getElementById('confirmInsertBtn').addEventListener('click', function() {
    if (!pendingImageData) return;
    
    const path = pendingImageData.path;
    const alt = document.getElementById('insertImageAlt').value;
    const cssClass = document.getElementById('insertImageClass').value;
    const position = document.getElementById('insertPosition').value;
    
    if (pendingImageTarget === 'visual') {
        insertImageIntoVisual(path, alt, cssClass);
    } else {
        insertImageIntoEditor(path, alt, cssClass, position);
    }
    
    // モーダルを閉じる
    bootstrap.Modal.getInstance(document.getElementById('imageInsertModal')).hide();
    pendingImageData = null;
    pendingImageTarget = 'html';
});

// 画像をエディタに挿入
function insertImageIntoEditor(path, alt = '', cssClass = 'img-fluid', position = 'cursor') {
    const imageTag = `<img src="${path}" alt="${alt}" class="${cssClass}">`;
    
    const editor = document.getElementById('htmlEditor');
    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    const text = editor.value;
    
    let newText, newPos;
    
    switch (position) {
        case 'cursor':
            // カーソル位置に挿入
            newText = text.substring(0, start) + imageTag + text.substring(end);
            newPos = start + imageTag.length;
            break;
            
        case 'line-end':
            // 現在の行の最後に挿入
            const lines = text.substring(0, start).split('\n');
            const currentLineStart = text.substring(0, start).lastIndexOf('\n') + 1;
            const currentLineEnd = text.indexOf('\n', start);
            const lineEnd = currentLineEnd === -1 ? text.length : currentLineEnd;
            newText = text.substring(0, lineEnd) + imageTag + text.substring(lineEnd);
            newPos = lineEnd + imageTag.length;
            break;
            
        case 'new-line':
            // 新しい行に挿入
            const beforeNewLine = text.substring(0, start);
            const afterNewLine = text.substring(end);
            const needsNewlineBefore = beforeNewLine && !beforeNewLine.endsWith('\n');
            const needsNewlineAfter = afterNewLine && !afterNewLine.startsWith('\n');
            newText = beforeNewLine + 
                     (needsNewlineBefore ? '\n' : '') + 
                     imageTag + 
                     (needsNewlineAfter ? '\n' : '') + 
                     afterNewLine;
            newPos = start + (needsNewlineBefore ? 1 : 0) + imageTag.length;
            break;
            
        case 'replace':
            // 選択範囲を置換
            newText = text.substring(0, start) + imageTag + text.substring(end);
            newPos = start + imageTag.length;
            break;
            
        default:
            newText = text.substring(0, start) + imageTag + text.substring(end);
            newPos = start + imageTag.length;
    }
    
    editor.value = newText;
    editor.selectionStart = newPos;
    editor.selectionEnd = newPos;
    editor.focus();
    
    // 挿入位置にスクロール
    const lines = newText.substring(0, newPos).split('\n');
    const lineNum = lines.length;
    const scrollRatio = lineNum / newText.split('\n').length;
    editor.scrollTop = scrollRatio * editor.scrollHeight - editor.clientHeight / 2;
    
    // 行数更新
    updateLineCount();
    
    // プレビューを更新
    if (autoPreviewSwitch.checked) {
        updatePreview();
    }
    
    // 成功メッセージ
    showNotification('画像を挿入しました', 'success');
}

// 画像プレビューを表示
function showImagePreview(item, event) {
    const url = item.dataset.url;
    const name = item.dataset.name;
    const path = item.dataset.path;
    const size = parseInt(item.dataset.size);
    const sizeKB = (size / 1024).toFixed(1);
    
    const tooltip = imagePreviewTooltip;
    tooltip.querySelector('img').src = url;
    tooltip.querySelector('.preview-filename').textContent = name;
    tooltip.querySelector('.preview-path').textContent = path + ' (' + sizeKB + ' KB)';
    
    tooltip.style.display = 'block';
    updateImagePreviewPosition(event);
}

// プレビュー位置を更新
function updateImagePreviewPosition(event) {
    const tooltip = imagePreviewTooltip;
    const offsetX = 20;
    const offsetY = 20;
    
    let left = event.clientX + offsetX;
    let top = event.clientY + offsetY;
    
    // 画面外に出ないように調整
    const tooltipRect = tooltip.getBoundingClientRect();
    if (left + tooltipRect.width > window.innerWidth) {
        left = event.clientX - tooltipRect.width - offsetX;
    }
    if (top + tooltipRect.height > window.innerHeight) {
        top = event.clientY - tooltipRect.height - offsetY;
    }
    
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
}

// プレビューを非表示
function hideImagePreview() {
    imagePreviewTooltip.style.display = 'none';
}

// 画像検索
imageSearchInput.addEventListener('input', function() {
    if (allImages) {
        displayImages(allImages, this.value);
    }
});

// 画像リスト更新
refreshImagesBtn.addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    loadImages().finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
    });
});

// 通知表示
function showNotification(message, type = 'success') {
    const indicator = document.querySelector('.save-indicator');
    const alert = indicator.querySelector('.alert');
    
    alert.className = `alert alert-${type} mb-0 shadow`;
    alert.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}`;
    
    indicator.style.display = 'block';
    setTimeout(() => {
        indicator.style.display = 'none';
    }, 2000);
}

// ============ ここから既存のコード ============


function createQuillEditor(selector, initialHtml = '') {
    const quill = new Quill(selector, {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ color: [] }, { background: [] }],
                    [{ align: [] }],
                    ['link', 'image', 'table'],
                    ['clean']
                ],
                handlers: {
                    image: function() { selectLocalImage(quill); },
                    table: function() { insertBootstrapTable(quill); }
                }
            }
        },
        placeholder: 'ここに内容を入力してください...'
    });

    const toolbar = quill.getModule('toolbar');
    const tableBtn = toolbar?.container.querySelector('.ql-table');
    if (tableBtn) {
        tableBtn.innerHTML = '<i class="bi bi-table"></i>';
    }

    quill.root.innerHTML = initialHtml || '<p></p>';
    return quill;
}

async function uploadImageFile(file) {
    if (!file) return null;
    if (file.size > maxUploadBytes) {
        alert('画像サイズが大きすぎます。最大 ' + Math.floor(maxUploadBytes / 1024 / 1024) + 'MB までです。');
        return null;
    }
    const fd = new FormData();
    fd.append('action', 'upload_image');
    fd.append('image', file);
    try {
        const res = await fetch('live_editor.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success && json.url) {
            return json.url;
        }
        alert('アップロードに失敗しました: ' + (json.error || '不明なエラー'));
    } catch (e) {
        alert('通信エラー: ' + e.message);
    }
    return null;
}

function selectLocalImage(quill) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = async () => {
        const file = input.files?.[0];
        const url = await uploadImageFile(file);
        if (url) {
            const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
            quill.insertEmbed(range.index, 'image', url, 'user');
            quill.setSelection(range.index + 1);
        }
    };
    input.click();
}

function insertBootstrapTable(quill) {
    const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
    const tableHtml = `
<table class="table table-bordered">
  <thead>
    <tr><th>ヘッダー1</th><th>ヘッダー2</th><th>ヘッダー3</th></tr>
  </thead>
  <tbody>
    <tr><td>セル1</td><td>セル2</td><td>セル3</td></tr>
  </tbody>
</table>`;
    quill.clipboard.dangerouslyPasteHTML(range.index, tableHtml);
}

async function fetchModalContent(modalId) {
        const fd = new FormData();
        fd.append('action', 'get_modal_content');
        fd.append('modal_id', modalId);
        try {
                const res = await fetch('live_editor.php', { method: 'POST', body: fd });
                const json = await res.json();
                if (json.success) return json;
                console.warn('get_modal_content failed:', json.error);
        } catch (e) {
                console.error('get_modal_content error:', e);
        }
        return null;
}

function reloadPreview(forceBust = false) {
    const frame = document.getElementById('siteFrame');
    if (!frame) return;
    const base = '/task/index.html';
    const bust = forceBust ? ('?live_reload=' + Date.now()) : (frame.dataset.lastBust || '');
    const url = forceBust ? base + bust : frame.src;
    frame.dataset.lastBust = bust;
    frame.src = forceBust ? url : frame.src;
}

function showLoadWarning(show) {
    const banner = document.querySelector('.load-warning');
    if (!banner) return;
    banner.style.display = show ? 'block' : 'none';
}

function validatePreviewContent(doc) {
    // メインアコーディオンが見えない、または本文が極端に短い場合は警告
    const hasAccordion = !!doc.querySelector('#accordion');
    const textLength = (doc.body && doc.body.innerText || '').trim().length;
    const looksEmpty = textLength < 200;
    showLoadWarning(!hasAccordion || looksEmpty);
}

window.addEventListener('load', function() {
    
    const addAccordionBtn = document.getElementById('addAccordionBtn');
    const addModalBtn = document.getElementById('addModalBtn');
    const restoreBackupBtn = document.getElementById('restoreBackupBtn');
    const listModalsBtn = document.getElementById('listModalsBtn');
    const reloadFrameBtn = document.getElementById('reloadFrameBtn');
    const warningReloadBtn = document.getElementById('warningReloadBtn');

    if (addAccordionBtn) {
        addAccordionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addAccordion();
        });
    }

    if (addModalBtn) {
        addModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addModal();
        });
    }

    if (restoreBackupBtn) {
        restoreBackupBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showRestoreBackupDialog();
        });
    }

    if (listModalsBtn) {
        listModalsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showModalQuickPicker();
        });
    }

    if (reloadFrameBtn) {
        reloadFrameBtn.addEventListener('click', function(e) {
            e.preventDefault();
            reloadPreview(true);
        });
    }

    if (warningReloadBtn) {
        warningReloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            reloadPreview(true);
        });
    }
});

function editItemText(btn) {
    const container = btn.closest('.edit-mode');
    const modalId = container.dataset.modalId;
    const currentText = container.textContent.trim();
    
    currentEdit = {
        type: 'item-text',
        modalId: modalId,
        container: container,
        originalValue: currentText
    };
    
    document.getElementById('editModalTitle').textContent = 'チェック項目を編集';
    document.getElementById('editContent').innerHTML = `
        <label class="form-label">項目名</label>
        <input type="text" class="form-control" id="editInput" value="${currentText}">
    `;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function editModalTitle(btn) {
    const container = btn.closest('.edit-mode');
    const modalId = container.dataset.modalId;
    const currentTitle = container.querySelector('.kokuban').textContent.trim();
    
    currentEdit = {
        type: 'modal-title',
        modalId: modalId,
        container: container,
        originalValue: currentTitle
    };
    
    document.getElementById('editModalTitle').textContent = 'モーダルタイトルを編集';
    document.getElementById('editContent').innerHTML = `
        <label class="form-label">タイトル</label>
        <input type="text" class="form-control form-control-lg" id="editInput" value="${currentTitle}">
    `;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function editModalBody(btn) {
    const container = btn.closest('.edit-mode');
    const modalId = container.dataset.modalId;
    const currentBody = container.querySelector('.modal-content-preview').innerHTML;
    
    currentEdit = {
        type: 'modal-body',
        modalId: modalId,
        container: container,
        originalValue: currentBody
    };
    
    document.getElementById('editModalTitle').textContent = 'モーダル内容を編集';
    document.getElementById('editContent').innerHTML = `
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="simple-tab" data-bs-toggle="tab" data-bs-target="#simple-edit" type="button">
                    <i class="bi bi-pencil-square me-1"></i>かんたん編集
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="html-tab" data-bs-toggle="tab" data-bs-target="#html-edit" type="button">
                    <i class="bi bi-code-slash me-1"></i>HTML編集
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="simple-edit" role="tabpanel">
                <div id="quill-editor" style="min-height: 300px;"></div>
            </div>
            <div class="tab-pane fade" id="html-edit" role="tabpanel">
                <textarea class="form-control font-monospace" id="htmlEditor" rows="15">${currentBody}</textarea>
            </div>
        </div>
    `;
    
    const quill = createQuillEditor('#quill-editor', currentBody);
    
    // タブ切り替え時の同期
    document.getElementById('simple-tab').addEventListener('click', () => {
        quill.root.innerHTML = document.getElementById('htmlEditor').value;
    });
    document.getElementById('html-tab').addEventListener('click', () => {
        document.getElementById('htmlEditor').value = quill.root.innerHTML;
    });
    
    currentEdit.quill = quill;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// ============ 保存処理（すべての編集タイプに対応） ============

const originalSaveEdit = async function() {
    if (!currentEdit) return;
    
    let newValue;
    if (currentEdit.type === 'modal-body') {
        newValue = currentEdit.quill.root.innerHTML;
        const htmlEditor = document.getElementById('htmlEditor');
        if (htmlEditor) {
            newValue = document.querySelector('.tab-pane.active').id === 'html-edit' 
                ? htmlEditor.value 
                : newValue;
        }
    } else {
        newValue = document.getElementById('editInput').value;
    }
    
    const formData = new FormData();
    formData.append('modal_id', currentEdit.modalId);
    
    if (currentEdit.type === 'item-text') {
        formData.append('action', 'update_item_text');
        formData.append('text', newValue);
    } else if (currentEdit.type === 'modal-title') {
        formData.append('action', 'update_modal_title');
        formData.append('title', newValue);
    } else if (currentEdit.type === 'modal-body') {
        formData.append('action', 'update_modal_body');
        formData.append('body', newValue);
    }
    
    try {
        const response = await fetch('live_editor.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            const indicator = document.querySelector('.save-indicator');
            indicator.style.display = 'block';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
        }
    } catch (error) {
        alert('保存に失敗しました: ' + error.message);
    }
};

async function saveEdit() {
    if (currentEdit && currentEdit.type === 'composite') {
        const modalId = currentEdit.modalId;
        const linkText = document.getElementById('edit_link_text').value;
        const title = document.getElementById('edit_modal_title_composite').value;
        const body = document.getElementById('h-pane').classList.contains('active')
            ? document.getElementById('h-editor').value
            : (quillComposite ? quillComposite.root.innerHTML : '');

        // 1) タイトル更新
        let ok = true;
        try {
            let fd1 = new FormData();
            fd1.append('action', 'update_modal_title');
            fd1.append('modal_id', modalId);
            fd1.append('title', title);
            let r1 = await fetch('live_editor.php', { method:'POST', body: fd1 });
            let j1 = await r1.json();
            ok = ok && j1.success;
        } catch(e) { ok = false; }

        // 2) 本文更新
        try {
            let fd2 = new FormData();
            fd2.append('action', 'update_modal_body');
            fd2.append('modal_id', modalId);
            fd2.append('body', body);
            let r2 = await fetch('live_editor.php', { method:'POST', body: fd2 });
            let j2 = await r2.json();
            ok = ok && j2.success;
        } catch(e) { ok = false; }

        // 3) リンクテキスト更新（トリガーがある場合のみ）
        if (currentEdit.linkEl) {
            try {
                let fd3 = new FormData();
                fd3.append('action', 'update_item_text');
                fd3.append('modal_id', modalId);
                fd3.append('text', linkText);
                let r3 = await fetch('live_editor.php', { method:'POST', body: fd3 });
                let j3 = await r3.json();
                ok = ok && j3.success;
            } catch(e) { ok = false; }
        }

        if (ok) {
            // iframe 内DOMを更新
            try {
                // リンク
                if (currentEdit.linkEl) currentEdit.linkEl.textContent = linkText;
                // タイトル
                const modalNode = currentEdit.frameDoc.getElementById(modalId);
                if (modalNode) {
                    const t = modalNode.querySelector('.modal-title');
                    if (t) t.textContent = title;
                    const b = modalNode.querySelector('.modal-body');
                    if (b) b.innerHTML = body;
                }
            } catch {}

            const indicator = document.querySelector('.save-indicator');
            indicator.style.display = 'block';
            setTimeout(() => indicator.style.display = 'none', 2000);

            console.log('✓ Composite save successful, reloading preview...');
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            
            // iframeを確実にリロード
            setTimeout(() => {
                const frame = document.getElementById('siteFrame');
                if (frame && frame.contentWindow) {
                    frame.contentWindow.location.reload(true);
                }
            }, 500);
        } else {
            alert('一部の更新に失敗しました。');
        }
        return;
    }
    if (currentEdit && currentEdit.type === 'free-node') {
        const xpath = currentEdit.xpath;
        const html = document.getElementById('h2-pane').classList.contains('active')
            ? currentEdit.h.value
            : currentEdit.q.root.innerHTML;
        try {
            const fd = new FormData();
            fd.append('action', 'update_by_xpath');
            fd.append('xpath', xpath);
            fd.append('html', html);
            const r = await fetch('live_editor.php', { method:'POST', body: fd });
            const j = await r.json();
            if (j.success) {
                const indicator = document.querySelector('.save-indicator');
                indicator.style.display = 'block';
                setTimeout(() => indicator.style.display = 'none', 2000);
                console.log('✓ Free-node save successful, reloading preview...');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                // iframeを確実にリロード
                setTimeout(() => {
                    const frame = document.getElementById('siteFrame');
                    if (frame && frame.contentWindow) {
                        frame.contentWindow.location.reload(true);
                    }
                }, 500);
            } else {
                alert('更新に失敗: ' + (j.error || '')); 
            }
        } catch (e) { alert('通信エラー: ' + e.message); }
        return;
    }
    if (currentEdit && currentEdit.type === 'free-img') {
        const xpath = currentEdit.xpath;
        const src = document.getElementById('img_src').value;
        const alt = document.getElementById('img_alt').value;
        try {
            // src更新
            let fd1 = new FormData();
            fd1.append('action', 'update_attr_by_xpath');
            fd1.append('xpath', xpath);
            fd1.append('attr', 'src');
            fd1.append('value', src);
            let r1 = await fetch('live_editor.php', { method:'POST', body: fd1 });
            let j1 = await r1.json();
            let ok = j1.success;

            // alt更新
            let fd2 = new FormData();
            fd2.append('action', 'update_attr_by_xpath');
            fd2.append('xpath', xpath);
            fd2.append('attr', 'alt');
            fd2.append('value', alt);
            let r2 = await fetch('live_editor.php', { method:'POST', body: fd2 });
            let j2 = await r2.json();
            ok = ok && j2.success;

            if (ok) {
                const indicator = document.querySelector('.save-indicator');
                indicator.style.display = 'block';
                setTimeout(() => indicator.style.display = 'none', 2000);
                console.log('✓ Free-img save successful, reloading preview...');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                
                // iframeを確実にリロード
                setTimeout(() => {
                    const frame = document.getElementById('siteFrame');
                    if (frame && frame.contentWindow) {
                        frame.contentWindow.location.reload(true);
                    }
                }, 500);
            } else {
                alert('画像の更新に失敗しました。');
            }
        } catch (e) { alert('通信エラー: ' + e.message); }
        return;
    }
    if (currentEdit && currentEdit.type === 'free-link') {
        const xpath = currentEdit.xpath;
        const text = document.getElementById('link_text').value;
        const href = document.getElementById('link_href').value;
        const target = document.getElementById('link_target').value;
        try {
            // テキスト更新
            let fd1 = new FormData();
            fd1.append('action', 'update_by_xpath');
            fd1.append('xpath', xpath);
            fd1.append('html', escapeHtml(text));
            let r1 = await fetch('live_editor.php', { method:'POST', body: fd1 });
            let j1 = await r1.json();
            let ok = j1.success;

            // href更新
            let fd2 = new FormData();
            fd2.append('action', 'update_attr_by_xpath');
            fd2.append('xpath', xpath);
            fd2.append('attr', 'href');
            fd2.append('value', href);
            let r2 = await fetch('live_editor.php', { method:'POST', body: fd2 });
            let j2 = await r2.json();
            ok = ok && j2.success;

            // target更新
            let fd3 = new FormData();
            fd3.append('action', 'update_attr_by_xpath');
            fd3.append('xpath', xpath);
            fd3.append('attr', 'target');
            fd3.append('value', target);
            let r3 = await fetch('live_editor.php', { method:'POST', body: fd3 });
            let j3 = await r3.json();
            ok = ok && j3.success;

            if (ok) {
                const indicator = document.querySelector('.save-indicator');
                indicator.style.display = 'block';
                setTimeout(() => indicator.style.display = 'none', 2000);
                console.log('✓ Free-link save successful, reloading preview...');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                
                // iframeを確実にリロード
                setTimeout(() => {
                    const frame = document.getElementById('siteFrame');
                    if (frame && frame.contentWindow) {
                        frame.contentWindow.location.reload(true);
                    }
                }, 500);
            } else {
                alert('リンクの更新に失敗しました。');
            }
        } catch (e) { alert('通信エラー: ' + e.message); }
        return;
    }
    // 既存の単体編集はそのまま
    return originalSaveEdit();
}

// ============ ここから iframe 連携（実ページに編集ボタンを挿入） ============
let iframeLoaded = false;
document.addEventListener('DOMContentLoaded', () => {
    const frame = document.getElementById('siteFrame');
    const freeSwitch = document.getElementById('freeEditSwitch');
    if (!frame) return;

    // iframe 読み込み後の処理
    function setupFrameEditButtons() {
        try {
            const doc = frame.contentWindow.document;
            // 既存の編集ボタンを一旦除去（再読み込み時の重複防止）
            Array.from(doc.querySelectorAll('.cms-edit-btn')).forEach(el => el.remove());

            // a[data-bs-toggle="modal"] に編集ボタンを付与
            const links = doc.querySelectorAll('a[data-bs-toggle="modal"][data-bs-target^="#Modal"]');
            links.forEach(a => {
                const modalId = (a.getAttribute('data-bs-target') || '').replace('#', '');
                if (!modalId) return;
                const badge = doc.createElement('a');
                badge.className = 'cms-edit-btn';
                badge.title = '編集';
                badge.innerHTML = '✎';
                badge.href = 'javascript:void(0)';
                badge.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openCompositeEditor(doc, a, modalId);
                });
                a.insertAdjacentElement('afterend', badge);
            });

            // モーダル自体にも編集ボタンを付与
            injectModalEditButtons(doc);

            // アコーディオン削除ボタンを注入
            injectAccordionDeleteButtons(doc);

            // 自由編集モード注入
            const isFreeModeEnabled = freeSwitch && freeSwitch.checked;
            injectFreeEdit(doc, isFreeModeEnabled);

            // 読み込みチェック
            validatePreviewContent(doc);
            iframeLoaded = true;
        } catch (e) {
            console.error('iframe injection error', e);
            showLoadWarning(true);
        }
    }

    // iframe 読み込みイベント
    frame.addEventListener('load', setupFrameEditButtons);

    // 自由編集モードのトグル
    if (freeSwitch) {
        freeSwitch.addEventListener('change', () => {
            if (!iframeLoaded || !frame.contentWindow) return;
            try {
                const doc = frame.contentWindow.document;
                // 自由編集モードの再注入（スイッチの状態に基づく）
                injectFreeEdit(doc, freeSwitch.checked);
            } catch (e) {
                console.error('freeSwitch change error', e);
            }
        });
    }

    // 再読み込み後も自由編集モードを保持
    const originalReload = window.reloadPreview || (() => {});
    window.reloadPreview = function(forceBust) {
        originalReload(forceBust);
        // 再読み込み後、自由編集モードが有効なら再注入
        setTimeout(() => {
            if (freeSwitch && freeSwitch.checked && iframeLoaded) {
                try {
                    const doc = frame.contentWindow.document;
                    injectFreeEdit(doc, true);
                } catch (e) {
                    console.warn('Auto-reinject freeEdit after reload failed', e);
                }
            }
        }, 500);
    };
});

let quillComposite;
async function openCompositeEditor(doc, linkEl, modalId) {
    // 取得: リンクテキスト
    let linkText = linkEl ? (linkEl.textContent || '').trim() : '';
    // モーダル要素
    const modalNode = doc.getElementById(modalId);
    let titleText = '';
    let bodyHtml = '';
    if (modalNode) {
        const titleNode = modalNode.querySelector('.modal-title');
        titleText = titleNode ? titleNode.textContent.trim() : '';
        const bodyNode = modalNode.querySelector('.modal-body');
        if (bodyNode) bodyHtml = bodyNode.innerHTML;
    }

    // 最新ファイルから上書き（iframeが古い場合に備える）
    const latest = await fetchModalContent(modalId);
    if (latest && latest.success) {
        titleText = latest.title || titleText;
        bodyHtml = latest.body || bodyHtml;
        if (latest.link_text) linkText = latest.link_text;
    }

    // モーダルUI組み立て
    document.getElementById('editModalTitle').textContent = '項目を編集';
    document.getElementById('editContent').innerHTML = `
        <div class="mb-3">
            <label class="form-label">リンクテキスト</label>
            <input type="text" class="form-control" id="edit_link_text" value="${escapeHtml(linkText)}">
        </div>
        <div class="mb-3">
            <label class="form-label">モーダルタイトル</label>
            <input type="text" class="form-control" id="edit_modal_title_composite" value="${escapeHtml(titleText)}">
        </div>
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="q-tab" data-bs-toggle="tab" data-bs-target="#q-pane" type="button">
                    かんたん編集
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="h-tab" data-bs-toggle="tab" data-bs-target="#h-pane" type="button">
                    HTML編集
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-pane" type="button">
                    <i class="bi bi-eye me-1"></i>プレビュー
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="q-pane" role="tabpanel">
                <div id="q-editor" style="min-height: 300px;"></div>
            </div>
            <div class="tab-pane fade" id="h-pane" role="tabpanel">
                <textarea class="form-control font-monospace" id="h-editor" rows="14"></textarea>
            </div>
            <div class="tab-pane fade" id="preview-pane" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong id="preview-title-display">${escapeHtml(titleText)}</strong>
                    </div>
                    <div class="card-body" id="preview-body-display" style="min-height: 300px;">
                        ${bodyHtml}
                    </div>
                </div>
            </div>
        </div>
    `;

    // Quill 初期化
    quillComposite = createQuillEditor('#q-editor', bodyHtml);
    document.getElementById('h-editor').value = bodyHtml;
    
    // リアルタイムプレビュー更新
    function updateCompositePreview() {
        const title = document.getElementById('edit_modal_title_composite').value;
        const body = document.querySelector('#h-pane').classList.contains('active')
            ? document.getElementById('h-editor').value
            : quillComposite.root.innerHTML;
        document.getElementById('preview-title-display').textContent = title;
        document.getElementById('preview-body-display').innerHTML = body;
    }
    
    // タイトル変更時
    document.getElementById('edit_modal_title_composite').addEventListener('input', updateCompositePreview);
    
    // Quill変更時
    quillComposite.on('text-change', updateCompositePreview);
    
    // HTMLエディタ変更時
    document.getElementById('h-editor').addEventListener('input', updateCompositePreview);
    
    // タブ切り替え時の同期
    document.getElementById('h-tab').addEventListener('click', () => {
        document.getElementById('h-editor').value = quillComposite.root.innerHTML;
        updateCompositePreview();
    });
    document.getElementById('q-tab').addEventListener('click', () => {
        quillComposite.root.innerHTML = document.getElementById('h-editor').value;
        updateCompositePreview();
    });
    document.getElementById('preview-tab').addEventListener('click', updateCompositePreview);

    currentEdit = {
        type: 'composite',
        modalId,
        frameDoc: doc,
        linkEl: linkEl || null
    };

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function escapeHtml(str) {
    return (str || '').replace(/[&<>\"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));
}

// ---------- モーダル（本体）への編集ボタン注入 ----------
function injectModalEditButtons(doc) {
    const modals = doc.querySelectorAll('.modal[id]');
    modals.forEach(modal => {
        if (modal.querySelector('.cms-edit-modal-btn')) return;
        const header = modal.querySelector('.modal-header') || modal;
        header.style.position = 'relative';
        const btn = doc.createElement('a');
        btn.className = 'cms-edit-btn cms-edit-modal-btn';
        btn.style.position = 'absolute';
        btn.style.top = '8px';
        btn.style.right = '8px';
        btn.textContent = '✎';
        btn.title = 'このモーダルを編集';
        const trigger = doc.querySelector(`[data-bs-target="#${modal.id}"]`);
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openCompositeEditor(doc, trigger || null, modal.id);
        });
        header.appendChild(btn);
    });
}

// ---------- モーダル一覧ピッカー ----------
function collectModalTriggers(doc) {
    const triggers = Array.from(doc.querySelectorAll('[data-bs-toggle="modal"][data-bs-target]'));
    const mapped = [];
    triggers.forEach(t => {
        const target = t.getAttribute('data-bs-target') || '';
        const modalId = target.replace('#', '');
        if (!modalId) return;
        const label = (t.textContent || t.getAttribute('aria-label') || '').trim();
        mapped.push({ modalId, label: label || modalId, trigger: t });
    });
    // 重複をユニークに
    const seen = new Set();
    return mapped.filter(item => {
        if (seen.has(item.modalId)) return false;
        seen.add(item.modalId);
        return true;
    });
}

function showModalQuickPicker() {
    const frame = document.getElementById('siteFrame');
    if (!frame || !frame.contentWindow || !frame.contentWindow.document) {
        alert('プレビューの読み込みを確認してください。');
        return;
    }
    const doc = frame.contentWindow.document;
    const items = collectModalTriggers(doc);

    document.getElementById('editModalTitle').textContent = 'モーダルを選択して編集';
    if (!items.length) {
        document.getElementById('editContent').innerHTML = `
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>モーダルのトリガーが見つかりませんでした。ページを確認してください。
            </div>`;
        new bootstrap.Modal(document.getElementById('editModal')).show();
        return;
    }

    let html = '<div class="list-group">';
    items.forEach(item => {
        html += `
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-modal-id="${item.modalId}">
                <span><i class="bi bi-window me-2 text-primary"></i>${escapeHtml(item.label)}</span>
                <span class="badge bg-light text-dark">#${item.modalId}</span>
            </button>`;
    });
    html += '</div>';
    html += '<p class="text-muted small mt-2 mb-0">クリックすると対象モーダルのタイトル・本文・リンクをまとめて編集できます。</p>';

    document.getElementById('editContent').innerHTML = html;
    document.querySelectorAll('#editContent .list-group-item').forEach(btn => {
        btn.addEventListener('click', () => {
            const mid = btn.getAttribute('data-modal-id');
            const trigger = doc.querySelector(`[data-bs-target="#${mid}"]`);
            openCompositeEditor(doc, trigger || null, mid);
        });
    });

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// ---------- アコーディオン削除ボタンの注入 ----------
function injectAccordionDeleteButtons(doc) {
    // 既存の削除ボタンを削除
    Array.from(doc.querySelectorAll('.accordion-delete-btn')).forEach(btn => btn.remove());
    
    // 各アコーディオンアイテムに削除ボタンを追加
    const accordionItems = doc.querySelectorAll('.accordion-item');
    accordionItems.forEach((item, index) => {
        const header = item.querySelector('.accordion-header');
        if (!header) return;
        
        // 削除ボタンを作成
        const deleteBtn = doc.createElement('button');
        deleteBtn.className = 'btn btn-sm btn-danger accordion-delete-btn';
        deleteBtn.style.position = 'absolute';
        deleteBtn.style.right = '10px';
        deleteBtn.style.top = '50%';
        deleteBtn.style.transform = 'translateY(-50%)';
        deleteBtn.style.zIndex = '10000';
        deleteBtn.style.pointerEvents = 'auto';
        deleteBtn.innerHTML = '<i class="bi bi-trash"></i>';
        deleteBtn.title = 'このアコーディオンを削除';
        
        // 親要素を相対位置に設定
        header.style.position = 'relative';
        header.style.zIndex = '1';  // ヘッダーもz-indexを設定
        
        // クリック時の処理
        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            if (confirm('このアコーディオンを削除してもよろしいですか？')) {
                deleteAccordionByIndex(index);
            }
        });
        
        header.appendChild(deleteBtn);
    });
}

// アコーディオン削除
// アコーディオン削除（インデックスベース）
async function deleteAccordionByIndex(index) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete_accordion_by_index');
        fd.append('accordion_index', index);
        
        const r = await fetch('live_editor.php', { method: 'POST', body: fd });
        const text = await r.text();
        
        let j;
        try {
            j = JSON.parse(text);
        } catch (e) {
            alert('予期しない応答: ' + text);
            return;
        }
        
        if (j.success) {
            alert('アコーディオンを削除しました。ページを再読み込みします。');
            location.reload();
        } else {
            alert('削除に失敗: ' + (j.error || '不明なエラー'));
        }
    } catch (e) {
        alert('通信エラー: ' + e.message);
    }
}

// アコーディオン削除（古い関数、互換性のため）
async function deleteAccordion(accordionId) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete_accordion');
        fd.append('accordion_id', accordionId);
        
        const r = await fetch('live_editor.php', { method: 'POST', body: fd });
        const text = await r.text();
        
        let j;
        try {
            j = JSON.parse(text);
        } catch (e) {
            alert('予期しない応答: ' + text);
            return;
        }
        
        if (j.success) {
            alert('アコーディオンを削除しました。ページを再読み込みします。');
            location.reload();
        } else {
            alert('削除に失敗: ' + (j.error || '不明なエラー'));
        }
    } catch (e) {
        alert('通信エラー: ' + e.message);
    }
}

// ---------- バックアップから復元 ----------
async function showRestoreBackupDialog() {
    try {
        // バックアップ一覧を取得
        const fd = new FormData();
        fd.append('action', 'get_backups');
        
        const r = await fetch('live_editor.php', { method: 'POST', body: fd });
        const text = await r.text();
        
        let j;
        try {
            j = JSON.parse(text);
        } catch (e) {
            console.error('showRestoreBackupDialog: JSON parse error =', e);
            alert('予期しない応答: ' + text);
            return;
        }
        
        console.log('showRestoreBackupDialog: backups =', j.backups);
        
        if (!j.success || !j.backups || j.backups.length === 0) {
            alert('利用可能なバックアップがありません');
            return;
        }
        
        // ダイアログを表示
        document.getElementById('editModalTitle').textContent = 'バックアップから復元';
        let html = '<div><p class="mb-3"><i class="bi bi-exclamation-triangle text-warning me-2"></i><strong>注意：</strong> 復元前に現在のページは自動でバックアップされます</p>';
        html += '<label class="form-label">復元するバックアップを選択：</label>';
        html += '<div class="list-group">';
        
        j.backups.forEach(backup => {
            html += `<button type="button" class="list-group-item list-group-item-action" onclick="restoreBackup('${backup.file}')">
                <i class="bi bi-clock-history me-2"></i>
                <strong>${backup.time}</strong>
                <small class="text-muted d-block">${backup.file}</small>
            </button>`;
        });
        
        html += '</div></div>';
        document.getElementById('editContent').innerHTML = html;
        
        new bootstrap.Modal(document.getElementById('editModal')).show();
    } catch (e) {
        alert('エラー: ' + e.message);
    }
}

async function restoreBackup(backupFile) {
    if (!confirm('このバックアップから復元してもよろしいですか？\n\n現在のページは自動でバックアップされます。')) {
        return;
    }
    
    try {
        const fd = new FormData();
        fd.append('action', 'restore_backup');
        fd.append('backup_file', backupFile);
        
        const r = await fetch('live_editor.php', { method: 'POST', body: fd });
        const text = await r.text();
        
        let j;
        try {
            j = JSON.parse(text);
        } catch (e) {
            alert('予期しない応答: ' + text);
            return;
        }
        
        if (j.success) {
            alert('バックアップから復元しました。ページを再読み込みします。');
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            location.reload();
        } else {
            alert('復元に失敗: ' + (j.error || '不明なエラー'));
        }
    } catch (e) {
        alert('通信エラー: ' + e.message);
    }
}

// ---------- ネストされたアコーディオンをチェック ----------
async function checkNestedAccordions() {
    try {
        const fd = new FormData();
        fd.append('action', 'check_nested_accordions');
        
        const r = await fetch('live_editor.php', { method: 'POST', body: fd });
        const j = await r.json();
        
        if (j.success && j.found > 0) {
            const message = `⚠️ 警告\n\nアコーディオン内に誤ってネストされたアコーディオンを${j.found}個検出し、自動的に削除しました。\n\n削除されたID: ${j.removed.join(', ')}\n\nバックアップが作成されています。`;
            alert(message);
            location.reload();
        } else if (!j.success) {
            console.error('ネストチェックエラー:', j.error);
        }
    } catch (e) {
        console.error('checkNestedAccordions error:', e);
    }
}

// ---------- アコーディオン追加 ----------
async function addAccordion() {
    const title = prompt('新しいアコーディオンのタイトルを入力してください:', '新しいアコーディオン');
    if (!title) {
        return;
    }
    
    try {
        const fd = new FormData();
        fd.append('action', 'add_accordion');
        fd.append('title', title);
        
        const r = await fetch('live_editor.php', { method: 'POST', body: fd });
        const text = await r.text();
        console.log('[addAccordion] Response:', text);
        
        let j;
        try {
            j = JSON.parse(text);
        } catch (e) {
            console.error('[addAccordion] JSON parse error:', e);
            console.error('[addAccordion] Response text:', text);
            alert('予期しない応答（F12コンソールを確認）: ' + text.substring(0, 200));
            return;
        }
        
        if (j.success) {
            alert('アコーディオンを追加しました。ページを再読み込みします。');
            location.reload();
        } else {
            alert('追加に失敗: ' + (j.error || '不明なエラー'));
        }
    } catch (e) {
        console.error('addAccordion: error =', e);
        alert('通信エラー: ' + e.message);
    }
}

// ---------- モーダル追加 ----------
async function addModal() {
    const frame = document.getElementById('siteFrame');
    if (!frame) return;
    
    document.getElementById('editModalTitle').textContent = '新しいモーダルを追加';
    document.getElementById('editContent').innerHTML = `
        <div class="mb-3">
            <label class="form-label">リンクテキスト（チェック項目名）</label>
            <input type="text" class="form-control" id="new_link_text" value="新しいチェック項目">
        </div>
        <div class="mb-3">
            <label class="form-label">モーダルタイトル</label>
            <input type="text" class="form-control" id="new_modal_title" value="新しいモーダル">
        </div>
        <div class="mb-3">
            <label class="form-label">追加先アコーディオン（オプション）</label>
            <select class="form-select" id="new_target_accordion">
                <option value="">選択してください</option>
            </select>
            <small class="text-muted">アコーディオンを選択すると、そのアコーディオン内に項目が追加されます</small>
        </div>
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="qn-tab" data-bs-toggle="tab" data-bs-target="#qn-pane" type="button">かんたん編集</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="hn-tab" data-bs-toggle="tab" data-bs-target="#hn-pane" type="button">HTML編集</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="previewn-tab" data-bs-toggle="tab" data-bs-target="#previewn-pane" type="button">
                    <i class="bi bi-eye me-1"></i>プレビュー
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="qn-pane" role="tabpanel">
                <div id="qn-editor" style="min-height: 260px;"></div>
            </div>
            <div class="tab-pane fade" id="hn-pane" role="tabpanel">
                <textarea class="form-control font-monospace" id="hn-editor" rows="12"></textarea>
            </div>
            <div class="tab-pane fade" id="previewn-pane" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong id="previewn-title-display">新しいモーダル</strong>
                    </div>
                    <div class="card-body" id="previewn-body-display" style="min-height: 260px;">
                        <p>ここに内容を入力してください</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // アコーディオン一覧を取得
    try {
        const doc = frame.contentWindow.document;
        const accordions = doc.querySelectorAll('.accordion[id]');
        const select = document.getElementById('new_target_accordion');
        accordions.forEach((acc, i) => {
            const btn = acc.querySelector('.accordion-button');
            const text = btn ? btn.textContent.trim() : `アコーディオン ${i+1}`;
            const opt = document.createElement('option');
            opt.value = acc.id;
            opt.textContent = text;
            select.appendChild(opt);
        });
    } catch (e) {}
    
    const q = createQuillEditor('#qn-editor', '<p>ここに内容を入力してください</p>');
    const h = document.getElementById('hn-editor');
    h.value = '<p>ここに内容を入力してください</p>';
    
    // リアルタイムプレビュー更新
    function updateNewModalPreview() {
        const title = document.getElementById('new_modal_title').value;
        const body = document.querySelector('#hn-pane').classList.contains('active') ? h.value : q.root.innerHTML;
        document.getElementById('previewn-title-display').textContent = title;
        document.getElementById('previewn-body-display').innerHTML = body;
    }
    
    document.getElementById('new_modal_title').addEventListener('input', updateNewModalPreview);
    q.on('text-change', updateNewModalPreview);
    h.addEventListener('input', updateNewModalPreview);
    
    document.getElementById('hn-tab').addEventListener('click', ()=> {
        h.value = q.root.innerHTML;
        updateNewModalPreview();
    });
    document.getElementById('qn-tab').addEventListener('click', ()=> {
        q.root.innerHTML = h.value;
        updateNewModalPreview();
    });
    document.getElementById('previewn-tab').addEventListener('click', updateNewModalPreview);
    
    currentEdit = { type: 'add-modal', q, h };
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// saveEditにadd-modal対応を追加
const originalSaveEdit2 = saveEdit;
saveEdit = async function() {
    if (currentEdit && currentEdit.type === 'add-modal') {
        const linkText = document.getElementById('new_link_text').value;
        const title = document.getElementById('new_modal_title').value;
        const body = document.getElementById('hn-pane').classList.contains('active')
            ? currentEdit.h.value
            : currentEdit.q.root.innerHTML;
        const targetAccordion = document.getElementById('new_target_accordion').value;
        
        try {
            const fd = new FormData();
            fd.append('action', 'add_modal');
            fd.append('link_text', linkText);
            fd.append('modal_title', title);
            fd.append('modal_body', body);
            fd.append('target_accordion', targetAccordion);
            const r = await fetch('live_editor.php', { method: 'POST', body: fd });
            const j = await r.json();
            
            if (j.success) {
                alert('モーダルを追加しました。ページを再読み込みします。');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                location.reload();
            } else {
                alert('追加に失敗: ' + (j.error || ''));
            }
        } catch (e) {
            alert('通信エラー: ' + e.message);
        }
        return;
    }
    return originalSaveEdit2();
}

// ---------- 自由編集（任意のテキスト/画像） ----------
function injectFreeEdit(doc, enable) {
    // まず既存マーカーを除去
    Array.from(doc.querySelectorAll('[data-cms-free]')).forEach(el => {
        el.removeAttribute('data-cms-free');
        const b = el.querySelector(':scope > .cms-edit-btn');
        if (b) b.remove();
        el.style.outline = '';
        el.onmouseenter = null;
        el.onmouseleave = null;
    });
    if (!enable) return;

    // 対象: ブロック系テキスト + 見出し + ボタンの見出し + 画像
    const selectors = [
        'p', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        '.accordion-button', '.kokuban', 'a:not([data-bs-toggle="modal"])',
        'img'
    ];
    const targets = doc.querySelectorAll(selectors.join(','));
    targets.forEach(el => {
        // 目立つアウトライン
        el.setAttribute('data-cms-free', '1');
        el.onmouseenter = () => { el.style.outline = '2px dashed #ffc107'; };
        el.onmouseleave = () => { el.style.outline = ''; };

        const btn = doc.createElement('a');
        btn.className = 'cms-edit-btn';
        btn.title = 'この要素を編集';
        btn.href = 'javascript:void(0)';
        btn.innerHTML = '✎';
        btn.addEventListener('click', (e) => {
            e.preventDefault(); e.stopPropagation();
            if (el.tagName.toLowerCase() === 'img') {
                openImageEditor(el);
            } else if (el.tagName.toLowerCase() === 'a') {
                openLinkEditor(el);
            } else {
                openNodeEditor(el);
            }
        });
        // 見出しや段落は直後に付与、画像は後ろに
        if (el.tagName.toLowerCase() === 'img') {
            el.insertAdjacentElement('afterend', btn);
        } else {
            el.appendChild(btn);
        }
    });
}

function getXPathForElement(el) {
    const idx = (sib, name) => {
        let i = 1;
        for (let s = sib.previousSibling; s; s = s.previousSibling) {
            if (s.nodeType === 1 && s.nodeName === name) i++;
        }
        return i;
    };
    const segs = [];
    for (let n = el; n && n.nodeType === 1; n = n.parentNode) {
        if (n.id) {
            segs.unshift(`//*[@id="${n.id}"]`);
            break;
        }
        const i = idx(n, n.nodeName);
        segs.unshift(`/${n.nodeName.toLowerCase()}[${i}]`);
    }
    return segs.join('');
}

function openNodeEditor(el) {
    const xpath = getXPathForElement(el);
    const currentHtml = el.innerHTML;
    document.getElementById('editModalTitle').textContent = '要素を編集';
    document.getElementById('editContent').innerHTML = `
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="q2-tab" data-bs-toggle="tab" data-bs-target="#q2-pane" type="button">かんたん編集</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="h2-tab" data-bs-toggle="tab" data-bs-target="#h2-pane" type="button">HTML編集</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="preview2-tab" data-bs-toggle="tab" data-bs-target="#preview2-pane" type="button">
                    <i class="bi bi-eye me-1"></i>プレビュー
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="q2-pane" role="tabpanel">
                <div id="q2-editor" style="min-height: 260px;"></div>
            </div>
            <div class="tab-pane fade" id="h2-pane" role="tabpanel">
                <textarea class="form-control font-monospace" id="h2-editor" rows="12"></textarea>
            </div>
            <div class="tab-pane fade" id="preview2-pane" role="tabpanel">
                <div class="card">
                    <div class="card-body" id="preview2-body-display" style="min-height: 260px;"></div>
                </div>
            </div>
        </div>
    `;
    const q = createQuillEditor('#q2-editor', currentHtml);
    const h = document.getElementById('h2-editor');
    h.value = currentHtml;
    
    // リアルタイムプレビュー更新
    function updateNodePreview() {
        const body = document.querySelector('#h2-pane').classList.contains('active') ? h.value : q.root.innerHTML;
        document.getElementById('preview2-body-display').innerHTML = body;
    }
    
    q.on('text-change', updateNodePreview);
    h.addEventListener('input', updateNodePreview);
    
    document.getElementById('h2-tab').addEventListener('click', ()=> {
        h.value = q.root.innerHTML;
        updateNodePreview();
    });
    document.getElementById('q2-tab').addEventListener('click', ()=> {
        q.root.innerHTML = h.value;
        updateNodePreview();
    });
    document.getElementById('preview2-tab').addEventListener('click', updateNodePreview);    document.getElementById('preview2-tab').addEventListener('click', updateNodePreview);

    currentEdit = { type:'free-node', xpath, el, q, h };
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function openImageEditor(img) {
    const xpath = getXPathForElement(img);
    const src = img.getAttribute('src') || '';
    const alt = img.getAttribute('alt') || '';
    document.getElementById('editModalTitle').textContent = '画像を編集';
    document.getElementById('editContent').innerHTML = `
        <div class="mb-3">
            <label class="form-label">画像URL（src）</label>
            <input type="text" class="form-control" id="img_src" value="${escapeHtml(src)}">
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="img_upload_btn">
                <i class="bi bi-upload me-1"></i>画像をアップロードして反映
            </button>
            <div class="form-text">5MBまでの jpg / png / gif / webp / svg に対応</div>
        </div>
        <div class="mb-3">
            <label class="form-label">代替テキスト（alt）</label>
            <input type="text" class="form-control" id="img_alt" value="${escapeHtml(alt)}">
        </div>
    `;

    const uploadBtn = document.getElementById('img_upload_btn');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', () => {
            const picker = document.createElement('input');
            picker.type = 'file';
            picker.accept = 'image/*';
            picker.onchange = async () => {
                const file = picker.files?.[0];
                const url = await uploadImageFile(file);
                if (url) {
                    document.getElementById('img_src').value = url;
                }
            };
            picker.click();
        });
    }

    currentEdit = { type:'free-img', xpath, el: img };
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function openLinkEditor(a) {
    const xpath = getXPathForElement(a);
    const href = a.getAttribute('href') || '';
    const text = a.textContent || '';
    const target = a.getAttribute('target') || '';
    document.getElementById('editModalTitle').textContent = 'リンクを編集';
    document.getElementById('editContent').innerHTML = `
        <div class="mb-3">
            <label class="form-label">リンクテキスト</label>
            <input type="text" class="form-control" id="link_text" value="${escapeHtml(text)}">
        </div>
        <div class="mb-3">
            <label class="form-label">リンク先URL（href）</label>
            <input type="text" class="form-control" id="link_href" value="${escapeHtml(href)}">
        </div>
        <div class="mb-3">
            <label class="form-label">ターゲット（_blank等）</label>
            <input type="text" class="form-control" id="link_target" value="${escapeHtml(target)}" placeholder="空欄で同じウィンドウ">
        </div>
    `;
    currentEdit = { type:'free-link', xpath, el: a };
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

console.log('✓ JavaScript fully loaded - all functions defined');
</script>

<?php render_admin_footer(); ?>
