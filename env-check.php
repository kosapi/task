<?php
/**
 * 環境診断ページ
 * /task/env-check.php
 * 
 * 本番・ローカル環境の設定確認用
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>環境診断 - task CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .check-pass { background-color: #d4edda; }
        .check-fail { background-color: #f8d7da; }
        .check-info { background-color: #d1ecf1; }
        pre { background: #f8f9fa; padding: 12px; border-radius: 4px; overflow-x: auto; }
        .badge-env { font-size: 1.1rem; padding: 6px 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-3">
                <i class="bi bi-gear"></i> 環境診断 - task CMS
            </h1>
            <p class="text-muted">ローカル環境と本番環境の自動判定が正しく機能しているかを確認します。</p>
        </div>
    </div>

    <!-- PHP側の環境情報 -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-server"></i> サーバー環境情報（PHP側）</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless mb-0">
                <tr>
                    <td class="fw-bold" style="width: 180px;">ホスト名:</td>
                    <td><code><?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?></code></td>
                </tr>
                <tr>
                    <td class="fw-bold">プロトコル:</td>
                    <td><code><?php echo htmlspecialchars($_SERVER['REQUEST_SCHEME']); ?></code></td>
                </tr>
                <tr>
                    <td class="fw-bold">ポート:</td>
                    <td><code><?php echo htmlspecialchars($_SERVER['SERVER_PORT']); ?></code></td>
                </tr>
                <tr>
                    <td class="fw-bold">PHP バージョン:</td>
                    <td><code><?php echo phpversion(); ?></code></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 環境判定結果 -->
    <div class="card">
        <div class="card-header <?php echo is_production() ? 'bg-danger' : 'bg-success'; ?> text-white">
            <h5 class="mb-0"><i class="bi bi-check2-circle"></i> 検出された環境</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">環境名</h6>
                    <div class="<?php echo is_production() ? 'check-fail' : 'check-pass'; ?> p-3 rounded">
                        <strong>
                            <i class="bi bi-<?php echo is_production() ? 'cloud' : 'laptop'; ?>"></i>
                            <?php echo ENVIRONMENT === 'production' ? '本番環境' : 'ローカル環境'; ?>
                        </strong>
                        <br>
                        <small class="text-muted">
                            <?php echo ENVIRONMENT === 'production' 
                                ? '(teito.link)' 
                                : '(localhost / 127.0.0.1)'; ?>
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">ベースURL</h6>
                    <div class="check-info p-3 rounded">
                        <code style="font-size: 0.85rem; word-break: break-all;">
                            <?php echo BASE_URL; ?>
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- パス設定 -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-folder-open"></i> パス設定</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr>
                    <td class="fw-bold" style="width: 180px;">DATA_DIR:</td>
                    <td><code style="font-size: 0.85rem;"><?php echo DATA_DIR; ?></code></td>
                </tr>
                <tr>
                    <td class="fw-bold">UPLOADS_DIR:</td>
                    <td><code style="font-size: 0.85rem;"><?php echo UPLOADS_DIR; ?></code></td>
                </tr>
                <tr>
                    <td class="fw-bold">CONTENT_FILE:</td>
                    <td><code style="font-size: 0.85rem;"><?php echo CONTENT_FILE; ?></code></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- セキュリティチェック -->
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-shield-check"></i> セキュリティチェック</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr>
                    <td class="fw-bold" style="width: 180px;">HTTPS:</td>
                    <td>
                        <?php if ($_SERVER['REQUEST_SCHEME'] === 'https' || $_SERVER['SERVER_PORT'] == 443): ?>
                            <span class="badge bg-success">有効</span>
                        <?php else: ?>
                            <span class="badge bg-warning">無効</span>
                            <small class="text-muted">(ローカル開発時は正常)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">エラー表示:</td>
                    <td>
                        <?php if (is_production()): ?>
                            <span class="badge bg-success">無効（推奨）</span>
                        <?php else: ?>
                            <span class="badge bg-info">有効（開発用）</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">data ディレクトリ存在:</td>
                    <td>
                        <?php if (is_dir(DATA_DIR)): ?>
                            <span class="badge bg-success">存在</span>
                        <?php else: ?>
                            <span class="badge bg-danger">欠落</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">uploads ディレクトリ存在:</td>
                    <td>
                        <?php if (is_dir(UPLOADS_DIR)): ?>
                            <span class="badge bg-success">存在</span>
                        <?php else: ?>
                            <span class="badge bg-danger">欠落</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">content.json 存在:</td>
                    <td>
                        <?php if (file_exists(CONTENT_FILE)): ?>
                            <span class="badge bg-success">存在</span>
                        <?php else: ?>
                            <span class="badge bg-warning">初期化必要</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- JavaScript側の環境情報（クライアント側で表示） -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-browser"></i> クライアント環境情報（JavaScript側）</h5>
        </div>
        <div class="card-body">
            <div id="js-info">
                <p class="text-muted">JavaScript 実行中...</p>
            </div>
        </div>
    </div>

    <!-- 推奨事項 -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-lightbulb"></i> 推奨事項</h5>
        </div>
        <div class="card-body">
            <?php if (is_production()): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>本番環境が検出されました</strong>
                    <ul class="mb-0 mt-2">
                        <li>管理パスワードを変更してください（config.php）</li>
                        <li>HTTPS が有効化されていることを確認してください</li>
                        <li>ファイルパーミッションを確認してください</li>
                        <li>エラーログが記録されるか確認してください</li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>ローカル環境が検出されました</strong>
                    <ul class="mb-0 mt-2">
                        <li>デバッグ機能が有効化されています</li>
                        <li>エラー表示が有効化されています</li>
                        <li>本番環境へのデプロイ時は config.php を確認してください</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ナビゲーション -->
    <div class="card">
        <div class="card-body text-center">
            <a href="/task/" class="btn btn-primary me-2">メインページへ</a>
            <a href="/task/admin/" class="btn btn-secondary">管理画面へ</a>
        </div>
    </div>
</div>

<!-- JavaScript側の情報を表示 -->
<script>
    // env-check.php 用の環境判定スクリプト（index.html のものと同じ）
    window.ENV = {
        isProduction: function() {
            return window.location.hostname === 'teito.link';
        },
        isLocal: function() {
            return window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        },
        getBaseUrl: function() {
            if (this.isProduction()) {
                return 'https://teito.link/task/';
            } else {
                const protocol = window.location.protocol;
                const host = window.location.host;
                return protocol + '//' + host + '/task/';
            }
        },
        getEnvironment: function() {
            return this.isProduction() ? 'production' : 'local';
        }
    };
    
    // グローバル変数としてベースURLを設定
    window.BASE_URL = window.ENV.getBaseUrl();
    window.ENVIRONMENT = window.ENV.getEnvironment();
    
    console.log('📍 環境:', window.ENVIRONMENT);
    console.log('🔗 ベースURL:', window.BASE_URL);
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // window.ENV が存在確認
        if (typeof window.ENV !== 'undefined') {
            const info = document.getElementById('js-info');
            const html = `
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="fw-bold" style="width: 180px;">環境:</td>
                        <td><span class="badge ${window.ENV.isProduction() ? 'bg-danger' : 'bg-success'}">
                            ${window.ENV.isProduction() ? '本番' : 'ローカル'}
                        </span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">ベースURL:</td>
                        <td><code style="font-size: 0.85rem; word-break: break-all;">${window.ENV.getBaseUrl()}</code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">ホスト名:</td>
                        <td><code>${window.location.hostname}</code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">プロトコル:</td>
                        <td><code>${window.location.protocol}</code></td>
                    </tr>
                </table>
            `;
            info.innerHTML = html;
        } else {
            document.getElementById('js-info').innerHTML = '<div class="alert alert-danger">window.ENV が初期化されていません</div>';
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
