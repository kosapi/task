<?php
// Last modified: 2026-01-04 15:38:30
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

// AJAX リクエスト処理（使用状況取得）
if (isset($_GET['ajax']) && $_GET['ajax'] === 'usage') {
    header('Content-Type: application/json');
    
    $filename = $_GET['filename'] ?? '';
    $folder = $_GET['folder'] ?? '';
    
    if ($filename && $folder) {
        $usage = check_image_usage($filename, $folder);
        echo json_encode(['usage' => $usage]);
    } else {
        echo json_encode(['error' => 'Invalid parameters']);
    }
    exit;
}

$success_message = '';
$error_message = '';

// 画像の使用状況をチェックする関数
function check_image_usage($filename, $folder) {
    $index_file = dirname(__DIR__) . '/index.html';
    if (!file_exists($index_file)) {
        return [];
    }
    
    $content = file_get_contents($index_file);
    $usage = [];
    
    // URLエンコードされたファイル名も生成
    $encoded_filename = rawurlencode($filename);
    
    // ファイル名自体がURLエンコードされている可能性もチェック
    $decoded_filename = rawurldecode($filename);
    $is_encoded = ($decoded_filename !== $filename);
    
    // パターンを構築（フォルダによって変わる）
    $patterns = [];
    
    if ($folder === 'root') {
        // ルート直下の画像パターン
        $patterns = [
            // 引用符あり
            '/"' . preg_quote($filename, '/') . '"/',
            '/\'' . preg_quote($filename, '/') . '\'/',
            // URLエンコード版
            '/"' . preg_quote($encoded_filename, '/') . '"/',
            '/\'' . preg_quote($encoded_filename, '/') . '\'/',
            // 引用符なし（src=filename>のパターン）
            '/src=' . preg_quote($filename, '/') . '[>\s]/',
            '/href=' . preg_quote($filename, '/') . '[>\s]/',
            '/src=' . preg_quote($encoded_filename, '/') . '[>\s]/',
            '/href=' . preg_quote($encoded_filename, '/') . '[>\s]/',
        ];
        
        // ファイル名がエンコード済みの場合、デコード版も検索
        if ($is_encoded) {
            $patterns[] = '/"' . preg_quote($decoded_filename, '/') . '"/';
            $patterns[] = '/\'' . preg_quote($decoded_filename, '/') . '\'/';
        }
    } elseif ($folder === 'img') {
        // imgフォルダの画像パターン - strpos()で単純な部分一致検索に変更
        // 正規表現を使わずに文字列検索
        $search_strings = [];
        
        // 1. ファイル名そのまま（img/ファイル名）
        $search_strings[] = 'img/' . $filename;
        
        // 2. ファイル名のURLエンコード版（img/%E3%82%B9...）
        if ($encoded_filename !== $filename) {
            $search_strings[] = 'img/' . $encoded_filename;
        }
        
        // 3. ファイル名が既にエンコード済みの場合、デコード版も検索
        if ($is_encoded) {
            $search_strings[] = 'img/' . $decoded_filename;
            
            // デコード後、再エンコード
            $re_encoded = rawurlencode($decoded_filename);
            if ($re_encoded !== $filename && $re_encoded !== $encoded_filename) {
                $search_strings[] = 'img/' . $re_encoded;
            }
        }
        
        // 4. パス全体がエンコード（img%2F... または img%2f...）
        $search_strings[] = 'img%2F' . $filename;
        $search_strings[] = 'img%2f' . $filename;
        if ($encoded_filename !== $filename) {
            $search_strings[] = 'img%2F' . $encoded_filename;
            $search_strings[] = 'img%2f' . $encoded_filename;
        }
    } else {
        // uploadsフォルダの画像パターン
        $patterns = [
            '/uploads\/' . preg_quote($filename, '/') . '/i',
            '/uploads\/' . preg_quote($encoded_filename, '/') . '/i',
            '/uploads%2F' . preg_quote($filename, '/') . '/i',
            '/uploads%2[Ff]' . preg_quote($filename, '/') . '/i',
            '/uploads%2F' . preg_quote($encoded_filename, '/') . '/i',
        ];
        
        if ($is_encoded) {
            $patterns[] = '/uploads\/' . preg_quote($decoded_filename, '/') . '/i';
            $patterns[] = '/uploads\/' . preg_quote(rawurlencode($decoded_filename), '/') . '/i';
        }
    }
    
    $lines = explode("\n", $content);
    
    // imgフォルダの場合は文字列検索、それ以外は正規表現
    if ($folder === 'img' && isset($search_strings)) {
        foreach ($lines as $line_num => $line) {
            foreach ($search_strings as $search_str) {
                if (stripos($line, $search_str) !== false) {
                    $usage[] = [
                        'line' => $line_num + 1,
                        'content' => trim($line)
                    ];
                    break;
                }
            }
        }
    } else {
        foreach ($lines as $line_num => $line) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $usage[] = [
                        'line' => $line_num + 1,
                        'content' => trim($line)
                    ];
                    break;
                }
            }
        }
    }
    
    return $usage;
}

// 画像一覧を取得（root、img、uploadsの3箇所から）
$image_files = [];
$allowed_exts = ALLOWED_EXTENSIONS;

// ルート直下から取得
$root_dir = dirname(__DIR__);
$root_files = glob($root_dir . '/*');
foreach ($root_files as $file) {
    if (is_file($file)) {
        $ext = get_file_extension($file);
        $filename = basename($file);
        // ファビコンや特定のファイルは除外（PHPファイル、HTMLファイルなど）
        if (in_array($ext, $allowed_exts) && 
            !in_array($ext, ['php', 'html', 'css', 'js']) &&
            !is_dir($file)) {
            $image_files[] = [
                'path' => $file,
                'name' => $filename,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'url' => '/task/' . $filename,
                'folder' => 'root',
                'folder_label' => 'ルート',
                'deletable' => true,
                'movable' => true
            ];
        }
    }
}

// uploadsフォルダから取得
$upload_files = glob(UPLOADS_DIR . '/*');
foreach ($upload_files as $file) {
    if (is_file($file)) {
        $ext = get_file_extension($file);
        if (in_array($ext, $allowed_exts)) {
            $image_files[] = [
                'path' => $file,
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file),
                'url' => '/task/uploads/' . basename($file),
                'folder' => 'uploads',
                'folder_label' => 'アップロード',
                'deletable' => true,
                'movable' => false
            ];
        }
    }
}

// imgフォルダから取得
$img_dir = dirname(__DIR__) . '/img';
if (is_dir($img_dir)) {
    $img_files = glob($img_dir . '/*');
    foreach ($img_files as $file) {
        if (is_file($file)) {
            $ext = get_file_extension($file);
            if (in_array($ext, $allowed_exts)) {
                $image_files[] = [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                    'url' => '/task/img/' . basename($file),
                    'folder' => 'img',
                    'folder_label' => 'imgフォルダ',
                    'deletable' => true,
                    'movable' => false
                ];
            }
        }
    }
}

// 更新日時でソート
usort($image_files, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

// POSTリクエストのログ記録
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('==== POSTリクエスト受信 ====');
    error_log('URL: ' . $_SERVER['REQUEST_URI']);
    error_log('$_FILES isset: ' . (isset($_FILES['image']) ? 'はい' : 'いいえ'));
    error_log('$_POST isset: ' . (empty($_POST) ? 'いいえ' : 'はい'));
}

// post_max_sizeを超えた場合のチェック（POSTリクエストなのに$_FILESと$_POSTが空）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES) && empty($_POST)) {
    $content_length = $_SERVER['CONTENT_LENGTH'] ?? 0;
    $content_mb = round($content_length / 1024 / 1024, 2);
    $limit = ini_get('post_max_size');
    
    error_log('POST受信エラー: $_FILESと$_POSTが空 - Content-Length: ' . $content_length . ' bytes (' . $content_mb . 'MB)');
    $error_message = "ファイルサイズが大きすぎます。アップロードしようとしたサイズ: {$content_mb}MB、制限: {$limit}。{$limit}以下のファイルを選択してください。";
}

// アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    error_log('==== アップロード処理開始 ====');
    error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
    error_log('CONTENT_TYPE: ' . ($_SERVER['CONTENT_TYPE'] ?? '不明'));
    error_log('CONTENT_LENGTH: ' . ($_SERVER['CONTENT_LENGTH'] ?? '不明'));
    error_log('FILES: ' . print_r($_FILES, true));
    error_log('POST keys: ' . implode(', ', array_keys($_POST)));
    
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        error_log('CSRFトークン検証失敗');
        $error_message = 'セキュリティエラーが発生しました。';
    } else {
        error_log('CSRFトークン検証成功');
        $file = $_FILES['image'];
        error_log('ファイル情報 - name: ' . $file['name'] . ', size: ' . $file['size'] . ', error: ' . $file['error']);
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = get_file_extension($file['name']);
            error_log('ファイル拡張子: ' . $ext);
            error_log('許可された拡張子: ' . implode(', ', ALLOWED_EXTENSIONS));
            
            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                error_log('エラー: 許可されていないファイル形式 - ' . $ext);
                $error_message = '許可されていないファイル形式です。';
            } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
                error_log('エラー: ファイルサイズ超過 - ' . $file['size'] . ' > ' . MAX_UPLOAD_SIZE);
                $error_message = 'ファイルサイズが大きすぎます（最大' . round(MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB）。';
            } else {
                error_log('ファイルチェックOK - アップロード処理開始');
                
                // uploadsディレクトリの存在確認と作成
                error_log('UPLOADS_DIR: ' . UPLOADS_DIR);
                error_log('UPLOADS_DIR exists: ' . (is_dir(UPLOADS_DIR) ? 'yes' : 'no'));
                
                error_log('$error_messageの値: "' . $error_message . '"');
                
                if (!is_dir(UPLOADS_DIR)) {
                    error_log('uploadsディレクトリを作成中...');
                    if (!mkdir(UPLOADS_DIR, 0755, true)) {
                        error_log('エラー: uploadsディレクトリの作成に失敗');
                        $error_message = 'アップロードディレクトリの作成に失敗しました。';
                    } else {
                        error_log('uploadsディレクトリを作成しました');
                    }
                }
                
                if (empty($error_message)) {
                    // 書き込み権限の確認
                    error_log('書き込み権限チェック...');
                    if (!is_writable(UPLOADS_DIR)) {
                        error_log('エラー: 書き込み権限がありません');
                        $error_message = 'アップロードディレクトリに書き込み権限がありません。';
                    } else {
                        error_log('書き込み権限OK');
                        $safe_filename = generate_safe_filename($file['name']);
                        $destination = UPLOADS_DIR . '/' . $safe_filename;
                        error_log('保存先: ' . $destination);
                        error_log('一時ファイル: ' . $file['tmp_name']);
                        error_log('一時ファイル存在: ' . (file_exists($file['tmp_name']) ? 'yes' : 'no'));
                        
                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            error_log('アップロード成功: ' . $safe_filename);
                            $success_message = '画像をアップロードしました: ' . $safe_filename;
                            // リロードして一覧を更新
                            header('Location: /task/admin/images.php?uploaded=1');
                            exit;
                        } else {
                            // 詳細なエラー情報を提供
                            error_log('アップロード失敗 - move_uploaded_file returned false');
                            $error_details = [];
                            if (!file_exists($file['tmp_name'])) {
                                $error_details[] = '一時ファイルが見つかりません';
                                error_log('エラー詳細: 一時ファイルが見つかりません');
                            } elseif (!is_uploaded_file($file['tmp_name'])) {
                                $error_details[] = 'セキュリティ検証に失敗';
                                error_log('エラー詳細: セキュリティ検証に失敗');
                            } else {
                                $error_details[] = 'ファイル移動エラー';
                                error_log('エラー詳細: ファイル移動エラー - パス: ' . $destination);
                            }
                            $error_message = 'アップロードに失敗しました。(' . implode(', ', $error_details) . ')';
                        }
                    }
                }
            }
        } else {
            // PHPアップロードエラーの詳細を表示
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'ファイルサイズがphp.iniの設定を超えています',
                UPLOAD_ERR_FORM_SIZE => 'ファイルサイズがフォームの設定を超えています',
                UPLOAD_ERR_PARTIAL => 'ファイルが部分的にしかアップロードされませんでした',
                UPLOAD_ERR_NO_FILE => 'ファイルが選択されていません',
                UPLOAD_ERR_NO_TMP_DIR => '一時フォルダがありません',
                UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました',
                UPLOAD_ERR_EXTENSION => 'PHPの拡張機能によりアップロードが中止されました'
            ];
            $error_message = 'アップロードエラー: ' . ($upload_errors[$file['error']] ?? 'エラーコード ' . $file['error']);
        }
    }
}

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $filename = $_POST['filename'] ?? '';
    $folder = $_POST['folder'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error_message = 'セキュリティエラーが発生しました。';
    } else {
        // 使用状況をチェック
        $usage = check_image_usage($filename, $folder);
        
        if ($folder === 'uploads') {
            $filepath = UPLOADS_DIR . '/' . basename($filename);
        } elseif ($folder === 'img') {
            $filepath = dirname(__DIR__) . '/img/' . basename($filename);
        } elseif ($folder === 'root') {
            $filepath = dirname(__DIR__) . '/' . basename($filename);
        } else {
            $error_message = '無効なフォルダが指定されました。';
            $filepath = '';
        }
        
        if ($filepath && file_exists($filepath) && unlink($filepath)) {
            $success_message = '画像を削除しました。';
            header('Location: /task/admin/images.php?deleted=1');
            exit;
        } else {
            $error_message = '削除に失敗しました。';
        }
    }
}

// 移動処理（ルート/uploads画像をimgフォルダへ）+ HTML自動修正
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_img'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $filename = $_POST['filename'] ?? '';
    $from_folder = $_POST['from_folder'] ?? 'root';
    
    if (!verify_csrf_token($csrf_token)) {
        $error_message = 'セキュリティエラーが発生しました。';
    } else {
        // 移動元のパスを決定
        if ($from_folder === 'uploads') {
            $source = UPLOADS_DIR . '/' . basename($filename);
        } else {
            $source = dirname(__DIR__) . '/' . basename($filename);
        }
        
        $destination = dirname(__DIR__) . '/img/' . basename($filename);
        $index_file = dirname(__DIR__) . '/index.html';
        
        if (!file_exists($source)) {
            $error_message = 'ファイルが見つかりませんでした。';
        } elseif (file_exists($destination)) {
            $error_message = 'img/フォルダに同名のファイルが既に存在します。';
        } else {
            // バックアップ作成
            $backup_dir = dirname(__DIR__) . '/data/backups';
            if (!is_dir($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            $backup_file = $backup_dir . '/index_' . date('Y-m-d_H-i-s') . '.html';
            
            if (!copy($index_file, $backup_file)) {
                $error_message = 'バックアップの作成に失敗しました。';
            } else {
                // HTMLファイルのパスを修正
                $html_content = file_get_contents($index_file);
                $encoded_filename = rawurlencode($filename);
                $replacements = 0;
                
                if ($from_folder === 'uploads') {
                    // uploads/ → img/ のパス置換
                    // 通常のファイル名
                    $html_content = preg_replace(
                        '/(["\'=])(uploads\/)(' . preg_quote($filename, '/') . ')/',
                        '$1img/$3',
                        $html_content,
                        -1,
                        $count1
                    );
                    $replacements += $count1;
                    
                    // URLエンコード版
                    if ($encoded_filename !== $filename) {
                        $html_content = preg_replace(
                            '/(["\'=])(uploads\/)(' . preg_quote($encoded_filename, '/') . ')/',
                            '$1img/$3',
                            $html_content,
                            -1,
                            $count2
                        );
                        $replacements += $count2;
                    }
                } else {
                    // ルート → img/ のパス置換
                    // 通常のファイル名
                    $html_content = preg_replace(
                        '/(["\'=])(' . preg_quote($filename, '/') . ')(["\'>\\s])/',
                        '$1img/$2$3',
                        $html_content,
                        -1,
                        $count1
                    );
                    $replacements += $count1;
                    
                    // URLエンコード版
                    if ($encoded_filename !== $filename) {
                        $html_content = preg_replace(
                            '/(["\'=])(' . preg_quote($encoded_filename, '/') . ')(["\'>\\s])/',
                            '$1img/$2$3',
                            $html_content,
                            -1,
                            $count2
                        );
                        $replacements += $count2;
                    }
                }
                
                // HTMLファイルを保存
                if (file_put_contents($index_file, $html_content) === false) {
                    $error_message = 'HTMLファイルの更新に失敗しました。';
                    // バックアップから復元
                    copy($backup_file, $index_file);
                } else {
                    // 画像ファイルを移動
                    if (rename($source, $destination)) {
                        $success_message = "画像をimg/フォルダへ移動しました。HTMLファイルのパスを{$replacements}箇所修正しました。";
                        header('Location: /task/admin/images.php?moved=1');
                        exit;
                    } else {
                        $error_message = '画像ファイルの移動に失敗しました。';
                        // HTMLを元に戻す
                        copy($backup_file, $index_file);
                    }
                }
            }
        }
    }
}

// 一括移動処理（全ルート画像をimgフォルダへ）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_move_to_img'])) {
    $csrf_token_input = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token_input)) {
        $error_message = 'セキュリティエラーが発生しました。';
    } else {
        $index_file = dirname(__DIR__) . '/index.html';
        $img_dir = dirname(__DIR__) . '/img';
        
        // バックアップ作成
        $backup_dir = dirname(__DIR__) . '/data/backups';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        $backup_file = $backup_dir . '/index_bulk_' . date('Y-m-d_H-i-s') . '.html';
        
        if (!copy($index_file, $backup_file)) {
            $error_message = 'バックアップの作成に失敗しました。';
        } else {
            // ルート画像のリストを取得
            $root_images = [];
            foreach ($image_files as $image) {
                if ($image['folder'] === 'root') {
                    $root_images[] = $image['name'];
                }
            }
            
            if (empty($root_images)) {
                $error_message = '移動する画像がありません。';
            } else {
                // HTMLファイルを読み込み
                $html_content = file_get_contents($index_file);
                $total_replacements = 0;
                $moved_count = 0;
                $failed_files = [];
                
                foreach ($root_images as $filename) {
                    $source = dirname(__DIR__) . '/' . $filename;
                    $destination = $img_dir . '/' . $filename;
                    
                    // 既に存在する場合はスキップ
                    if (file_exists($destination)) {
                        $failed_files[] = $filename . ' (既に存在)';
                        continue;
                    }
                    
                    // HTMLのパスを置換
                    $encoded_filename = rawurlencode($filename);
                    $replacements = 0;
                    
                    // 通常のファイル名
                    $html_content = preg_replace(
                        '/(["\'=])(' . preg_quote($filename, '/') . ')(["\'>\\s])/',
                        '$1img/$2$3',
                        $html_content,
                        -1,
                        $count1
                    );
                    $replacements += $count1;
                    
                    // URLエンコードされたファイル名
                    if ($encoded_filename !== $filename) {
                        $html_content = preg_replace(
                            '/(["\'=])(' . preg_quote($encoded_filename, '/') . ')(["\'>\\s])/',
                            '$1img/$2$3',
                            $html_content,
                            -1,
                            $count2
                        );
                        $replacements += $count2;
                    }
                    
                    $total_replacements += $replacements;
                    
                    // ファイルを移動
                    if (rename($source, $destination)) {
                        $moved_count++;
                    } else {
                        $failed_files[] = $filename . ' (移動失敗)';
                    }
                }
                
                // HTMLファイルを保存
                if (file_put_contents($index_file, $html_content) === false) {
                    $error_message = 'HTMLファイルの更新に失敗しました。バックアップから復元してください: ' . $backup_file;
                } else {
                    if ($moved_count > 0) {
                        $message = "{$moved_count}個の画像をimg/フォルダへ移動しました。HTMLファイルのパスを{$total_replacements}箇所修正しました。";
                        if (!empty($failed_files)) {
                            $message .= " 失敗: " . implode(', ', $failed_files);
                        }
                        $success_message = $message;
                        header('Location: /task/admin/images.php?bulk_moved=1&count=' . $moved_count);
                        exit;
                    } else {
                        $error_message = '画像の移動に失敗しました。' . (!empty($failed_files) ? ' 理由: ' . implode(', ', $failed_files) : '');
                        // HTMLを元に戻す
                        copy($backup_file, $index_file);
                    }
                }
            }
        }
    }
}

if (isset($_GET['uploaded'])) {
    $success_message = '画像をアップロードしました。';
}
if (isset($_GET['deleted'])) {
    $success_message = '画像を削除しました。';
}
if (isset($_GET['moved'])) {
    $success_message = '画像を移動しました。';
}
if (isset($_GET['bulk_moved'])) {
    $count = $_GET['count'] ?? 0;
    $success_message = "{$count}個の画像を一括移動しました。";
}

$csrf_token = generate_csrf_token();

render_admin_header('画像管理');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">画像管理</h1>
    <div class="btn-toolbar gap-2">
        <a href="/task/admin/USAGE_GUIDE.md" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-question-circle me-1"></i>使い方
        </a>
        <div class="btn-group">
            <?php 
            // ルート画像の数をカウント
            $root_image_count = 0;
            foreach ($image_files as $image) {
                if ($image['folder'] === 'root') {
                    $root_image_count++;
                }
            }
            if ($root_image_count > 0): 
            ?>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#bulkMoveModal">
                    <i class="bi bi-folder-symlink me-1"></i>ルート画像を一括移動 (<?php echo $root_image_count; ?>個)
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-upload me-1"></i>画像をアップロード
            </button>
        </div>
    </div>
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

<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="text-center">
                    <h3 class="mb-0"><?php echo count($image_files); ?></h3>
                    <small class="text-muted">アップロード済み画像</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <h3 class="mb-0"><?php echo number_format(array_sum(array_column($image_files, 'size')) / 1024 / 1024, 2); ?> MB</h3>
                    <small class="text-muted">合計サイズ</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <h3 class="mb-0"><?php echo implode(', ', array_map('strtoupper', ALLOWED_EXTENSIONS)); ?></h3>
                    <small class="text-muted">許可形式</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">画像一覧</h5>
        <div class="btn-group btn-group-sm" role="group">
            <input type="radio" class="btn-check" name="folderFilter" id="filterAll" autocomplete="off" checked>
            <label class="btn btn-outline-primary" for="filterAll">すべて</label>
            
            <input type="radio" class="btn-check" name="folderFilter" id="filterRoot" autocomplete="off">
            <label class="btn btn-outline-primary" for="filterRoot">ルート</label>
            
            <input type="radio" class="btn-check" name="folderFilter" id="filterImg" autocomplete="off">
            <label class="btn btn-outline-primary" for="filterImg">imgフォルダ</label>
            
            <input type="radio" class="btn-check" name="folderFilter" id="filterUploads" autocomplete="off">
            <label class="btn btn-outline-primary" for="filterUploads">アップロード</label>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($image_files)): ?>
            <p class="text-muted text-center py-5">
                <i class="bi bi-images" style="font-size: 3rem; opacity: 0.3;"></i><br>
                アップロードされた画像がありません。<br>
                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    最初の画像をアップロード
                </button>
            </p>
        <?php else: ?>
            <div class="row g-3" id="imageGrid">
                <?php foreach ($image_files as $image): ?>
                    <div class="col-md-4 col-lg-3 image-item" data-folder="<?php echo h($image['folder']); ?>">
                        <div class="card h-100">
                            <div class="position-relative" style="height: 200px; overflow: hidden; background: #f8f9fa;">
                                <?php 
                                $badge_color = 'info';
                                if ($image['folder'] === 'uploads') $badge_color = 'success';
                                elseif ($image['folder'] === 'root') $badge_color = 'warning';
                                ?>
                                <span class="badge bg-<?php echo $badge_color; ?> position-absolute top-0 start-0 m-2">
                                    <?php echo h($image['folder_label']); ?>
                                </span>
                                <?php if (in_array(get_file_extension($image['name']), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])): ?>
                                    <img src="<?php echo h($image['url']); ?>" class="w-100 h-100" style="object-fit: contain;" alt="<?php echo h($image['name']); ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <i class="bi bi-file-earmark text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title text-truncate" title="<?php echo h($image['name']); ?>">
                                    <?php echo h($image['name']); ?>
                                </h6>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-calendar me-1"></i><?php echo date('Y/m/d H:i', $image['modified']); ?><br>
                                    <i class="bi bi-hdd me-1"></i><?php echo number_format($image['size'] / 1024, 2); ?> KB
                                </p>
                                <?php 
                                // 使用状況をチェック
                                $usage = check_image_usage($image['name'], $image['folder']);
                                if (!empty($usage)):
                                ?>
                                <div class="alert alert-warning alert-sm p-2 mb-2">
                                    <small><i class="bi bi-exclamation-triangle me-1"></i><?php echo count($usage); ?>箇所で使用中</small>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-1" onclick="showUsage('<?php echo h($image['name']); ?>', '<?php echo h($image['folder']); ?>')">
                                        詳細
                                    </button>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="copyUrl('<?php echo h($image['url']); ?>')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                    <a href="<?php echo h($image['url']); ?>" target="_blank" class="btn btn-sm btn-outline-info flex-fill">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger flex-fill" onclick="deleteImage('<?php echo h($image['name']); ?>', '<?php echo h($image['folder']); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <?php if ($image['folder'] === 'root'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success w-100" onclick="moveToImg('<?php echo h($image['name']); ?>', 'root')">
                                        <i class="bi bi-folder-symlink me-1"></i>img/へ移動
                                    </button>
                                <?php elseif ($image['folder'] === 'uploads'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success w-100" onclick="moveToImg('<?php echo h($image['name']); ?>', 'uploads')">
                                        <i class="bi bi-folder-symlink me-1"></i>img/へ移動
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 一括移動確認モーダル -->
<div class="modal fade" id="bulkMoveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                <input type="hidden" name="bulk_move_to_img" value="1">
                
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>ルート画像を一括移動</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong><i class="bi bi-info-circle me-1"></i>重要な操作です</strong>
                        <p class="mb-0 mt-2">以下の処理が実行されます：</p>
                        <ul class="mb-0">
                            <li>ルート直下の全画像（<?php echo $root_image_count; ?>個）を <strong>img/</strong> フォルダへ移動</li>
                            <li>index.html内の画像パスを自動修正</li>
                            <li>実行前に自動バックアップを作成</li>
                        </ul>
                    </div>
                    
                    <h6 class="mt-3">移動対象の画像:</h6>
                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        <div class="row g-2">
                            <?php foreach ($image_files as $image): ?>
                                <?php if ($image['folder'] === 'root'): ?>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-image text-warning me-2"></i>
                                            <span class="text-truncate" title="<?php echo h($image['name']); ?>">
                                                <?php echo h($image['name']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-shield-check me-1"></i>
                        バックアップは <code>data/backups/</code> フォルダに保存されます
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-folder-symlink me-1"></i>一括移動を実行
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- アップロードモーダル -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">画像をアップロード</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">画像ファイル</label>
                        <input type="file" class="form-control" name="image" id="uploadFileInput" accept="image/*,.pdf" required>
                        <div class="form-text" id="uploadFileInfo">
                            最大サイズ: 10MB<br>
                            対応形式: <?php echo implode(', ', array_map('strtoupper', ALLOWED_EXTENSIONS)); ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">アップロード</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                <input type="hidden" name="delete" value="1">
                <input type="hidden" name="filename" id="deleteFilename">
                <input type="hidden" name="folder" id="deleteFolder">
                
                <div class="modal-header">
                    <h5 class="modal-title">削除確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>以下の画像を削除してもよろしいですか？</p>
                    <p class="text-danger"><strong id="deleteImageName"></strong></p>
                    <p class="small text-muted">フォルダ: <span id="deleteFolderName"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger">削除</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 移動確認モーダル -->
<div class="modal fade" id="moveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                <input type="hidden" name="move_to_img" value="1">
                <input type="hidden" name="filename" id="moveFilename">
                <input type="hidden" name="from_folder" id="moveFromFolder">
                
                <div class="modal-header">
                    <h5 class="modal-title">移動確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>以下の画像を <strong>img/</strong> フォルダへ移動してもよろしいですか？</p>
                    <p class="text-primary"><strong id="moveImageName"></strong></p>
                    <p class="small text-muted">移動元: <span id="moveFromFolderLabel"></span></p>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        移動後、index.htmlの画像パスも自動的に修正されます。
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">移動</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 使用状況詳細モーダル -->
<div class="modal fade" id="usageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">画像の使用状況</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="usageImageName" class="mb-3"></h6>
                <div id="usageContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">読み込み中...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<script>
function copyUrl(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        alert('URLをコピーしました: ' + fullUrl);
    });
}

function deleteImage(filename, folder) {
    document.getElementById('deleteFilename').value = filename;
    document.getElementById('deleteFolder').value = folder;
    document.getElementById('deleteImageName').textContent = filename;
    
    let folderLabel = 'その他';
    if (folder === 'uploads') folderLabel = 'アップロード';
    else if (folder === 'img') folderLabel = 'imgフォルダ';
    else if (folder === 'root') folderLabel = 'ルート';
    
    document.getElementById('deleteFolderName').textContent = folderLabel;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function moveToImg(filename, fromFolder) {
    document.getElementById('moveFilename').value = filename;
    document.getElementById('moveFromFolder').value = fromFolder;
    document.getElementById('moveImageName').textContent = filename;
    
    let folderLabel = 'ルート';
    if (fromFolder === 'uploads') folderLabel = 'アップロード';
    document.getElementById('moveFromFolderLabel').textContent = folderLabel;
    
    new bootstrap.Modal(document.getElementById('moveModal')).show();
}

function showUsage(filename, folder) {
    document.getElementById('usageImageName').textContent = filename;
    document.getElementById('usageContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">読み込み中...</span></div></div>';
    
    const modal = new bootstrap.Modal(document.getElementById('usageModal'));
    modal.show();
    
    // AJAXで使用状況を取得
    fetch('<?php echo '/task/admin/images.php'; ?>?ajax=usage&filename=' + encodeURIComponent(filename) + '&folder=' + encodeURIComponent(folder))
        .then(response => response.json())
        .then(data => {
            if (data.usage && data.usage.length > 0) {
                let html = '<div class="list-group">';
                data.usage.forEach(item => {
                    html += '<div class="list-group-item">';
                    html += '<div class="d-flex justify-content-between align-items-start">';
                    html += '<div class="flex-grow-1">';
                    html += '<strong>行 ' + item.line + ':</strong><br>';
                    html += '<code class="small">' + escapeHtml(item.content) + '</code>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';
                document.getElementById('usageContent').innerHTML = html;
            } else {
                document.getElementById('usageContent').innerHTML = '<p class="text-muted text-center py-3">使用されていません。</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('usageContent').innerHTML = '<p class="text-danger text-center py-3">エラーが発生しました。</p>';
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// フォルダフィルタ機能
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('input[name="folderFilter"]');
    
    filterButtons.forEach(button => {
        button.addEventListener('change', function() {
            const filterValue = this.id.replace('filter', '').toLowerCase();
            const imageItems = document.querySelectorAll('.image-item');
            
            imageItems.forEach(item => {
                if (filterValue === 'all') {
                    item.style.display = '';
                } else {
                    const itemFolder = item.getAttribute('data-folder');
                    item.style.display = itemFolder === filterValue ? '' : 'none';
                }
            });
        });
    });
    
    // ファイルサイズのリアルタイム表示
    const uploadFileInput = document.getElementById('uploadFileInput');
    const uploadFileInfo = document.getElementById('uploadFileInfo');
    
    if (uploadFileInput) {
        uploadFileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                const maxSizeMB = 10;
                
                let infoHtml = `選択: ${file.name} (${sizeMB}MB)<br>`;
                
                if (file.size > maxSizeMB * 1024 * 1024) {
                    infoHtml += `<span class="text-danger">⚠ ファイルサイズが${maxSizeMB}MBを超えています！</span><br>`;
                } else {
                    infoHtml += `<span class="text-success">✓ アップロード可能</span><br>`;
                }
                
                infoHtml += `最大サイズ: ${maxSizeMB}MB<br>`;
                infoHtml += `対応形式: <?php echo implode(", ", array_map("strtoupper", ALLOWED_EXTENSIONS)); ?>`;
                
                uploadFileInfo.innerHTML = infoHtml;
            }
        });
    }
    
    // アップロードフォームのデバッグと二重送信防止
    const uploadForm = document.querySelector('#uploadModal form');
    let isSubmitting = false;
    
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            // 二重送信を防止
            if (isSubmitting) {
                console.log('既に送信中です');
                e.preventDefault();
                return false;
            }
            
            console.log('フォーム送信開始');
            const fileInput = this.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                const maxSizeMB = 10;
                
                console.log('選択されたファイル:', file.name);
                console.log('ファイルサイズ:', file.size, 'bytes (' + sizeMB + 'MB)');
                
                // ファイルサイズチェック
                if (file.size > maxSizeMB * 1024 * 1024) {
                    alert(`ファイルサイズが大きすぎます。\n選択: ${sizeMB}MB\n制限: ${maxSizeMB}MB`);
                    e.preventDefault();
                    return false;
                }
                
                // 送信開始
                isSubmitting = true;
                
                // ボタンを無効化
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'アップロード中...';
                }
            } else {
                console.log('ファイルが選択されていません');
                e.preventDefault();
                alert('ファイルを選択してください');
                return false;
            }
        });
    }
});
</script>

<?php
// AJAX要求の処理
if (isset($_GET['ajax']) && $_GET['ajax'] === 'usage') {
    $filename = $_GET['filename'] ?? '';
    $folder = $_GET['folder'] ?? '';
    
    header('Content-Type: application/json');
    echo json_encode([
        'usage' => check_image_usage($filename, $folder)
    ]);
    exit;
}

render_admin_footer();
?>
