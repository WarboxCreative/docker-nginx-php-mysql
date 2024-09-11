<?php declare(strict_types=1);

namespace Warbox\Category\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product as MagentoProduct;

/**
 * Class AbstractProduct
 * @package Warbox\Category\Block
 */
class AbstractProduct extends \Magento\Catalog\Block\Product\AbstractProduct
{
    protected Product $productHelper;

    /**
     * @param \Warbox\Category\Block\Product         $productHelper
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param array                                  $data
     */
    public function __construct(
        Product $productHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productHelper = $productHelper;
    }

    /**
     * @param string $url
     * @param string $search
     *
     * @return string|null
     */
    public function findImageByName(string $url, string $search) : ?string
    {
        return $this->productHelper->findImageByName($url, $search);
    }

    /**
     * @param string         $imgFile
     * @param MagentoProduct $product
     * @param string         $size
     *
     * @return string
     */
    public function getCachedGalleryImage(
        string $imgFile,
        MagentoProduct $product,
        string $size = 'category_page_grid'
    ) : string {
        return $this->_imageHelper->init($product, $size)
            ->setImageFile($imgFile)
            ->getUrl();
    }
}
