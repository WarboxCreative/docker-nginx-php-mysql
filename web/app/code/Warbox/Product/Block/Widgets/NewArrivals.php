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
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Warbox\Category\Block\Product as productHelper;

/**
 * Class NewArrivals
 *
 * @package Warbox\Product\Block\Widgets
 */
class NewArrivals extends View
{
    private CollectionFactory $collectionFactory;

    private Status $productStatus;

    private Visibility $productVisibility;

    private productHelper $product;

    /**
     * @param Context                                                $context
     * @param \Magento\Framework\Url\EncoderInterface                $urlEncoder
     * @param EncoderInterface                                       $jsonEncoder
     * @param StringUtils                                            $string
     * @param Product                                                $productHelper
     * @param ConfigInterface                                        $productTypeConfig
     * @param FormatInterface                                        $localeFormat
     * @param Session                                                $customerSession
     * @param ProductRepositoryInterface                             $productRepository
     * @param PriceCurrencyInterface                                 $priceCurrency
     * @param CollectionFactory                                      $collectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility              $productVisibility
     * @param \Warbox\Category\Block\Product                         $product
     * @param array                                                  $data
     *
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

        $blocks = $this->getLayout()->getAllBlocks();
        foreach ($blocks as $block) {
            /** @var \Magento\Framework\View\Element\Template $block */
            if ($block->getNameInLayout() === 'Magento_Theme::html/blocks/c-newArrivals.phtml' ) {
                $block->addChild('c-badges', 'Warbox\Category\Block\Product', ['template' => 'Magento_Theme::html/blocks/c-badges.phtml']);
            }
        }
    }

    /**
     * @return Collection|null
     */
    public function getNewProducts(): ?Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addPriceData()
            ->addUrlRewrite()
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addAttributeToSort('entity_id', 'desc')
            ->setPageSize(8);

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
