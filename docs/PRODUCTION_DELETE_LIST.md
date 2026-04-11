# 本番環境：削除推奨ファイル一覧

作成日: 2026-03-31

注意: 削除前に必ずバックアップ（ZIP 等）を取り、動作確認用のテスト環境で検証してください。`config.php` と `includes/functions.php` は残してください。

ディレクトリ（完全削除推奨）
- admin/
- api/

ルートPHP（削除推奨）
- check_parking_images.php
- check_missing_images.php
- check_images.php
- check_gopay.php
- check_backup_gopay.php
- final_check.php
- env-check.php
- clear_opcache.php
- generate_missing_images_list.php
- fix_image_paths.php

JavaScript（削除推奨）
- js/accordion_link.js
- js/calsel_v10.js
- js/checklist-search.js
- js/checklist_v1.js
- js/mobile-optimizer.js
- js/deployment-checklist.js
- js/notices.js
- js/scroll.js

その他
- config-production.php

補足
- 既に `tools/generate_static.php` を導入しているため、CMS 管理用のPHP（上記の `admin/`, `api/` 等）は不要であれば完全削除して問題ありません。  
- `js/slogans.js` はクライアント側で曜日表示制御を行うため残すことを推奨します。  
- 削除作業は本番直接の上書きではなく、先に `tar/zip` のアーカイブを作成し検証後に反映してください。

アーカイブ済みの削除履歴（参考）: `data/backups/removed_files_20260331_062122.zip` と `docs/REMOVED_FILES.md`
