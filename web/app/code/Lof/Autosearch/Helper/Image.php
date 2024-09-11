<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Autosearch
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\Autosearch\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Image extends AbstractHelper
{
    protected \Magento\Catalog\Helper\Image $_imageHelper;

    /**
     * @param Context                       $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->_imageHelper = $imageHelper;
        parent::__construct($context);
    }

    /**
     * Get image URL of the given product
     *
     * @param Product    $product		Product
     * @param int        $w				Image width
     * @param int        $h				Image height
     * @param string     $imgVersion		Image version: image, small_image, thumbnail
     * @param mixed|null $file			Specific file
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImg( Product $product, int $w = 300, int $h=300, string $imgVersion='image', mixed $file=null): \Magento\Catalog\Helper\Image
    {
        $w = (!isset($w) || ($w==null)) ? 300 : $w;
        $imgVersion = (!isset($imgVersion) || ($imgVersion==null)) ? 'image' : $imgVersion;

        if (!$h || (int)$h == 0) {
            $image = $this->_imageHelper
            ->init($product, $imgVersion)
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepFrame(false);
            if ($file) {
                $image->setImageFile($file);
            }
            $image->resize($w);
        } else {
            $image = $this->_imageHelper
            ->init($product, $imgVersion);
            if ($file) {
                $image->setImageFile($file);
            }
            $image->resize($w, $h);
        }

        return $image;
    }

    /**
     * Get alternative image HTML of the given product
     *
     * @param Product    $product        Product
     * @param int                               $w              Image width
     * @param int                               $h              Image height
     * @param string                            $imgVersion     Image version: image, small_image, thumbnail
     * @return string
     */
    public function getAltImgHtml($product, $w, $h, $imgVersion='small_image', $column = 'position', $value = 1)
    {
        $product->load('media_gallery');
        if ($images = $product->getMediaGalleryImages()) {
            $image = $images->getItemByColumnValue($column, $value);
            if (isset($image) && $image->getUrl()) {
                $imgAlt = $this->getImg($product, $w, $h, $imgVersion, $image->getFile());
                if (!$imgAlt) {
                    return '';
                }
                return $imgAlt;
            } else {
                return '';
            }
        }
        return '';
    }
}
