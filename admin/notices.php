<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

// お知らせ用JSONファイルパス
$notices_file = DATA_DIR . '/notices.json';

// お知らせ一覧を取得
$notices = [];
if (file_exists($notices_file)) {
    $json_content = file_get_contents($notices_file);
    $notices = json_decode($json_content, true) ?? [];
}

// 新規作成処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $new_notice = [
            'id' => uniqid('notice_'),
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'type' => $_POST['type'] ?? 'info', // info, warning, danger, success
            'display' => isset($_POST['display']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $notices[] = $new_notice;
        file_put_contents($notices_file, json_encode($notices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $_SESSION['message'] = 'お知らせを作成しました。';
        $_SESSION['message_type'] = 'success';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    // 更新処理
    elseif ($_POST['action'] === 'update') {
        $notice_id = $_POST['id'] ?? '';
        foreach ($notices as &$notice) {
            if ($notice['id'] === $notice_id) {
                $notice['title'] = $_POST['title'] ?? '';
                $notice['content'] = $_POST['content'] ?? '';
                $notice['type'] = $_POST['type'] ?? 'info';
                $notice['display'] = isset($_POST['display']) ? 1 : 0;
                $notice['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        file_put_contents($notices_file, json_encode($notices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $_SESSION['message'] = 'お知らせを更新しました。';
        $_SESSION['message_type'] = 'success';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    // 削除処理
    elseif ($_POST['action'] === 'delete') {
        $notice_id = $_POST['id'] ?? '';
        $notices = array_filter($notices, function($n) use ($notice_id) {
            return $n['id'] !== $notice_id;
        });
        $notices = array_values($notices);
        file_put_contents($notices_file, json_encode($notices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $_SESSION['message'] = 'お知らせを削除しました。';
        $_SESSION['message_type'] = 'success';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    // 表示/非表示切り替え処理
    elseif ($_POST['action'] === 'toggle') {
        $notice_id = $_POST['id'] ?? '';
        foreach ($notices as &$notice) {
            if ($notice['id'] === $notice_id) {
                $notice['display'] = $notice['display'] ? 0 : 1;
                $notice['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        file_put_contents($notices_file, json_encode($notices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $_SESSION['message'] = 'お知らせの表示設定を更新しました。';
        $_SESSION['message_type'] = 'success';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// メッセージ表示
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? 'info';
unset($_SESSION['message'], $_SESSION['message_type']);

render_admin_header('お知らせ管理');
?>

<style>
    /* デスクトップ/共通: 見やすいサイズに拡大 */
    .table { font-size: 1.05rem; }
    .table td small { font-size: 0.95rem; }
    .btn { font-size: 1rem; }
    .btn-sm { font-size: 0.95rem; }
    .form-label, .form-control, .form-select { font-size: 1rem; }
    .card-header h5 { font-size: 1.2rem; }

    /* モバイル対応 */
    @media (max-width: 768px) {
        .table {
            font-size: 14px;
        }
    
    .table th,
    .table td {
      padding: 8px 6px;
    }
    
        .btn {
            padding: 8px 10px;
            font-size: 14px;
        }
    
        .btn-sm {
            padding: 6px 8px;
            font-size: 13px;
        }
    
        .form-control,
        .form-select {
            font-size: 16px;
            padding: 8px;
        }
    
    .modal-dialog {
      margin: 10px auto;
      max-width: 95vw;
    }
    
    .modal-header {
      padding: 12px 10px;
    }
    
        .modal-title {
            font-size: 17px;
        }
    
        .modal-body {
            padding: 12px;
            font-size: 15px;
        }
    
    .modal-footer {
      padding: 10px;
      gap: 6px;
    }
    
    .card-header,
    .card-body {
      padding: 12px;
    }
    
        .alert {
            padding: 10px 12px;
            font-size: 15px;
        }
    
    .d-flex {
      flex-direction: column;
      gap: 8px;
    }
    
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
  }
  
        @media (max-width: 480px) {
        .table {
            font-size: 13px;
        }
    
    .table th,
    .table td {
      padding: 6px 4px;
    }
    
        .btn {
            padding: 10px 12px;
            font-size: 13px;
      min-height: 44px;
      width: 100%;
      margin-bottom: 6px;
    }
    
        .btn-sm {
            padding: 8px 10px;
            font-size: 12px;
      min-height: 36px;
      width: 100%;
    }
    
    .form-control,
    .form-select,
    input,
    textarea {
      font-size: 16px;
      padding: 10px 8px;
      border-radius: 6px;
    }
    
    textarea {
      min-height: 100px;
    }
    
    .modal-dialog {
      margin: 5px auto;
      max-width: calc(100vw - 10px);
    }
    
    .modal-header {
      padding: 12px 10px;
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }
    
    .modal-header .btn-close {
      align-self: flex-end;
    }
    
        .modal-title {
            font-size: 16px;
      width: 100%;
    }
    
        .modal-body {
            padding: 12px;
            font-size: 14px;
    }
    
    .modal-footer {
      padding: 10px 8px;
      flex-direction: column;
      gap: 6px;
    }
    
    .modal-footer .btn {
      margin-bottom: 0;
    }
    
    .card {
      border-radius: 8px;
      margin-bottom: 12px;
    }
    
        .card-header {
            padding: 10px;
            font-size: 16px;
    }
    
    .card-body {
      padding: 10px;
    }
    
        .alert {
            padding: 10px 10px;
            font-size: 14px;
            border-radius: 6px;
    }
    
    .d-flex {
      flex-direction: column;
      gap: 8px;
    }
    
    .row {
      gap: 8px;
    }
    
    .col-md-6 {
      width: 100%;
    }
    
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    
    table {
      width: 100%;
      table-layout: auto;
    }
  }

    /* さらに小さい端末でテーブルをカード風に */
    @media (max-width: 576px) {
        table.table thead { display: none; }
        table.table tbody tr {
            display: block;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 6px 8px;
            margin-bottom: 10px;
            background: #fff;
        }
        table.table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 8px 4px;
            border-bottom: 1px dashed #eef0f4;
        }
        table.table tbody td:last-child { border-bottom: none; }
        table.table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6b7280;
            min-width: 88px;
            flex-shrink: 0;
        }
        .card-body .btn.btn-sm { width: 100%; margin-bottom: 6px; }
        .card-body .d-flex.gap-1 { gap: 6px !important; }
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-megaphone me-2"></i>お知らせ管理</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNoticeModal">
            <i class="bi bi-plus-lg me-1"></i>新規作成
        </button>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo h($message_type); ?> alert-dismissible fade show mb-4" role="alert">
    <?php echo h($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">お知らせ一覧</h5>
    </div>
    <div class="card-body">
        <?php if (empty($notices)): ?>
        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            お知らせはまだ作成されていません。
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">タイトル</th>
                        <th style="width: 40%;">内容</th>
                        <th style="width: 10%;">タイプ</th>
                        <th style="width: 10%;">表示</th>
                        <th style="width: 15%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($notices) as $notice): ?>
                    <tr>
                        <td data-label="タイトル">
                            <strong><?php echo h($notice['title']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo h($notice['created_at']); ?></small>
                        </td>
                        <td data-label="内容">
                            <small><?php echo h(mb_substr($notice['content'], 0, 50)); ?>...</small>
                        </td>
                        <td data-label="タイプ">
                            <?php 
                            $type_colors = [
                                'info' => 'primary',
                                'warning' => 'warning',
                                'danger' => 'danger',
                                'success' => 'success'
                            ];
                            $color = $type_colors[$notice['type']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $color; ?>">
                                <?php echo h($notice['type']); ?>
                            </span>
                        </td>
                        <td data-label="表示">
                            <?php if (!empty($notice['display'])): ?>
                                <span class="badge bg-success">表示中</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">非表示</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="操作">
                            <div class="d-flex gap-1 flex-wrap">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editNoticeModal" 
                                        onclick="editNotice('<?php echo h($notice['id']); ?>', '<?php echo h($notice['title']); ?>', '<?php echo h(str_replace("'", "\\'", $notice['content'])); ?>', '<?php echo h($notice['type']); ?>', <?php echo $notice['display']; ?>)">
                                    <i class="bi bi-pencil"></i> 編集
                                </button>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewNoticeModal" 
                                        onclick="viewNotice('<?php echo h($notice['id']); ?>', '<?php echo h($notice['title']); ?>', '<?php echo h(str_replace("'", "\\'", $notice['content'])); ?>', '<?php echo h($notice['type']); ?>', <?php echo $notice['display']; ?>, '<?php echo h($notice['created_at']); ?>')">
                                    <i class="bi bi-eye"></i> 詳細
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('削除しますか？');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo h($notice['id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> 削除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 新規作成モーダル -->
<div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createNoticeLabel">新規お知らせを作成</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">タイトル <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="お知らせのタイトルを入力">
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">内容 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="5" required placeholder="お知らせの内容を入力"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">タイプ</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="info">情報</option>
                                    <option value="success">成功</option>
                                    <option value="warning">警告</option>
                                    <option value="danger">重要</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label d-block">表示設定</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="display" name="display" checked>
                                    <label class="form-check-label" for="display">
                                        メインページに表示する
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <input type="hidden" name="action" value="create">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>作成
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 編集モーダル -->
<div class="modal fade" id="editNoticeModal" tabindex="-1" aria-labelledby="editNoticeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNoticeLabel">お知らせを編集</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id" value="">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">タイトル <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required placeholder="お知らせのタイトルを入力">
                    </div>
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">内容 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_content" name="content" rows="5" required placeholder="お知らせの内容を入力"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_type" class="form-label">タイプ</label>
                                <select class="form-select" id="edit_type" name="type">
                                    <option value="info">情報</option>
                                    <option value="success">成功</option>
                                    <option value="warning">警告</option>
                                    <option value="danger">重要</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label d-block">表示設定</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_display" name="display">
                                    <label class="form-check-label" for="edit_display">
                                        メインページに表示する
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <input type="hidden" name="action" value="update">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>更新
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 詳細表示モーダル -->
<div class="modal fade" id="viewNoticeModal" tabindex="-1" aria-labelledby="viewNoticeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewNoticeLabel" style="word-break: break-word;"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="noticeContent" style="line-height: 1.8;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<script>
function editNotice(id, title, content, type, display) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_content').value = content;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_display').checked = display === 1;
}

function viewNotice(id, title, content, type, display, createdAt) {
        document.getElementById('viewNoticeLabel').textContent = title;

        // 色/ラベル定義
        const typeMap = {
                info:    {bg: '#0d6efd', label: '情報'},
                success: {bg: '#198754', label: '成功'},
                warning: {bg: '#ffc107', label: '警告'},
                danger:  {bg: '#dc3545', label: '重要'}
        };
        const t = typeMap[type] || typeMap.info;
        const grad2 = (hex, amt) => {
                const num = parseInt(hex.replace('#',''), 16);
                const A = Math.round(2.55 * amt);
                const R = Math.max(0, Math.min(255, (num >> 16) + A));
                const G = Math.max(0, Math.min(255, (num >> 8 & 0x00FF) + A));
                const B = Math.max(0, Math.min(255, (num & 0x0000FF) + A));
                return '#' + (0x1000000 + R*0x10000 + G*0x100 + B).toString(16).slice(1);
        };

        const statusBadge = display === 1
                ? '<span class="badge bg-success">表示中</span>'
                : '<span class="badge bg-secondary">非表示</span>';

        const safeContent = (content || '').replace(/\n/g, '<br>');

        const html = `
            <div style="border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.08); border:1px solid #eef0f4;">
                <div style="background:linear-gradient(135deg, ${t.bg} 0%, ${grad2(t.bg,-15)} 100%); color:#fff; padding:14px 16px; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="bi bi-megaphone"></i>
                        <span style="font-weight:700;">${title}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="badge" style="background:rgba(255,255,255,0.2); color:#fff;">${t.label}</span>
                        ${statusBadge}
                    </div>
                </div>
                <div style="background:#f8f9fa; padding:16px; color:#333; line-height:1.85; font-size:15px;">
                    ${safeContent}
                </div>
                <div style="border-top:1px solid #eef0f4; background:#fff; padding:10px 14px; color:#6b7280; font-size:14px; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-calendar-event"></i><span>${createdAt || ''}</span>
                </div>
            </div>`;

        document.getElementById('noticeContent').innerHTML = html;
}
</script>

<?php render_admin_footer(); ?>
