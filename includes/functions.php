<?php
/**
 * 共通関数ライブラリ
 */

/**
 * 本番環境かどうかを判定
 */
function is_production() {
    return ENVIRONMENT === 'production';
}

/**
 * ローカル環境かどうかを判定
 */
function is_local() {
    return ENVIRONMENT === 'local';
}

/**
 * ログイン状態をチェック
 */
function check_login() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ' . get_base_url() . 'admin/login.php');
        exit;
    }
}

/**
 * CSRFトークンを生成
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRFトークンを検証
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * JSONファイルを読み込む
 */
function read_json_file($filepath) {
    if (!file_exists($filepath)) {
        return null;
    }
    $content = file_get_contents($filepath);
    return json_decode($content, true);
}

/**
 * JSONファイルに書き込む
 */
function write_json_file($filepath, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filepath, $json) !== false;
}

/**
 * コンテンツデータを取得
 */
function get_content_data() {
    $data = read_json_file(CONTENT_FILE);
    if ($data === null) {
        // デフォルトデータ
        $data = [
            'slogans' => [
                '安全第一で運転しましょう',
                '笑顔でお客様をお迎えしましょう',
                '時間厳守を心がけましょう'
            ],
            'checklist' => []
        ];
        write_json_file(CONTENT_FILE, $data);
    }
    return $data;
}

/**
 * コンテンツデータを保存（排他ロック・原子的保存・自動バックアップ付き）
 */
function save_content_data($data) {
    // バリデーション
    if (!validate_content_data($data)) {
        error_log('Content data validation failed');
        return false;
    }

    // 内容更新日時を自動反映（日本時間）
    $data['updated_at'] = date('Y年m月d日');
    
    // 自動バックアップ（世代数上限10）
    rotate_backups(BACKUP_MAX_GENERATIONS);
    create_backup();
    
    // 原子的保存（テンポラリ→rename）
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $temp_file = CONTENT_FILE . '.tmp.' . getmypid();
    
    // 排他ロックで書き込み
    $fp = fopen($temp_file, 'w');
    if (!$fp) {
        error_log('Failed to open temp file: ' . $temp_file);
        return false;
    }
    
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        
        // 原子的にリネーム
        if (rename($temp_file, CONTENT_FILE)) {
            // content_new_structure.json にも保存
            $new_structure_file = DATA_DIR . '/content_new_structure.json';
            $temp_file_new = $new_structure_file . '.tmp.' . getmypid();
            
            $fp_new = fopen($temp_file_new, 'w');
            if ($fp_new) {
                if (flock($fp_new, LOCK_EX)) {
                    fwrite($fp_new, $json);
                    fflush($fp_new);
                    flock($fp_new, LOCK_UN);
                    fclose($fp_new);
                    @rename($temp_file_new, $new_structure_file);
                } else {
                    fclose($fp_new);
                    @unlink($temp_file_new);
                }
            }
            
            return true;
        } else {
            error_log('Failed to rename temp file to content file');
            @unlink($temp_file);
            return false;
        }
    } else {
        fclose($fp);
        @unlink($temp_file);
        error_log('Failed to acquire lock on temp file');
        return false;
    }
}

/**
 * コンテンツデータのバリデーション
 */
function validate_content_data($data) {
    // 基本的な型チェック
    if (!is_array($data)) {
        return false;
    }
    
    // slogansの検証
    if (isset($data['slogans'])) {
        if (!is_array($data['slogans'])) {
            return false;
        }
        foreach ($data['slogans'] as $slogan) {
            if (!is_string($slogan)) {
                return false;
            }
        }
    }
    
    // checklistの検証
    if (isset($data['checklist'])) {
        if (!is_array($data['checklist'])) {
            return false;
        }
    }
    
    return true;
}

/**
 * HTMLエスケープ
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON APIレスポンスを返す
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * エラーレスポンスを返す
 */
function error_response($message, $status = 400) {
    json_response(['success' => false, 'error' => $message], $status);
}

/**
 * 成功レスポンスを返す
 */
function success_response($data = []) {
    json_response(array_merge(['success' => true], $data));
}

/**
 * ファイル拡張子を取得
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * 安全なファイル名を生成
 */
function generate_safe_filename($original_name) {
    $ext = get_file_extension($original_name);
    $basename = pathinfo($original_name, PATHINFO_FILENAME);
    $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
    $timestamp = date('YmdHis');
    $random = substr(bin2hex(random_bytes(4)), 0, 8);
    return $safe_basename . '_' . $timestamp . '_' . $random . '.' . $ext;
}

/**
 * バックアップを作成
 */
function create_backup() {
    $backup_dir = DATA_DIR . '/backups';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . '/content_' . $timestamp . '.json';
    
    if (file_exists(CONTENT_FILE)) {
        return copy(CONTENT_FILE, $backup_file);
    }
    return false;
}

/**
 * バックアップをローテーション（古いものを削除）
 */
function rotate_backups($max_backups = 30) {
    $backup_dir = DATA_DIR . '/backups';
    if (!is_dir($backup_dir)) {
        return;
    }
    
    // バックアップファイルを取得
    $files = glob($backup_dir . '/content_*.json');
    if (count($files) <= $max_backups) {
        return;
    }
    
    // 更新日時でソート（古い順）
    usort($files, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // 上限を超えた分を削除
    $to_delete = count($files) - $max_backups;
    for ($i = 0; $i < $to_delete; $i++) {
        @unlink($files[$i]);
    }
}

/**
 * システム情報を取得
 */
function get_system_info() {
    $info = [];
    
    // JSONファイルサイズ
    if (file_exists(CONTENT_FILE)) {
        $info['content_size'] = filesize(CONTENT_FILE);
        $info['content_size_formatted'] = format_bytes($info['content_size']);
        $info['content_modified'] = filemtime(CONTENT_FILE);
        $info['content_modified_formatted'] = date('Y/m/d H:i:s', $info['content_modified']);
    } else {
        $info['content_size'] = 0;
        $info['content_size_formatted'] = '0 B';
        $info['content_modified'] = 0;
        $info['content_modified_formatted'] = '-';
    }
    
    // バックアップ数
    $backup_dir = DATA_DIR . '/backups';
    if (is_dir($backup_dir)) {
        $backups = glob($backup_dir . '/content_*.json');
        $info['backup_count'] = count($backups);
        
        // 最新のバックアップ
        if (!empty($backups)) {
            usort($backups, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $info['latest_backup'] = filemtime($backups[0]);
            $info['latest_backup_formatted'] = date('Y/m/d H:i:s', $info['latest_backup']);
        } else {
            $info['latest_backup'] = 0;
            $info['latest_backup_formatted'] = '-';
        }
    } else {
        $info['backup_count'] = 0;
        $info['latest_backup'] = 0;
        $info['latest_backup_formatted'] = '-';
    }
    
    return $info;
}

/**
 * バイト数を人間が読みやすい形式にフォーマット
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * HTMLバックアップをローテーション（古いものを削除）
 */
function rotate_html_backups($max_backups = 10) {
    $backup_dir = DATA_DIR . '/backups';
    if (!is_dir($backup_dir)) {
        return;
    }
    
    // HTMLバックアップファイルを取得
    $files = glob($backup_dir . '/index_*.html');
    if (count($files) <= $max_backups) {
        return;
    }
    
    // 更新日時でソート（古い順）
    usort($files, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // 上限を超えた分を削除
    $to_delete = count($files) - $max_backups;
    for ($i = 0; $i < $to_delete; $i++) {
        @unlink($files[$i]);
    }
}

/**
 * 管理画面のヘッダーを出力
 */
function render_admin_header($title = 'CMS管理画面') {
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo h($title); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            .sidebar {
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .sidebar .nav-link {
                color: rgba(255, 255, 255, 0.8);
                padding: 0.75rem 1rem;
                border-radius: 0.5rem;
                margin: 0.25rem 0;
                transition: all 0.3s;
            }
            .sidebar .nav-link:hover,
            .sidebar .nav-link.active {
                color: white;
                background: rgba(255, 255, 255, 0.1);
            }
            .content-area {
                min-height: 100vh;
                background: #f8f9fa;
            }
            /* モバイルトップバー */
            .mobile-topbar {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .mobile-topbar .navbar-brand {
                color: white;
                font-weight: 700;
            }
            .mobile-topbar .navbar-toggler {
                border-color: rgba(255,255,255,0.3);
            }
            .mobile-topbar .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }
            @media (max-width: 767.98px) {
                .sidebar {
                    min-height: auto;
                }
            }
        </style>
    </head>
    <body>
        <!-- モバイル用トップバー -->
        <nav class="navbar navbar-dark mobile-topbar d-md-none sticky-top">
            <div class="container-fluid">
                <span class="navbar-brand">Task CMS</span>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="メニュー">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>
        
        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <div class="text-center mb-4">
                            <h4 class="text-white">Task CMS</h4>
                            <small class="text-white-50">管理画面</small>
                        </div>
                        <div class="px-3 mb-3">
                            <form method="POST" action="/task/admin/settings.php" class="d-grid">
                                <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                                <button type="submit" name="create_backup" class="btn btn-sm btn-light w-100" title="今すぐバックアップ">
                                    <i class="bi bi-archive me-1"></i>今すぐバックアップ
                                </button>
                            </form>
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="/task/admin/index.php">
                                    <i class="bi bi-speedometer2 me-2"></i>ダッシュボード
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/task/admin/slogans.php">
                                    <i class="bi bi-chat-quote me-2"></i>スローガン管理
                                </a>
                            </li>
                            <li class="nav-item">

                                <a class="nav-link" href="/task/admin/accordion_links.php">
                                    <i class="bi bi-link-45deg me-2"></i>アコーディオンリンク
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/task/admin/live_editor.php">
                                    <i class="bi bi-eye me-2"></i>ライブ編集
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/task/admin/images.php">
                                    <i class="bi bi-images me-2"></i>画像管理
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/task/admin/notices.php">
                                    <i class="bi bi-megaphone me-2"></i>お知らせ管理
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/task/admin/settings.php">
                                    <i class="bi bi-gear me-2"></i>設定
                                </a>
                            </li>
                            <li class="nav-item mt-3">
                                <a class="nav-link text-danger" href="/task/admin/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>ログアウト
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-area">
                    <div class="py-4">
    <?php
}

/**
 * 管理画面のフッターを出力
 */
function render_admin_footer() {
    ?>
                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
