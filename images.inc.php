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

/**
  * @deprecated 1.5.0
  */
function cacheImage($image, $cacheImage, $size, $imageType = 'jpg', $disableCache = false)
{
    Tools::displayAsDeprecated();
    return ImageManager::thumbnail($image, $cacheImage, $size, $imageType, $disableCache);
}

/**
 * @deprecated 1.5.0
 */
function checkImage($file, $maxFileSize = 0)
{
    Tools::displayAsDeprecated();
    return ImageManager::validateUpload($file, $maxFileSize);
}

/**
 * @deprecated 1.5.0
 */
function checkImageUploadError($file)
{
    return ImageManager::getErrorFromCode($file['error']);
}

/**
 *  @deprecated 1.5.0
 */
function isPicture($file, $types = null)
{
    Tools::displayAsDeprecated();
    return ImageManager::isRealImage($file['tmp_name'], $file['type'], $types);
}

/**
  * @deprecated 1.5.0
  */
function checkIco($file, $maxFileSize = 0)
{
    Tools::displayAsDeprecated();
    return ImageManager::validateIconUpload($file, $maxFileSize);
}

/**
  * @deprecated 1.5.0
  */
function imageResize($sourceFile, $destFile, $destWidth = null, $destHeight = null, $fileType = 'jpg')
{
    Tools::displayAsDeprecated();
    return ImageManager::resize($sourceFile, $destFile, $destWidth, $destHeight, $fileType);
}

/**
 * @deprecated 1.5.0
 */
function imageCut($srcFile, $destFile, $destWidth = null, $destHeight = null, $fileType = 'jpg', $destX = 0, $destY = 0)
{
    Tools::displayAsDeprecated();
    if (isset($srcFile['tmp_name'])) {
        return ImageManager::cut($srcFile['tmp_name'], $destFile, $destWidth, $destHeight, $fileType, $destX, $destY);
    }
    return false;
}

/**
 * @deprecated 1.5.0
 */
function createSrcImage($type, $filename)
{
    Tools::displayAsDeprecated();
    return ImageManager::create($type, $filename);
}

/**
 * @deprecated 1.5.0
 */
function createDestImage($width, $height)
{
    Tools::displayAsDeprecated();
    return ImageManager::createWhiteImage($width, $height);
}

/**
 * @deprecated 1.5.0
 */
function returnDestImage($type, $ressource, $filename)
{
    Tools::displayAsDeprecated();
    return ImageManager::write($type, $ressource, $filename);
}

/**
 *  @deprecated 1.5.0
 */
function deleteImage($id_item, $id_image = null)
{
    Tools::displayAsDeprecated();

    // Category
    if (!$id_image) {
        $path = _PS_CAT_IMG_DIR_;
        $table = 'category';

        if ($tmpImage = ImageManager::getSourceImage(_PS_TMP_IMG_DIR_, $table.'_'.$id_item, null, false)) {
            unlink($tmpImage);
        }
        if ($sourceImage = ImageManager::getSourceImage($path, $id_item, null, false)) {
            unlink($sourceImage);
        }

    /* Auto-generated images */
    $imagesTypes = ImageType::getImagesTypes();
        foreach ($imagesTypes as $k => $imagesType) {
            if ($imageByType = ImageManager::getSourceImage($path, $id_item.'-'.$imagesType['name'], null, false)) {
                unlink($imageByType);
            }
        }
    } else {
        // Product

        $path = _PS_PROD_IMG_DIR_;
        $table = 'product';
        $image = new Image($id_image);
        $image->id_product = $id_item;

        if ($sourceImage = ImageManager::getSourceImage($path.$image->getImgFolder(), $image->id, null, false)) {
            unlink($sourceImage);
        }

        /* Auto-generated images */
        $imagesTypes = ImageType::getImagesTypes();
        foreach ($imagesTypes as $k => $imagesType) {
            if ($imageByType = ImageManager::getSourceImage($path.$image->getImgFolder(), $image->id.'-'.$imagesType['name'], null, false)) {
                unlink($imageByType);
            }
        }
    }

    /* BO "mini" image */
    if ($tmpImage = ImageManager::getSourceImage(_PS_TMP_IMG_DIR_, $table.'_mini_'.$id_item, null, false)) {
        unlink($tmpImage);
    }
    return true;
}
