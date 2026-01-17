{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{extends file="helpers/form/form.tpl"}

{block name="script"}
	var ps_force_friendly_product = false;
	{if $multishop_overwrite_enabled}
		$(function () {
			var $form = $('#category_form');
			var $overwriteInput = $('#multishop_overwrite');
			var labels = {$multishop_overwrite_field_labels|json_encode};
			var checkUrl = '{$current|escape:'javascript':'UTF-8'}&token={$token|escape:'javascript':'UTF-8'}';
			var $modal = $('#category-multishop-overwrite-modal');
			var $details = $('#category-multishop-overwrite-details');

			var renderDetails = function (shops) {
				var html = '<ul class="list-unstyled">';
				$.each(shops, function (index, shop) {
					var fieldLabels = $.map(shop.fields, function (field) {
						return labels[field] || field;
					});
					html += '<li><strong>' + shop.name + '</strong>: ' + fieldLabels.join(', ') + '</li>';
				});
				html += '</ul>';
				$details.html(html);
			};

			$form.on('submit', function (event) {
				if ($overwriteInput.val()) {
					return true;
				}

				event.preventDefault();
				var data = $form.serializeArray();
				data.push({name: 'ajax', value: 1});
				data.push({name: 'action', value: 'CategoryShopDifferences'});
				$.post(checkUrl, $.param(data), function (response) {
					if (response && response.hasDifferences) {
						renderDetails(response.shops);
						$modal.modal('show');
					} else {
						$overwriteInput.val('all');
						$form[0].submit();
					}
				}, 'json');
			});

			$('#category-multishop-overwrite-all').on('click', function () {
				$overwriteInput.val('all');
				$modal.modal('hide');
				$form[0].submit();
			});

			$('#category-multishop-overwrite-empty').on('click', function () {
				$overwriteInput.val('empty');
				$modal.modal('hide');
				$form[0].submit();
			});
		});
	{/if}
{/block}

{block name="input"}
	{if $input.name == "link_rewrite"}
		<script type="text/javascript">
		{if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
			var PS_ALLOW_ACCENTED_CHARS_URL = 1;
		{else}
			var PS_ALLOW_ACCENTED_CHARS_URL = 0;
		{/if}
		</script>
		{$smarty.block.parent}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name="description"}
	{$smarty.block.parent}
	{if ($input.name == 'groupBox')}
		<div class="alert alert-info">
			<h4>{$input.info_introduction}</h4>
			<p>{$input.unidentified}<br />
			{$input.guest}<br />
			{$input.customer}</p>
		</div>
	{/if}
{/block}
{block name="input_row"}
	{$smarty.block.parent}
	{if ($input.name == 'thumb')}
	{$displayBackOfficeCategory}
	{/if}
{/block}

{block name="footer"}
	<input type="hidden" name="multishop_overwrite" id="multishop_overwrite" value="" />
	{$smarty.block.parent}
	<div class="modal fade" id="category-multishop-overwrite-modal" tabindex="-1" role="dialog" aria-labelledby="category-multishop-overwrite-title">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close'}"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="category-multishop-overwrite-title">{l s='Overwrite per-shop category values?'}</h4>
				</div>
				<div class="modal-body">
					<p>{l s='This category has different values in some shops. Saving now will overwrite those values.'}</p>
					<div id="category-multishop-overwrite-details"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel'}</button>
					<button type="button" class="btn btn-default" id="category-multishop-overwrite-empty">{l s='Fill empty values only'}</button>
					<button type="button" class="btn btn-primary" id="category-multishop-overwrite-all">{l s='Overwrite all shops'}</button>
				</div>
			</div>
		</div>
	</div>
{/block}
