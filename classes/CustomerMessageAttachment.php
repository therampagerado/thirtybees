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
    /** @var int */
    public $id_customer_message;

    /** @var string */
    public $file_name;

    /** @var string */
    public $original_name;

    /** @var string */
    public $date_add;

    /**
     * @var array Object model definition
     */
    public static $definition = [
        'table'   => 'customer_message_attachment',
        'primary' => 'id_customer_message_attachment',
        'fields'  => [
            'id_customer_message' => ['type' => self::TYPE_INT, 'dbType' => 'int(11)'],
            'file_name'           => ['type' => self::TYPE_STRING, 'size' => 255],
            'original_name'       => ['type' => self::TYPE_STRING, 'size' => 255],
            'date_add'            => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'dbNullable' => false],
        ],
        'keys'    => [
            'customer_message_attachment' => [
                'id_customer_message' => ['type' => ObjectModel::KEY, 'columns' => ['id_customer_message']],
            ],
        ],
    ];

    /**
     * Ensure the attachment table exists.
     *
     * @return void
     */
    public static function createTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'customer_message_attachment` (
            `id_customer_message_attachment` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer_message` INT(11) UNSIGNED NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_customer_message_attachment`),
            KEY `id_customer_message` (`id_customer_message`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

        Db::getInstance()->execute($sql);
    }

    /**
     * @param int[] $messageIds
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     */
    public static function getByMessageIds(array $messageIds): array
    {
        if (!$messageIds) {
            return [];
        }

        $ids = array_map('intval', $messageIds);

        $rows = Db::readOnly()->getArray(
            (new DbQuery())
                ->select('cma.*')
                ->from('customer_message_attachment', 'cma')
                ->where('cma.`id_customer_message` IN ('.implode(',', $ids).')')
                ->orderBy('cma.`id_customer_message_attachment` ASC')
        );

        $attachments = [];
        foreach ($rows as $row) {
            $attachments[(int)$row['id_customer_message']][] = $row;
        }

        return $attachments;
    }

    /**
     * @param int $messageId
     *
     * @return void
     */
    public static function deleteByMessageId(int $messageId): void
    {
        Db::getInstance()->delete(
            bqSQL(static::$definition['table']),
            'id_customer_message = '.(int)$messageId
        );
    }

    /**
     * @param array $fileAttachments
     * @param array $errors
     *
     * @return array
     */
    public static function uploadAttachments(array $fileAttachments, array &$errors): array
    {
        static::createTable();

        $storedAttachments = [];
        foreach ($fileAttachments as $fileAttachment) {
            if (!empty($fileAttachment['rename']) && !empty($fileAttachment['tmp_name'])) {
                $destination = _PS_UPLOAD_DIR_.basename($fileAttachment['rename']);
                if (rename($fileAttachment['tmp_name'], $destination)) {
                    @chmod($destination, 0664);
                    $storedAttachments[] = [
                        'file_name'     => basename($fileAttachment['rename']),
                        'original_name' => $fileAttachment['name'],
                    ];
                } else {
                    $errors[] = Tools::displayError('An error occurred during the file upload process.');
                }
            } elseif (!empty($fileAttachment['name']) && ($fileAttachment['error'] != 0)) {
                $errors[] = Tools::displayError('An error occurred during the file upload process.');
            }
        }

        return $storedAttachments;
    }

    /**
     * @param int $messageId
     * @param array $storedAttachments
     *
     * @return void
     */
    public static function persistAttachments(int $messageId, array $storedAttachments): void
    {
        foreach ($storedAttachments as $attachment) {
            $record = new CustomerMessageAttachment();
            $record->id_customer_message = $messageId;
            $record->file_name = $attachment['file_name'];
            $record->original_name = $attachment['original_name'];
            $record->add();
        }
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return _PS_UPLOAD_DIR_.basename($this->file_name);
    }
}
