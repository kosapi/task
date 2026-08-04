# 手動デプロイ用シンプルガイド

## 配置パス（ロリポップ等の例）
- 本番: `/public_html/task/` （サーバの公開ディレクトリ直下に task フォルダを作成して中身を置く）

## まず作る/用意するフォルダ（なければ作成）
- `/public_html/task/data/`
- `/public_html/task/uploads/`
- `/public_html/task/img/`（必要な画像を置く）

## 必ずアップするファイル/フォルダ
- ルート: `index.html` `.htaccess` `config.php` `config-production.php`
- 共通PHP: `includes/`
- フロントJS: `js/` （全て）
- フロントCSS: `css/` （全て）
- API: `api/get-content.php` `api/get-notices.php`
- 管理: `admin/` （全て）
- データ: `data/content.json` `data/notices.json`（中身は `[]` でOK）
- 画像: `img/`（必要分）
- アップロード置き場: `uploads/`（空でも可）

## アップしない（除外推奨）
- ドキュメント: `*.md`（README/DEPLOYMENT系）
- バックアップ/一時: `index.html.backup_*` `missing_images_list.*` `data/backups/`（必要なら手元保管）
- 各種チェック/検査系: `check_*.php` `env-check.php` `final_check.php` `fix_image_paths.php` `generate_missing_images_list.php`
- Git関連: `.git/` `.gitignore`

## 権限（目安）
- ディレクトリ: 755
- ファイル: 644
- `data/notices.json` は書き込みが必要なら 664（同一ユーザー実行なら 644 で可）

## 手順（最短）
1) 事前にローカルで `data/notices.json` を作成し、内容を `[]` にしておく
2) サーバの `/public_html/task/` を用意（なければ作成）
3) 上記「必ずアップする」一式をアップロード
4) 権限を確認（特に data/ と uploads/）
5) ブラウザで `https://<your-domain>/task/` を開き、画面表示を確認
6) 管理画面（`/task/admin/`）でお知らせを1件作成→トップでモーダル表示を確認

## 簡易チェックリスト
- [ ] `index.html` が最新
- [ ] `js/notices.js` と `api/get-notices.php` をアップ済み
- [ ] `data/notices.json` が存在し JSON 形式（`[]`）
- [ ] 管理画面にログインできる
- [ ] お知らせを作成するとトップで表示される
- [ ] 「今後表示しない」が動作（localStorage）

---
このファイルだけサーバに置かず、ローカル手元のメモとして使ってください。
