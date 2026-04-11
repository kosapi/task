# 静的化・ローカル確認手順

概要
- `tools/generate_static.php` を使って `data/content.json` の内容を `index.html` に埋め込み、`static/index.html` を生成します。

再生成（ワークスペースのルートで実行）

```powershell
php tools/generate_static.php
```

注意: CLI 実行時に `config.php` 内の $_SERVER 未定義に関する警告が出ますが、出力自体は正常に生成されます。

ローカルで確認する方法（XAMPP）

1. 既存の `index.html` をバックアップする（必須）

```powershell
Copy-Item .\index.html .\index.html.bak -Force
```

2. 生成した静的ファイルを Apache の公開フォルダへ配置する（安全な方法）

- テスト用に別フォルダへコピーする場合:

```powershell
New-Item -ItemType Directory -Path C:\xampp\htdocs\task_static -Force
Copy-Item .\static\* C:\xampp\htdocs\task_static -Recurse -Force
```

- 既存の `/task/` を上書きして運用する場合（本番置換と同じ操作）:

```powershell
Copy-Item .\static\* C:\xampp\htdocs\task -Recurse -Force
```

3. Apache を起動してブラウザで確認

- テスト用コピーを使った場合: `http://localhost/task_static/`
- 既存を上書きした場合: `http://localhost/task/`

注意点
- `index.html` の `<base href="/task/">` は配置先に合わせて編集してください。例えばテスト用フォルダなら `/task_static/` に書き換えます。

```powershell
# PowerShell で置換例
(Get-Content static\index.html) -replace '<base href="/task/">','<base href="/task_static/">' | Set-Content static\index.html
```

- 生成ファイルはすでに `js/slogans.js` の API フェッチを無効化しているため、CMS API へのランタイム呼び出しは行いません。
- モーダルやアコーディオン等の JS はローカルでも動作しますが、外部CDNに依存するライブラリ（Google Fonts, Bootstrap CDN 等）はネットワークアクセスが必要です。

ロールバック

上書きした場合はバックアップを戻します。

```powershell
Copy-Item .\index.html.bak .\index.html -Force
```

次のステップ
- 他ページも同様に静的出力したい場合、`tools/generate_static.php` を拡張して複数ページを生成します。

問題があれば指示ください。
