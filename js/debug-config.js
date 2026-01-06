/**
 * デバッグログ制御
 * 環境別にログ出力を制御
 */

window.DEBUG = {
  // 本番環境では false、開発/ステージングでは true
  enabled: window.ENVIRONMENT !== 'production',
  
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
    if (this.enabled) {
      console.error(message, ...args);
    }
  }
};
