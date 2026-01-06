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
```bash
# テスト/デバッグ系を除外した同期
# 本番に以下を展開（テスト系は除外）
# ✅ index.html (更新版)
# ✅ 全JS/CSS (ログ削減版)
# ✅ config-production.php (新規)
# ✅ .htaccess (gzip追加版)
# ✅ admin/ (login.php 認証確認)
# ✅ data/ (最新JSON)
# ✅ includes/
# ✅ img/

# 除外:
# ❌ debug_*.html/php
# ❌ test_*.html/php
# ❌ puppeteer_test.js
# ❌ index_cleaned.html (既削除)
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
<link rel="stylesheet" href="modal-fix.css">
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
