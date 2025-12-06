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
 * @author    thirty bees <contact@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2024 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

/**
 * Class CustomerMessageAttachmentCore
 */
class CustomerMessageAttachmentCore extends ObjectModel
{
    /** @var int $id_customer_message */
    public $id_customer_message;

    /** @var string $file_name */
    public $file_name;

    /** @var string $original_name */
    public $original_name;

    /** @var string|null $mime */
    public $mime;

    /** @var bool */
    protected static $tableChecked = false;

    /** @var array Object model definition */
    public static $definition = [
        'table'   => 'customer_message_attachment',
        'primary' => 'id_customer_message_attachment',
        'fields'  => [
            'id_customer_message' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'file_name'           => ['type' => self::TYPE_STRING, 'size' => 64, 'required' => true],
            'original_name'       => ['type' => self::TYPE_STRING, 'size' => 255, 'required' => true],
            'mime'                => ['type' => self::TYPE_STRING, 'size' => 255],
        ],
        'keys' => [
            'customer_message_attachment' => [
                'id_customer_message' => ['type' => ObjectModel::KEY, 'columns' => ['id_customer_message']],
            ],
        ],
    ];

    /**
     * Ensure the database table exists before using it.
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public static function ensureTableExists(): void
    {
        if (static::$tableChecked) {
            return;
        }

        $createSql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.bqSQL(static::$definition['table']).'` (
            `'.bqSQL(static::$definition['primary']).'` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer_message` INT(11) UNSIGNED NOT NULL,
            `file_name` VARCHAR(64) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `mime` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`'.bqSQL(static::$definition['primary']).'`),
            KEY `id_customer_message` (`id_customer_message`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

        Db::getInstance()->execute($createSql);
        static::$tableChecked = true;
    }

    /**
     * @param int $customerMessageId
     *
     * @return static[]
     *
     * @throws PrestaShopException
     */
    public static function getByCustomerMessageId(int $customerMessageId): array
    {
        static::ensureTableExists();

        $rows = Db::readOnly()->getArray(
            (new DbQuery())
                ->select('*')
                ->from(static::$definition['table'])
                ->where('`id_customer_message` = '.(int) $customerMessageId)
        );

        $attachments = [];
        foreach ($rows as $row) {
            $attachment = new static();
            $attachment->hydrate($row);
            $attachments[] = $attachment;
        }

        return $attachments;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return _PS_UPLOAD_DIR_.basename($this->file_name);
    }

    /**
     * @return bool
     */
    public function fileExists(): bool
    {
        $path = $this->getFilePath();

        return $path && file_exists($path) && is_file($path);
    }

    /**
     * @param string $legacyFileName
     *
     * @return static
     */
    public static function fromLegacy(string $legacyFileName): self
    {
        $attachment = new static();
        $attachment->id_customer_message = 0;
        $attachment->file_name = $legacyFileName;
        $attachment->original_name = basename($legacyFileName);
        $attachment->mime = null;

        return $attachment;
    }
}

