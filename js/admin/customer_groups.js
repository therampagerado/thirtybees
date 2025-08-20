$(function() {
    var $form = $('#customer_form');
    if (!$form.length) {
        return;
    }
    var $defaultGroup = $('#id_default_group');
    $('.groupBox').on('change', function() {
        var groups = [];
        $('.groupBox:checked').each(function() {
            groups.push($(this).val());
        });
        $.ajax({
            type: 'POST',
            url: $form.attr('action') + '&ajax=1&action=updateCustomerGroups',
            dataType: 'json',
            data: {
                id_customer: $form.find('input[name="id_customer"]').val(),
                groupBox: groups,
                token: $form.find('input[name="token"]').val()
            },
            success: function(response) {
                if (!response) {
                    return;
                }
                var current = $defaultGroup.val();
                $defaultGroup.empty();
                $.each(response, function(i, group) {
                    $defaultGroup.append($('<option>', {
                        value: group.id_group,
                        text: group.name
                    }));
                });
                if ($defaultGroup.find('option[value="' + current + '"]').length) {
                    $defaultGroup.val(current);
                }
            }
        });
    });
});
