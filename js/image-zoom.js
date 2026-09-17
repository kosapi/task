/**
 * image-zoom.js
 * モーダル内の画像をタップした際に、全画面で拡大表示・ピンチズーム・ドラッグ移動できる軽量ビューア
 */
(function () {
  'use strict';

  // スタイルシートの動的追加
  var style = document.createElement('style');
  style.textContent = [
    '/* モーダル内画像にズームインカーソルを適用 */',
    '.modal-body img { cursor: zoom-in; transition: opacity 0.2s; }',
    '.modal-body img:active { opacity: 0.85; }',
    '',
    '/* 拡大ビューア オーバーレイ */',
    '#image-zoom-overlay {',
    '  position: fixed;',
    '  top: 0;',
    '  left: 0;',
    '  width: 100vw;',
    '  height: 100vh;',
    '  background: rgba(0, 0, 0, 0.93);',
    '  z-index: 99999;',
    '  display: none;',
    '  align-items: center;',
    '  justify-content: center;',
    '  touch-action: none;',
    '  user-select: none;',
    '  -webkit-user-select: none;',
    '  opacity: 0;',
    '  transition: opacity 0.25s ease;',
    '}',
    '#image-zoom-overlay.active {',
    '  display: flex;',
    '  opacity: 1;',
    '}',
    '',
    '/* 拡大対象画像 */',
    '#image-zoom-img {',
    '  max-width: 96vw;',
    '  max-height: 90vh;',
    '  object-fit: contain;',
    '  border-radius: 4px;',
    '  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.8);',
    '  transform-origin: center center;',
    '  will-change: transform;',
    '  cursor: grab;',
    '}',
    '#image-zoom-img:active {',
    '  cursor: grabbing;',
    '}',
    '',
    '/* 閉じるボタン */',
    '#image-zoom-close {',
    '  position: absolute;',
    '  top: 16px;',
    '  right: 16px;',
    '  width: 44px;',
    '  height: 44px;',
    '  background: rgba(255, 255, 255, 0.2);',
    '  color: #ffffff;',
    '  border: 1px solid rgba(255, 255, 255, 0.4);',
    '  border-radius: 50%;',
    '  font-size: 24px;',
    '  line-height: 1;',
    '  display: flex;',
    '  align-items: center;',
    '  justify-content: center;',
    '  cursor: pointer;',
    '  z-index: 100000;',
    '  backdrop-filter: blur(4px);',
    '  -webkit-backdrop-filter: blur(4px);',
    '  transition: background 0.2s, transform 0.1s;',
    '}',
    '#image-zoom-close:hover, #image-zoom-close:active {',
    '  background: rgba(255, 255, 255, 0.4);',
    '  transform: scale(1.05);',
    '}',
    '',
    '/* 操作ヒントバッジ */',
    '#image-zoom-hint {',
    '  position: absolute;',
    '  bottom: 20px;',
    '  left: 50%;',
    '  transform: translateX(-50%);',
    '  background: rgba(0, 0, 0, 0.65);',
    '  color: #ffffff;',
    '  font-size: 13px;',
    '  padding: 6px 16px;',
    '  border-radius: 20px;',
    '  pointer-events: none;',
    '  z-index: 100000;',
    '  letter-spacing: 0.5px;',
    '  border: 1px solid rgba(255, 255, 255, 0.2);',
    '  transition: opacity 0.5s ease;',
    '}'
  ].join('\n');
  document.head.appendChild(style);

  // オーバーレイDOM要素の生成
  var overlay = document.createElement('div');
  overlay.id = 'image-zoom-overlay';
  overlay.innerHTML = [
    '<button type="button" id="image-zoom-close" aria-label="閉じる">&times;</button>',
    '<img id="image-zoom-img" alt="拡大画像">',
    '<div id="image-zoom-hint">ピンチまたはダブルタップで拡大</div>'
  ].join('');
  document.body.appendChild(overlay);

  var zoomImg = document.getElementById('image-zoom-img');
  var closeBtn = document.getElementById('image-zoom-close');
  var hintBadge = document.getElementById('image-zoom-hint');

  // 変形状態
  var scale = 1;
  var minScale = 1;
  var maxScale = 4.5;
  var posX = 0;
  var posY = 0;
  var isDragging = false;
  var startX = 0;
  var startY = 0;
  var initialDistance = 0;
  var initialScale = 1;
  var lastTapTime = 0;
  var hintTimer = null;

  function updateTransform(withTransition) {
    if (withTransition) {
      zoomImg.style.transition = 'transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1)';
    } else {
      zoomImg.style.transition = 'none';
    }
    zoomImg.style.transform = 'translate3d(' + posX + 'px, ' + posY + 'px, 0) scale(' + scale + ')';
  }

  function resetZoom() {
    scale = 1;
    posX = 0;
    posY = 0;
    updateTransform(false);
  }

  function openViewer(src) {
    if (!src) return;
    zoomImg.src = src;
    resetZoom();
    overlay.style.display = 'flex';
    // リフロー強制後にアクティブ化
    void overlay.offsetWidth;
    overlay.classList.add('active');

    // ヒント表示（3秒後にフェードアウト）
    if (hintBadge) {
      hintBadge.style.opacity = '1';
      clearTimeout(hintTimer);
      hintTimer = setTimeout(function () {
        hintBadge.style.opacity = '0';
      }, 3000);
    }
  }

  function closeViewer() {
    overlay.classList.remove('active');
    setTimeout(function () {
      if (!overlay.classList.contains('active')) {
        overlay.style.display = 'none';
        zoomImg.src = '';
        resetZoom();
      }
    }, 250);
  }

  // 閉じるイベント
  closeBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    closeViewer();
  });

  // 背景クリックで閉じる（画像自体のクリックは除く）
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) {
      closeViewer();
    }
  });

  // ESCキーで閉じる
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && overlay.classList.contains('active')) {
      closeViewer();
    }
  });

  // タッチ操作（ピンチズーム・パン・ダブルタップ）
  overlay.addEventListener('touchstart', function (e) {
    if (e.target !== zoomImg && e.target !== overlay) return;

    if (e.touches.length === 1) {
      // 1本指ドラッグ準備
      isDragging = true;
      startX = e.touches[0].clientX - posX;
      startY = e.touches[0].clientY - posY;

      // ダブルタップ判定
      var currentTime = new Date().getTime();
      var tapLength = currentTime - lastTapTime;
      if (tapLength < 300 && tapLength > 0) {
        // ダブルタップ：1倍なら2.5倍へ、拡大中なら1倍へリセット
        e.preventDefault();
        if (scale > 1.2) {
          scale = 1;
          posX = 0;
          posY = 0;
        } else {
          scale = 2.5;
          // タップ位置を中心に拡大
          var touch = e.touches[0];
          var rect = zoomImg.getBoundingClientRect();
          var offsetX = touch.clientX - (rect.left + rect.width / 2);
          var offsetY = touch.clientY - (rect.top + rect.height / 2);
          posX = -offsetX * 1.5;
          posY = -offsetY * 1.5;
        }
        updateTransform(true);
        isDragging = false;
      }
      lastTapTime = currentTime;
    } else if (e.touches.length === 2) {
      // 2本指ピンチ準備
      isDragging = false;
      initialDistance = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
      initialScale = scale;
    }
  }, { passive: false });

  overlay.addEventListener('touchmove', function (e) {
    if (!overlay.classList.contains('active')) return;
    e.preventDefault(); // 画面全体のスクロール防止

    if (e.touches.length === 1 && isDragging) {
      // パン（移動）
      if (scale > 1) {
        posX = e.touches[0].clientX - startX;
        posY = e.touches[0].clientY - startY;
        updateTransform(false);
      }
    } else if (e.touches.length === 2 && initialDistance > 0) {
      // ピンチズーム
      var currentDistance = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
      var newScale = initialScale * (currentDistance / initialDistance);
      if (newScale >= minScale && newScale <= maxScale) {
        scale = newScale;
        updateTransform(false);
      }
    }
  }, { passive: false });

  overlay.addEventListener('touchend', function (e) {
    if (e.touches.length === 0) {
      isDragging = false;
      // 縮小しすぎた場合は 1倍 にバウンスバック
      if (scale < 1) {
        scale = 1;
        posX = 0;
        posY = 0;
        updateTransform(true);
      }
    } else if (e.touches.length === 1) {
      // 2本指から1本指に戻った場合の座標再調整
      startX = e.touches[0].clientX - posX;
      startY = e.touches[0].clientY - posY;
      isDragging = true;
    }
  });

  // PCマウス操作（ドラッグとホイール拡大）
  overlay.addEventListener('mousedown', function (e) {
    if (e.target === zoomImg) {
      e.preventDefault();
      isDragging = true;
      startX = e.clientX - posX;
      startY = e.clientY - posY;
    }
  });

  window.addEventListener('mousemove', function (e) {
    if (isDragging && scale > 1) {
      e.preventDefault();
      posX = e.clientX - startX;
      posY = e.clientY - startY;
      updateTransform(false);
    }
  });

  window.addEventListener('mouseup', function () {
    isDragging = false;
  });

  // マウスホイールズーム
  overlay.addEventListener('wheel', function (e) {
    e.preventDefault();
    var delta = e.deltaY > 0 ? -0.25 : 0.25;
    var newScale = Math.min(Math.max(scale + delta, minScale), maxScale);
    if (newScale === minScale) {
      posX = 0;
      posY = 0;
    }
    scale = newScale;
    updateTransform(true);
  }, { passive: false });

  // ダブルクリック拡大（PC用）
  zoomImg.addEventListener('dblclick', function (e) {
    e.preventDefault();
    if (scale > 1.2) {
      scale = 1;
      posX = 0;
      posY = 0;
    } else {
      scale = 2.5;
    }
    updateTransform(true);
  });

  // モーダル内の画像クリックを拾って拡大表示
  document.addEventListener('click', function (e) {
    var img = e.target.closest('.modal-body img');
    if (!img) return;

    // アイコンや極小ボタン画像（幅40px以下など）は除外
    if (img.width > 0 && img.width < 45 && img.height < 45) return;

    // 実体画像URLの取得（遅延ロード対応）
    var src = img.getAttribute('data-lazy-src') || img.currentSrc || img.src;
    if (!src || src.indexOf('data:image/svg+xml') === 0) return;

    e.preventDefault();
    e.stopPropagation();
    openViewer(src);
  }, true);

  // グローバル公開
  window.openImageZoom = openViewer;
  window.closeImageZoom = closeViewer;
})();
