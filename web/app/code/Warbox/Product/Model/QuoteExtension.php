<?php declare(strict_types=1);

namespace Warbox\Product\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Checkout\Model\ResourceModel\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * BSSCommerce Quote override
 *
 * @package Warbox\Product\Model
 */
class QuoteExtension extends \Bss\QuoteExtension\Model\QuoteExtension
{
    protected ProductFactory $productFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface            $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Checkout\Model\ResourceModel\Cart           $resourceCart
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface    $stockState
     * @param \Magento\Quote\Api\CartRepositoryInterface           $quoteRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository
     * @param \Magento\Catalog\Model\ProductFactory                $productFactory
     * @param array                                                $data
     */
    public function __construct(
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Cart $resourceCart,
        Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        array $data = []
    ) {
        parent::__construct(
            $eventManager,
            $scopeConfig,
            $storeManager,
            $resourceCart,
            $checkoutSession,
            $customerSession,
            $messageManager,
            $stockRegistry,
            $stockState,
            $quoteRepository,
            $productRepository,
            $data
        );
        $this->productFactory = $productFactory;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProduct($productInfo, $requestInfo = null) : static
    {
        $qtyInQuote = 0;
        $canAdd = true;
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);
        $productId = $product->getId();
        $finalProduct = $product;

        if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();

            if (array_key_exists('selected_configurable_option', $requestInfo)) {
                if ($request->getData('selected_configurable_option')) {
                    $childProduct = $this->_getProduct($request->getData('selected_configurable_option'));
                    $stockItem = $this->stockRegistry->getStockItem(
                        $childProduct->getId(),
                        $childProduct->getStore()->getWebsiteId()
                    );
                    $minimumQty = $stockItem->getMinSaleQty();
                    $finalProduct = $childProduct;
                }
            }

            $items = $this->getQuote()->getAllVisibleItems();
            if ($items) {
                foreach ( $items as $item ) {
                    $sku = $item->getSku();
                    if ( $sku === $finalProduct->getSku() ) {
                        $qtyInQuote = $item->getQty();
                    }
                }
            }

            $finalQty = $qtyInQuote + $request->getQty();

            if ($finalQty < $minimumQty) {
                $canAdd = false;
            }

            //If product was not found in cart and there is set minimal qty for it
            if (($minimumQty
                && $minimumQty > 0
                && !$request->getQty()
                && !$this->getQuote()->hasProductId($productId))
                || !$canAdd
            ) {
                $result = 'The fewest you may purchase is ' . $minimumQty;
                throw new LocalizedException(__($result));
                return $this;
            }
        }

        if ($productId) {
            try {
                if (!$canAdd) {
                    $result = 'The fewest you may purchase is ' . $minimumQty;
                    throw new LocalizedException(__($result));
                }
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            $this->checkResult($result, $product);
        } else {
            throw new LocalizedException(__('The product does not exist.'));
        }
        return $this;
    }

    /**
     * Update cart items information
     *
     * @param  array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateItems($data)
    {
        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            $this->formatCustomPrice($itemInfo);
            $this->formatDescription($itemInfo);
            $item = $this->getQuote()->getItemById($itemId);
            if (!$this->checkItem($item, $itemInfo, $itemId)) {
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : false;

            $prodId = $this->productFactory->create()->getIdBySku($item->getSku());
            $product = $this->_getProduct($prodId);
            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();

            if ($minimumQty && $qty < $minimumQty) {
                $result = 'The fewest you may purchase is ' . $minimumQty;
                throw new LocalizedException(__($result));
                return $this;
            }

            if ($qty > 0) {
                $item->setQty($qty);

                $update_price = false;

                $this->checkCustomPrice($itemInfo, $item, $qty, $update_price);

                $this->setDescriptionForItem($itemInfo, $item);

                if ($update_price && $item->getHasConfigurationUnavailableError()) {
                    $item->unsHasConfigurationUnavailableError();
                }

                $this->returnErrorMess($item);
                $this->returnNoticeMess($item, $itemInfo, $qtyRecalculatedFlag, $qty);
            }
        }

        if ($qtyRecalculatedFlag) {
            $this->messageManager->addNoticeMessage(
                __('We adjusted product quantities to fit the required increments.')
            );
        }

        return $this;
    }
}
