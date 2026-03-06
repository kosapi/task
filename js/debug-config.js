/**
 * デバッグログ制御
 * 環境別にログ出力を制御
 */

const __DEBUG_IS_LOCAL = typeof window.ENV !== 'undefined' && window.ENV.isLocal && window.ENV.isLocal();

// 本番/ステージングではログを抑制（errorは残す）
if (!__DEBUG_IS_LOCAL) {
  console.log = () => {};
  console.info = () => {};
  console.warn = () => {};
}

window.DEBUG = {
  enabled: __DEBUG_IS_LOCAL,
  log: function(message, ...args) {
    if (this.enabled) {
      console.log(message, ...args);
    }
  },
  warn: function(message, ...args) {
    if (this.enabled) {
      console.warn(message, ...args);
    }
  },
  error: function(message, ...args) {
    console.error(message, ...args);
  }
};
