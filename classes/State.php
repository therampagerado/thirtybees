<?php
/**
 * 2007-2016 PrestaShop
 *
 * Thirty Bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017 Thirty Bees
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
 * @author    Thirty Bees <contact@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017 Thirty Bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

/**
 * Class StateCore
 *
 * @since 1.0.0
 */
class StateCore extends ObjectModel
{
    // @codingStandardsIgnoreStart
    /** @var int Country id which state belongs */
    public $id_country;
    /** @var int Zone id which state belongs */
    public $id_zone;
    /** @var string 2 letters iso code */
    public $iso_code;
    /** @var string Name */
    public $name;
    /** @var bool Status for delivery */
    public $active = true;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'state',
        'primary' => 'id_state',
        'fields'  => [
            'id_country' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_zone'    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'iso_code'   => ['type' => self::TYPE_STRING, 'validate' => 'isStateIsoCode', 'required' => true, 'size' => 7],
            'name'       => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32],
            'active'     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];

    protected $webserviceParameters = [
        'fields' => [
            'id_zone'    => ['xlink_resource' => 'zones'],
            'id_country' => ['xlink_resource' => 'countries'],
        ],
    ];

    /**
     * @param bool $idLang
     * @param bool $active
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getStates($idLang = false, $active = false)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
		SELECT `id_state`, `id_country`, `id_zone`, `iso_code`, `name`, `active`
		FROM `'._DB_PREFIX_.'state`
		'.($active ? 'WHERE active = 1' : '').'
		ORDER BY `name` ASC'
        );
    }

    /**
     * Get a state name with its ID
     *
     * @param int $idState Country ID
     *
     * @return string State name
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getNameById($idState)
    {
        if (!$idState) {
            return false;
        }
        $cacheId = 'State::getNameById_'.(int) $idState;
        if (!Cache::isStored($cacheId)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                '
				SELECT `name`
				FROM `'._DB_PREFIX_.'state`
				WHERE `id_state` = '.(int) $idState
            );
            Cache::store($cacheId, $result);

            return $result;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Get a state id with its name
     *
     * @param string $id_state Country ID
     *
     * @return int state id
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getIdByName($state)
    {
        if (empty($state)) {
            return false;
        }
        $cacheId = 'State::getIdByName_'.pSQL($state);
        if (!Cache::isStored($cacheId)) {
            $result = (int) Db::getInstance()->getValue(
                '
				SELECT `id_state`
				FROM `'._DB_PREFIX_.'state`
				WHERE `name` = \''.pSQL($state).'\'
			'
            );
            Cache::store($cacheId, $result);

            return $result;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Get a state id with its iso code
     *
     * @param string $isoCode Iso code
     *
     * @return int state id
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getIdByIso($isoCode, $idCountry = null)
    {
        return Db::getInstance()->getValue(
            '
		SELECT `id_state`
		FROM `'._DB_PREFIX_.'state`
		WHERE `iso_code` = \''.pSQL($isoCode).'\'
		'.($idCountry ? 'AND `id_country` = '.(int) $idCountry : '')
        );
    }

    /**
     * @param int $idCountry
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getStatesByIdCountry($idCountry)
    {
        if (empty($idCountry)) {
            die(Tools::displayError());
        }

        return Db::getInstance()->executeS(
            '
			SELECT *
			FROM `'._DB_PREFIX_.'state` s
			WHERE s.`id_country` = '.(int) $idCountry
        );
    }

    /**
     * @param int $idState
     *
     * @return int
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function hasCounties($idState)
    {
        return count(County::getCounties((int) $idState));
    }

    /**
     * @param int $idState
     *
     * @return false|null|string
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getIdZone($idState)
    {
        if (!Validate::isUnsignedId($idState)) {
            die(Tools::displayError());
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            '
			SELECT `id_zone`
			FROM `'._DB_PREFIX_.'state`
			WHERE `id_state` = '.(int) $idState
        );
    }

    /**
     * Delete a state only if is not in use
     *
     * @return bool
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public function delete()
    {
        if (!$this->isUsed()) {
            // Database deletion
            $result = Db::getInstance()->delete($this->def['table'], '`'.$this->def['primary'].'` = '.(int) $this->id);
            if (!$result) {
                return false;
            }

            // Database deletion for multilingual fields related to the object
            if (!empty($this->def['multilang'])) {
                Db::getInstance()->delete(bqSQL($this->def['table']).'_lang', '`'.$this->def['primary'].'` = '.(int) $this->id);
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Check if a state is used
     *
     * @return bool
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public function isUsed()
    {
        return ($this->countUsed() > 0);
    }

    /**
     * Returns the number of utilisation of a state
     *
     * @return int count for this state
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public function countUsed()
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            '
			SELECT COUNT(*)
			FROM `'._DB_PREFIX_.'address`
			WHERE `'.$this->def['primary'].'` = '.(int) $this->id
        );

        return $result;
    }

    /**
     * @param int $idsStates
     * @param int $idZone
     *
     * @return bool
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public function affectZoneToSelection($idsStates, $idZone)
    {
        // cast every array values to int (security)
        $idsStates = array_map('intval', $idsStates);

        return Db::getInstance()->execute(
            '
		UPDATE `'._DB_PREFIX_.'state` SET `id_zone` = '.(int) $idZone.' WHERE `id_state` IN ('.implode(',', $idsStates).')
		'
        );
    }
}
