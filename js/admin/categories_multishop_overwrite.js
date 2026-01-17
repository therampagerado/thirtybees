$(function () {
  if (typeof categoryMultishopOverwriteData === 'undefined' || !categoryMultishopOverwriteData) {
    return;
  }

  var differences = categoryMultishopOverwriteData.differences || [];
  if (!differences.length) {
    return;
  }

  var $form = $('#category_form');
  if (!$form.length) {
    return;
  }

  var modalId = 'category-multishop-overwrite-modal';
  var $modal = $('#' + modalId);
  if (!$modal.length) {
    var modalHtml = '' +
      '<div class="modal fade" id="' + modalId + '" tabindex="-1" role="dialog" aria-hidden="true">' +
      '  <div class="modal-dialog">' +
      '    <div class="modal-content">' +
      '      <div class="modal-header">' +
      '        <h4 class="modal-title"></h4>' +
      '      </div>' +
      '      <div class="modal-body">' +
      '        <p class="category-multishop-overwrite-message"></p>' +
      '        <div class="category-multishop-overwrite-list"></div>' +
      '      </div>' +
      '      <div class="modal-footer">' +
      '        <button type="button" class="btn btn-default" data-dismiss="modal" id="' + modalId + '-cancel"></button>' +
      '        <button type="button" class="btn btn-warning" id="' + modalId + '-empty"></button>' +
      '        <button type="button" class="btn btn-primary" id="' + modalId + '-confirm"></button>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>';
    $('body').append(modalHtml);
    $modal = $('#' + modalId);
  }

  var escapeHtml = function (value) {
    return $('<div>').text(value).html();
  };

  var listHtml = '<ul class="list-unstyled">';
  $.each(differences, function (index, item) {
    listHtml += '<li><strong>' + escapeHtml(categoryMultishopOverwriteShopLabel || 'Shop') + ':</strong> ' +
      escapeHtml(item.shop_name) +
      '<ul>';
    $.each(item.fields || [], function (fieldIndex, fieldLabel) {
      listHtml += '<li>' + escapeHtml(fieldLabel) + '</li>';
    });
    listHtml += '</ul></li>';
  });
  listHtml += '</ul>';

  $modal.find('.modal-title').text(categoryMultishopOverwriteTitle || '');
  $modal.find('.category-multishop-overwrite-message').text(categoryMultishopOverwriteMessage || '');
  $modal.find('.category-multishop-overwrite-list').html(listHtml);
  $modal.find('#' + modalId + '-confirm').text(categoryMultishopOverwriteConfirmLabel || '');
  $modal.find('#' + modalId + '-empty').text(categoryMultishopOverwriteEmptyLabel || '');
  $modal.find('#' + modalId + '-cancel').text(categoryMultishopOverwriteCancelLabel || '');

  var setActionAndSubmit = function (action) {
    var fieldName = categoryMultishopOverwriteActionField || 'category_multishop_overwrite_action';
    var $input = $form.find('input[name="' + fieldName + '"]');
    if (!$input.length) {
      $input = $('<input type="hidden">').attr('name', fieldName).appendTo($form);
    }
    $input.val(action);
    $form.submit();
  };

  $modal.find('#' + modalId + '-confirm').off('click').on('click', function () {
    setActionAndSubmit('overwrite_all');
  });

  $modal.find('#' + modalId + '-empty').off('click').on('click', function () {
    setActionAndSubmit('overwrite_empty');
  });

  $modal.modal({
    backdrop: 'static',
    keyboard: false
  });
  $modal.modal('show');
});
