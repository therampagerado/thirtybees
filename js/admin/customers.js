$(function() {
  var idCustomer = parseInt($('input[name="id_customer"]').val(), 10);
  if (isNaN(idCustomer)) {
    idCustomer = 0;
  }
  var $defaultGroup = $('select[name="id_default_group"]');
  $('input[name^="groupBox"]').on('change', function() {
    if (!idCustomer) {
      return;
    }
    var groupIds = [];
    $('input[name^="groupBox"]:checked').each(function() {
      groupIds.push($(this).val());
    });
    $.ajax({
      type: 'POST',
      url: updateCustomerGroupsUrl,
      dataType: 'json',
      data: {
        id_customer: idCustomer,
        groupBox: groupIds
      },
      success: function(resp) {
        if (resp && resp.groups) {
          $defaultGroup.empty();
          $.each(resp.groups, function(i, group) {
            $('<option>', {
              value: group.id_group,
              text: group.name
            }).appendTo($defaultGroup);
          });
        }
      }
    });
  });
});
