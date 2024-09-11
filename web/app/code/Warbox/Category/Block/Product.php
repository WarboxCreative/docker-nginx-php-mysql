<?php declare(strict_types=1);

namespace Warbox\Category\Block;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;

/**
 * Class Product
 * @package Warbox\Category\Block
 */
class Product extends View
{
    private string $badgeClass = '';

    private string $badgeText = '';

    private Configurable $configurable;

    private CollectionFactory $collectionFactory;

    private AttributeFactory $attributeFactory;

    private GalleryReadHandler $galleryReadHandler;

    private ProductAttributeMediaGalleryManagementInterface $gallery_management;

    /**
     * @param Context                                                              $context
     * @param EncoderInterface                                                     $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface                             $jsonEncoder
     * @param StringUtils                                                          $string
     * @param \Magento\Catalog\Helper\Product                                      $productHelper
     * @param ConfigInterface                                                      $productTypeConfig
     * @param FormatInterface                                                      $localeFormat
     * @param Session                                                              $customerSession
     * @param ProductRepositoryInterface                                           $productRepository
     * @param PriceCurrencyInterface                                               $priceCurrency
     * @param Configurable                                                         $configurable
     * @param CollectionFactory                                                    $collectionFactory
     * @param AttributeFactory                                                     $attributeFactory
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler                   $galleryReadHandler
     * @param \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface $gallery_management
     * @param array                                                                $data
     *
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        Configurable $configurable,
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory,
        GalleryReadHandler $galleryReadHandler,
        ProductAttributeMediaGalleryManagementInterface $gallery_management,
        array $data = []
    ) {
        $this->configurable      = $configurable;
        $this->collectionFactory = $collectionFactory;
        $this->attributeFactory  = $attributeFactory;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->gallery_management = $gallery_management;
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
    }

    /**
     * @param string $badgeClass
     *
     * @return void
     */
    public function setBadgeClass( string $badgeClass): void
    {
        $this->badgeClass = $badgeClass;
    }

    /**
     * @return string
     */
    public function getBadgeClass(): string
    {
        return $this->badgeClass;
    }

    /**
     * @param string $badgeText
     *
     * @return void
     */
    public function setBadgeText( string $badgeText): void
    {
        $this->badgeText = $badgeText;
    }

    /**
     * @return string
     */
    public function getBadgeText(): string
    {
        return $this->badgeText;
    }

    /**
     * @param $id
     *
     * @return \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigOptions( $id = null)
    {
        $product = $this->getProduct();
        if($id) {
            $product = $this->productRepository->getById($id);
        }

        $options = $this->configurable->getConfigurableAttributes($product);

        return $options;
    }

    /**
     * @return ProductInterface[]
     */
    public function getChildProducts(): array
    {
        $product = $this->getProduct();
        if($product->getTypeId() === 'configurable') {
            return $product->getTypeInstance()->getUsedProducts($product);
        }

        return [];
    }

    /**
     * @return Collection
     */
    public function getWorkbenchLinkedProducts(): Collection
    {
        $sku = $this->getProduct()->getSku();

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addFieldToSelect('gpc_linked_bundle_product')
            ->addFieldToFilter('gpc_linked_bundle_product', ['eq' => $sku]);

        return $collection;
    }

    /**
     * @param int $attrId
     *
     * @return array|string
     */
    public function getProductAttrCall(int $attrId)
    {
        $attr = $this->attributeFactory->create()->load($attrId);
        $attrCode = $attr->getAttributeCode();

        return str_replace('_', '', ucwords($attrCode, '_'));
    }

    /**
     * @param string $url
     * @param string $search
     *
     * @return string|null
     */
    public function findImageByName(string $url, string $search) : ?string
    {
        $base = $this->getRootDirectory()->getAbsolutePath() . 'pub';
        $dir = new \FilesystemIterator($base . $url);
        foreach ($dir as $fileInfo){
            $file = pathinfo($url . $fileInfo->getFilename());
            if($file['filename'] === $search) {
                return $url . $fileInfo->getFilename();
            }
        }

        return null;
    }

    /**
     * @param $product
     *
     * @return void
     */
    public function addGallery($product) : void
    {
        $this->galleryReadHandler->execute($product);
    }

    /**
     * @param string $sku
     *
     * @return array<ProductAttributeMediaGalleryEntryInterface>
     */
    public function getGalleryImages(string $sku) : array
    {
        return $this->gallery_management->getList($sku);
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
