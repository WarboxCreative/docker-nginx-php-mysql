<?php
declare(strict_types=1);

namespace Warbox\Product\Block\Widgets;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Warbox\Category\Block\Product as productHelper;

/**
 * Class Offers
 *
 * @package Warbox\Product\Block\Widgets
 */
class Offers extends View
{
    private CollectionFactory $collectionFactory;

    private Status $productStatus;

    private Visibility $productVisibility;

    private productHelper $product;

    private CategoryCollection $categoryCollection;

    /**
     * @param Context                                                         $context
     * @param \Magento\Framework\Url\EncoderInterface                         $urlEncoder
     * @param EncoderInterface                                                $jsonEncoder
     * @param StringUtils                                                     $string
     * @param Product                                                         $productHelper
     * @param ConfigInterface                                                 $productTypeConfig
     * @param FormatInterface                                                 $localeFormat
     * @param Session                                                         $customerSession
     * @param ProductRepositoryInterface                                      $productRepository
     * @param PriceCurrencyInterface                                          $priceCurrency
     * @param CollectionFactory                                               $collectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status          $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility                       $productVisibility
     * @param \Warbox\Category\Block\Product                                  $product
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     * @param array                                                           $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        CollectionFactory $collectionFactory,
        Status $productStatus,
        Visibility $productVisibility,
        productHelper $product,
        CategoryCollection $categoryCollection,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->collectionFactory = $collectionFactory;
        $this->productStatus     = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->product           = $product;
        $this->categoryCollection = $categoryCollection;

        $blocks = $this->getLayout()->getAllBlocks();
        foreach ($blocks as $block) {
            /** @var \Magento\Framework\View\Element\Template $block */
            if ($block->getNameInLayout() === 'Magento_Theme::html/blocks/c-bestsellers.phtml' ) {
                $block->addChild('c-badges', 'Warbox\Category\Block\Product', ['template' => 'Magento_Theme::html/blocks/c-badges.phtml']);
            }
            if ($block->getNameInLayout() === 'Magento_Theme::html/blocks/c-specialOffers.phtml' ) {
                $block->addChild('c-badges', 'Warbox\Category\Block\Product', ['template' => 'Magento_Theme::html/blocks/c-badges.phtml']);
            }
        }
    }

    /**
     * @param array $category_ids
     * @param int   $count
     *
     * @return Collection|null
     */
    public function getProductsByCategory(array $category_ids, int $count = 8): ?Collection
    {
        if (empty($category_ids)) {
            return null;
        }

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addPriceData()
            ->addUrlRewrite()
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addCategoriesFilter(['in' => $category_ids])
            ->setPageSize($count);

        return $collection->count() > 0 ? $collection : null;
    }

    /**
     * @param array $categories
     * @param int   $count
     *
     * @return Collection|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsByCategoryUrl(array $categories, int $count = 8): ?Collection
    {
        $category_ids = [];

        if (empty($categories)) {
            return null;
        }

        foreach ($categories as $cat_name) {
            $catColl = $this->categoryCollection->create();
            $category_ids = $catColl->addAttributeToSelect('*')
                ->addFieldToFilter('url_key', $cat_name)->getAllIds();
        }


        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addPriceData()
            ->addUrlRewrite()
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addCategoriesFilter(['in' => $category_ids])
            ->setPageSize($count);

        return $collection->count() > 0 ? $collection : null;
    }

    /**
     * @param string $url
     * @param string $search
     *
     * @return string|null
     */
    public function findImageByName(string $url, string $search) : ?string
    {
        return $this->product->findImageByName($url, $search);
    }

    /**
     * @param string $sku
     *
     * @return array<ProductAttributeMediaGalleryEntryInterface>
     */
    public function getGalleryImages(string $sku) : array
    {
        return $this->product->getGalleryImages($sku);
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
        return $this->product->getCachedGalleryImage($imgFile,$product,$size);
    }
}
