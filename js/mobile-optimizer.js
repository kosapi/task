/**
 * モバイルフレンドリー最適化スクリプト
 */

(function() {
  'use strict';

  class MobileOptimizer {
    constructor() {
      this.isMobile = this.detectMobileDevice();
      this.isTablet = this.detectTabletDevice();
      if (this.isMobile || this.isTablet) {
        this.init();
      }
    }

    /**
     * モバイルデバイス判定
     */
    detectMobileDevice() {
      return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * タブレットデバイス判定
     */
    detectTabletDevice() {
      return /iPad|Android(?!.*Mobile)/i.test(navigator.userAgent);
    }

    /**
     * 初期化
     */
    init() {
      this.optimizeViewport();
      this.improveFormInputs();
      this.enhanceTouchInteraction();
      this.optimizeImages();
    }

    /**
     * ビューポート最適化
     */
    optimizeViewport() {
      const viewport = document.querySelector('meta[name="viewport"]');
      if (viewport) {
        viewport.setAttribute('content', 
          'width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover'
        );
      }
    }

    /**
     * フォーム入力最適化
     */
    improveFormInputs() {
      const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="search"], textarea');
      inputs.forEach(input => {
        // フォーカス時の枠線スタイル
        input.addEventListener('focus', function() {
          this.style.outline = '2px solid #667eea';
          this.style.outlineOffset = '2px';
        });
        
        input.addEventListener('blur', function() {
          this.style.outline = 'none';
        });
      });
    }

    /**
     * タッチ操作強化
     */
    enhanceTouchInteraction() {
      // チェックボックスのタップエリア拡張（微調整）
      const checkboxes = document.querySelectorAll('.form-check');
      checkboxes.forEach(checkbox => {
        checkbox.addEventListener('touchstart', function() {
          this.style.opacity = '0.9';
        });

        checkbox.addEventListener('touchend', function() {
          this.style.opacity = '1';
        });
      });
    }

    /**
     * 画像最適化
     */
    optimizeImages() {
      const images = document.querySelectorAll('img');
      images.forEach(img => {
        // 遅延読み込みを有効化
        if (!img.hasAttribute('loading')) {
          img.setAttribute('loading', 'lazy');
        }
      });
    }
  }

  // ドキュメント読み込み時に初期化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      window.mobileOptimizer = new MobileOptimizer();
    });
  } else {
    window.mobileOptimizer = new MobileOptimizer();
  }
})();
