// チェック数集計＆表示
$(function(){
  const checkGroups = [
    {id: 'items0', countId: 'check-count0', total: 5},
    {id: 'items1', countId: 'check-count1', total: 4},
    {id: 'items2', countId: 'check-count2', total: 10},
    {id: 'items3', countId: 'check-count3', total: 10},
    {id: 'items4', countId: 'check-count4', total: 7},
    {id: 'items5', countId: 'check-count5', total: 8},
    {id: 'items6', countId: 'check-count6', total: 13},
    {id: 'items7', countId: 'check-count7', total: 7},
    {id: 'items8', countId: 'check-count8', total: 5}
  ];

  function updateCounts() {
    checkGroups.forEach(function(group){
      const checked = $('#' + group.id + ' :checked').length;
      if (checked === group.total) {
        $('#' + group.countId).html('<span class="badge badge-success" style="background-color:#28a745;color:#fff;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-size:1em;">完</span>');
      } else {
        $('#' + group.countId).html('<span class="badge badge-secondary" style="background-color:#1b3a2f;color:#fff;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-size:1em;">' + (group.total - checked) + '</span>');
      }
    });
  }

  checkGroups.forEach(function(group){
    $('#' + group.id + ' :checkbox').on('change', updateCounts);
  });

  updateCounts();
});
