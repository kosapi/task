// 入れ子モーダル（親モーダル ↔ サブモーダル）の100%絶対履歴スタック復元エンジン
(function () {
  var modalStack = [];
  var isRestoring = false;

  document.addEventListener('DOMContentLoaded', function () {

    // 1. モーダルが表示完了したとき（開いたモーダルを履歴スタックに記録）
    document.addEventListener('shown.bs.modal', function (event) {
      var modal = event.target;
      if (!modal || !modal.id) return;

      // 復元処理中でなく、スタック末尾と異なるなら履歴に追加
      if (!isRestoring) {
        if (modalStack.length === 0 || modalStack[modalStack.length - 1] !== modal.id) {
          modalStack.push(modal.id);
        }
      }
    });

    // 2. モーダルが完全に閉じられたとき（直前の親モーダルがあれば全自動でオープン復元）
    document.addEventListener('hidden.bs.modal', function (event) {
      var closedModal = event.target;
      if (!closedModal || !closedModal.id) return;

      if (isRestoring) return;

      // スタック末尾が今回閉じたモーダルなら取り除く
      if (modalStack.length > 0 && modalStack[modalStack.length - 1] === closedModal.id) {
        modalStack.pop();
      }

      // スタックに親モーダルが残っていれば即座に復元表示
      if (modalStack.length > 0) {
        var parentModalId = modalStack[modalStack.length - 1];
        var parentModalEl = document.getElementById(parentModalId);

        if (parentModalEl) {
          isRestoring = true;
          setTimeout(function () {
            var bsModal = bootstrap.Modal.getOrCreateInstance(parentModalEl);
            bsModal.show();
            setTimeout(function () {
              isRestoring = false;
            }, 300);
          }, 100);
        }
      }

      // 背景暗転・画面スクロールフリーズ防止の補正
      setTimeout(function () {
        if (document.querySelectorAll('.modal.show').length > 0) {
          document.body.classList.add('modal-open');
        } else {
          modalStack = []; // すべて閉じたらスタック初期化
        }
      }, 350);
    });

  });
})();
