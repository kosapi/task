<?php
// 削除されたvisual_editor.phpへのアクセスをlive_editor.phpにリダイレクト
header('Location: /task/admin/live_editor.php', true, 301);
exit;
?>
