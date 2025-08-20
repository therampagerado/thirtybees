/**
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.thirtybees.com for more information.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2024 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

$(function () {
  $('.groupBox').on('change', function () {
    var idCustomer = $('input[name="id_customer"]').val();
    if (typeof idCustomer === 'undefined' || idCustomer === '' || idCustomer === '0') {
      return;
    }
    var groups = [];
    $('.groupBox:checked').each(function () {
      groups.push($(this).val());
    });
    $.ajax({
      type: 'POST',
      url: window.currentIndex + '&ajax=1&action=updateCustomerGroups&token=' + window.token,
      data: { id_customer: idCustomer, 'groupBox': groups },
      dataType: 'json',
      success: function (data) {
        if (!data || typeof data.groups === 'undefined') {
          return;
        }
        var select = $('#id_default_group');
        var currentVal = select.val();
        select.empty();
        $.each(data.groups, function (i, group) {
          var option = $('<option>').val(group.id_group).text(group.name);
          select.append(option);
        });
        if (select.find('option[value="' + currentVal + '"]').length) {
          select.val(currentVal);
        }
      }
    });
  });
});
