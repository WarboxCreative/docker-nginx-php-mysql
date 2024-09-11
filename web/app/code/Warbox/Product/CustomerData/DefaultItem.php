<?php
declare(strict_types=1);

namespace Warbox\Product\CustomerData;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Msrp\Helper\Data;

/**
 * Default item
 */
class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Data
     */
    protected $msrpHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ConfigurationPool
     */
    protected $configurationPool;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * Escaper
     *
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Image                         $imageHelper
     * @param Data                          $msrpHelper
     * @param UrlInterface                  $urlBuilder
     * @param ConfigurationPool             $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param Escaper|null                  $escaper
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        Image $imageHelper,
        Data $msrpHelper,
        UrlInterface $urlBuilder,
        ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        Escaper $escaper = null
    ) {
        parent::__construct($imageHelper, $msrpHelper, $urlBuilder, $configurationPool, $checkoutHelper);
    }

    /**
     * @inheritdoc
     */
    protected function doGetItemData(): array
    {
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->escaper->escapeHtml($this->item->getProduct()->getName());

        return [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'gpc_lead_time' => $this->item->getProduct()->getAttributeText('gpc_lead_time'),
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
        ];
    }

}
?>
