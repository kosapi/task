<?php
/**
 * 本番環境用設定ファイル
 * php.iniで設定されていない項目をここで補完
 */

// エラー表示の抑制（本番）
if (extension_loaded('xdebug')) {
  xdebug_set_filter(XDEBUG_FILTER_ERROR, XDEBUG_FILTER_NONE);
}

// エラーログレベル（本番は最小限）
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/error.log');

// セッション設定（セキュリティ）
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');

// その他
ini_set('default_charset', 'UTF-8');
ini_set('date.timezone', 'Asia/Tokyo');

// OPcache（本番で有効化推奨）
if (extension_loaded('Zend OPcache')) {
  ini_set('opcache.enable', '1');
  ini_set('opcache.enable_cli', '0');
  ini_set('opcache.memory_consumption', '256');
}
