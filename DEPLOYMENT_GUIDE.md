# 本番デプロイガイド

## 準備完了項目

### ✅ クリーンアップ完了
- テスト/デバッグファイル削除: 10ファイル
- 不要バックアップファイル削除: index_cleaned.html

### ✅ ログ抑制完了
- デバッグログ制御システム実装 (`debug-config.js`)
- 全主要JSで console.log → DEBUG.log に置換
  - progress_v22.js
  - checklist-search.js
  - init.js, hash-navigation.js, modal-autoshow.js
  - clear_v27.js, reload.js, spek_v4.js, cheke_v3.js
- 本番環境（`window.ENVIRONMENT === 'production'`）ではログが自動抑制

### ✅ 環境判定システム拡張
- `env-detection.js`: production / local / staging を区別
- 環境に応じた自動設定（BASE_URL、ログレベル等）

### ✅ PHP本番設定
- `config-production.php`: エラーレベル、Session設定、OPcache
- `.htaccess` 拡張: gzip圧縮、キャッシュ制御、セキュリティヘッダ

### ✅ セキュリティ確認
- admin認証: CSRF トークン + パスワード検証
- ディレクトリリスティング無効
- HTTPセキュリティヘッダ設定

### ✅ デプロイテストツール
- `deployment-checklist.js`: ブラウザコンソールから `window.DeploymentChecklist.runAll()` で全テスト実行

---

## 本番デプロイ手順

### 1. 前日準備
```bash
# データバックアップ取得
cd c:\xampp\htdocs\task\data
tar -czf backup_$(date +%Y%m%d).tar.gz .

# config.php 本番版確認
# - DATABASE_HOST, USER, PASS が本番値か確認
# - get_environment() が 'production' を返すか確認
```

### 2. 本番環境へのファイル同期

#### デプロイするファイル構造
```
task/
├── index.html              # メインページ（更新版）
├── config-production.php   # 本番用PHP設定
├── config.php              # 環境設定
├── .htaccess              # サーバー設定（gzip/キャッシュ）
│
├── js/                    # JavaScriptファイル（整理済み）
│   ├── env-detection.js      # 環境判定（production/local/staging）
│   ├── debug-config.js       # デバッグログ制御
│   ├── modal-autoshow.js     # モーダルハッシュナビゲーション
│   ├── init.js               # 初期化スクリプト
│   ├── garlic.js             # フォーム保存ライブラリ
│   ├── progress_v22.js       # 進捗バー制御
│   ├── clear_v27.js          # クリア機能
│   ├── reload.js             # リロード機能
│   ├── spek_v4.js            # スペック表示
│   ├── cheke_v3.js           # チェック機能
│   ├── checklist-search.js   # チェックリスト検索
│   ├── popup-helper.js       # ポップアップヘルパー
│   ├── slogans.js            # スローガン表示
│   ├── update-footer-date.js # フッター日付更新
│   ├── hash-navigation.js    # ハッシュナビゲーション
│   ├── deployment-checklist.js # デプロイテストツール
│   ├── accordion_link.js     # アコーディオンリンク
│   ├── checklist_v1.js       # チェックリスト機能
│   └── calsel_v10.js         # カレンダー選択
│
├── css/                   # CSSファイル（整理済み）
│   ├── main_v51.css          # メインスタイル
│   ├── loader.css            # ローダースタイル
│   ├── checklist-search.css  # 検索UIスタイル
│   └── modal-fix.css         # モーダル修正スタイル
│
├── admin/                 # 管理画面
│   ├── index.php             # 管理画面トップ
│   ├── login.php             # ログイン（認証確認）
│   ├── logout.php            # ログアウト
│   ├── images.php            # 画像管理
│   ├── settings.php          # 設定管理
│   ├── slogans.php           # スローガン管理
│   ├── accordion_links.php   # アコーディオンリンク管理
│   └── visual_editor.php     # ビジュアルエディタ
│
├── api/                   # APIエンドポイント
│   └── get-content.php       # コンテンツ取得API
│
├── data/                  # データファイル
│   ├── content.json          # 最新コンテンツJSON
│   └── backups/              # バックアップフォルダ
│
├── includes/              # 共通PHPファイル
│   └── functions.php         # 共通関数
│
├── img/                   # 画像ファイル
│   ├── *.png, *.jpg          # 各種画像
│   └── (すべての画像ファイル)
│
└── uploads/               # アップロードフォルダ
    └── .gitkeep

```

#### 同期コマンド例
```bash
# FTP/SCP/rsyncなどで同期
# 以下のファイル・フォルダーを本番環境にアップロード:
rsync -avz --exclude='test_*' --exclude='debug_*' \
  --exclude='*.md' --exclude='.git' \
  . user@teito.link:/path/to/production/
```

#### 除外するファイル（デプロイ不要）
```
❌ test_*.html/php          # テストファイル
❌ debug_*.html/php         # デバッグファイル
❌ *.md                     # ドキュメントファイル
❌ .git/                    # Gitリポジトリ
❌ node_modules/            # npmパッケージ（存在する場合）
```

### 3. 本番環境設定確認
```php
// php.ini 又は config.php で確認:
display_errors = Off              // 画面表示OFF
log_errors = On                   // ログ記録ON
error_reporting = E_ALL & ~E_NOTICE  // 最小限ログ
session.cookie_secure = 1         // HTTPS環境
session.cookie_httponly = 1       // HTTPアクセス禁止
opcache.enable = 1                // OPcache有効化
```

### 4. キャッシュクリア
```bash
# PHP OPcache クリア（サーバ再起動 又は opcache_reset()）
# ブラウザキャッシュクリア指示（ユーザーに通知）
```

### 5. 本番での動作確認
```javascript
// ブラウザコンソールで実行:
window.DeploymentChecklist.runAll()

// 期待される結果:
// ✅ ENVIRONMENT: 'production'
// ✅ DEBUG.enabled: false
// ✅ 全モーダル/アコーディオン表示可能
// ✅ チェックリスト検索UI生成
// ✅ エラーログなし
```

### 6. 主要導線テスト（各ブラウザ）
- Chrome / Firefox / Safari / Edge
- スマートフォンブラウザ

**テスト項目:**
1. ページロード: 文字化け・レイアウト崩れなし
2. モーダル: 開閉・ネストモーダル正常動作
3. アコーディオン: 展開・収縮・ハッシュ遷移正常動作
4. チェックボックス: クリック時進捗バー更新
5. チェックリスト検索: キーワード入力→結果表示
6. admin画面: ログイン→編集→保存機能
7. コンソール: エラーなし、ログなし（production環境）

### 7. ロールバック手順（問題発生時）
```bash
# 事前バックアップから復元
cd c:\xampp\htdocs\task
tar -xzf data/backups/backup_YYYYMMDD.tar.gz

# 本番前のファイルバージョンに戻す
git checkout <commit-hash>  # Git使用時
# または手動で前日のバージョンを配置
```

---

## 確認チェックリスト

- [ ] テスト/デバッグファイル削除完了
- [ ] ログ抑制システム実装済み（DEBUG.log使用）
- [ ] env-detection.js が production 判定OK
- [ ] PHP本番設定ファイル配置
- [ ] .htaccess gzip/キャッシュ設定完了
- [ ] admin認証確認OK
- [ ] データバックアップ取得完了
- [ ] 本番ホスト設定（teito.link）確認
- [ ] ブラウザ複数で動作テスト完了
- [ ] コンソールエラーなし（production環境）

---

## トラブル対応

### Q: 本番環境でもコンソールログが出ている
**A:** env-detection.js の `isProduction()` が正しく `teito.link` を判定しているか確認。
```javascript
// ブラウザコンソールで確認:
window.ENVIRONMENT  // 'production' であること
window.DEBUG.enabled  // false であること
```

### Q: モーダルが表示されない
**A:** modal-fix.css が読み込まれているか確認。
```html
<!-- index.html で確認: -->
<link rel="stylesheet" href="css/modal-fix.css">
```

### Q: admin画面にアクセスできない
**A:** login.php の CSRF トークン・認証確認、かつ session.cookie_secure が ON/OFF 正しいか。

### Q: ハッシュ遷移がうまくいかない
**A:** target ID が存在するか確認。`window.DeploymentChecklist.testHashNavigation()` でテスト。

---

## 本番環境のモニタリング（1週間）

1. **ブラウザコンソール監視**: F12 で定期的に確認、エラーなしを確認
2. **PHPエラーログ確認**: `logs/error.log` をチェック
3. **ユーザー反応**: ジャンルごとのチェック完了率を確認
4. **パフォーマンス**: Network タブで読み込み時間を確認（> 3秒で要最適化）

---

## 最後に

デプロイ後、本番環境で `window.ENVIRONMENT === 'production'` かつ `window.DEBUG.enabled === false` であれば、ログ出力は完全に抑制されます。万一の問題時は即座にロールバック可能な状態を保ちながら、慎重に本番移行してください。

**デプロイ予定日時:** ___________

**デプロイ実行者:** ___________

**確認者:** ___________
