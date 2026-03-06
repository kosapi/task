// 環境判定（localhost / staging / production）
window.ENV = {
  isProduction: function() {
    return window.location.hostname === 'teito.link';
  },
  isLocal: function() {
    const host = window.location.hostname;
    return host === 'localhost' || host === '127.0.0.1';
  },
  isStaging: function() {
    // productionでもlocalでもない場合は仮本番（ステージング）扱い
    return !this.isProduction() && !this.isLocal();
  },
  getBaseUrl: function() {
    if (this.isProduction()) {
      return 'https://teito.link/task/';
    }
    // staging / local はホストをそのまま利用
    const protocol = window.location.protocol;
    const host = window.location.host;
    return protocol + '//' + host + '/task/';
  },
  getEnvironment: function() {
    if (this.isProduction()) return 'production';
    if (this.isLocal()) return 'local';
    return 'staging';
  }
};

// グローバル変数としてベースURLを設定
window.BASE_URL = window.ENV.getBaseUrl();
window.ENVIRONMENT = window.ENV.getEnvironment();
