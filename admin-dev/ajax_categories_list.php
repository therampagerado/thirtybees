<?php
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
 *  @author    thirty bees <contact@thirtybees.com>
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2017-2024 thirty bees
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

use Thirtybees\Core\DependencyInjection\ServiceLocator;
use Thirtybees\Core\Error\Response\JSendErrorResponse;

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}
include(_PS_ADMIN_DIR_.'/../config/config.inc.php');
require_once(_PS_ADMIN_DIR_.'/init.php');

ServiceLocator::getInstance()->getErrorHandler()->setErrorResponseHandler(new JSendErrorResponse(_PS_MODE_DEV_));

$query = Tools::getValue('q', false);
if (!$query || $query == '' || strlen($query) < 1) {
    exit;
}

$context = Context::getContext();

$sql = 'SELECT c.id_category, cl.name
        FROM `'._DB_PREFIX_.'category` c
        '.Shop::addSqlAssociation('category', 'c').'
        LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.id_category = c.id_category AND cl.id_lang = '.(int)$context->language->id.Shop::addSqlRestrictionOnLang('cl').')
        WHERE cl.name LIKE \'%'.pSQL($query).'%\'
        ORDER BY cl.name ASC';

$conn = Db::readOnly();
$items = $conn->getArray($sql);

if (Tools::getValue('returnType') === 'json') {
    $results = [];
    foreach ($items as $item) {
        $results[] = [
            'id' => (int)$item['id_category'],
            'name' => $item['name'],
        ];
    }
    echo json_encode($results);
} else {
    foreach ($items as $item) {
        echo trim($item['name']).'|'.(int)$item['id_category']."\n";
    }
}
