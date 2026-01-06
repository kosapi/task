// ネストされたモーダル管理スクリプト

document.addEventListener('DOMContentLoaded', function() {
  
  // 親モーダルに戻る子モーダルのリスト
  const childModalsWithParent = [
    { childId: 'modal-ticket-welfare', parentId: 'Modal6-3' },
    { childId: 'modal-welfare-many', parentId: 'Modal6-3' },
    { childId: 'modal-additional-uncollected', parentId: 'Modal6-3' },
    { childId: 'modal-teito-cancel', parentId: 'Modal6-3' },
    { childId: 'modal-go-app-cancel', parentId: 'Modal6-3' },
    { childId: 'modal-meter-mistake', parentId: 'Modal6-3' },
    { childId: 'modal-etc-statement', parentId: 'Modal6-3' }
  ];

  // 各子モーダルに親モーダル復帰イベントを設定
  childModalsWithParent.forEach(function(config) {
    const childModal = document.getElementById(config.childId);
    if (childModal) {
      childModal.addEventListener('hidden.bs.modal', function() {
        const parentModal = new bootstrap.Modal(document.getElementById(config.parentId));
        parentModal.show();
      });
    }
  });

  // data-nested-modal-target属性を持つボタンの処理（汎用）
  const nestedModalButtons = document.querySelectorAll('[data-nested-modal-target]');
  
  nestedModalButtons.forEach(function(button) {
    button.addEventListener('click', function() {
      const targetModalId = this.getAttribute('data-nested-modal-target').substring(1);
      
      // 最も近い親モーダルを探す
      const parentModalElement = this.closest('.modal');
      
      if (parentModalElement) {
        const parentModalId = parentModalElement.id;
        
        // 親モーダルを非表示
        const parentModal = bootstrap.Modal.getInstance(parentModalElement);
        if (parentModal) {
          parentModal.hide();
        }
        
        // 対象モーダルを表示
        const targetModalElement = document.getElementById(targetModalId);
        if (targetModalElement) {
          const targetModal = new bootstrap.Modal(targetModalElement);
          targetModal.show();
          
          // 対象モーダルが閉じられたら親モーダルを再表示
          targetModalElement.addEventListener('hidden.bs.modal', function() {
            const parentModal = new bootstrap.Modal(document.getElementById(parentModalId));
            parentModal.show();
          }, { once: true });
        }
      }
    });
  });
});
