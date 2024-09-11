<?php declare(strict_types=1);

namespace Warbox\Category\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;

/**
 * Class ListProduct
 * @package Warbox\Category\Block
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected Product $productHelper;

    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        Product $productHelper,
        array $data = [],
        ?OutputHelper $outputHelper = null
    ) {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data,
            $outputHelper
        );

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
     * @param $product
     *
     * @return void
     */
    public function addGallery($product) : void
    {
        $this->productHelper->addGallery($product);
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
        return $this->productHelper->getCachedGalleryImage($imgFile, $product, $size);
    }
}
