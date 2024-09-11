<?php declare(strict_types=1);
namespace Warbox\Product\Model;

use Bss\QuoteExtension\Model\QuoteExtension;
use Bss\QuoteExtension\Model\Session;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as MagentoConfigurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class Configurable
 * @package Warbox\Product\Model
 */
class Configurable
{
	private QuoteExtension $quote;
	private Session $session;
    private Cart $cart;
    private ProductRepositoryInterface $productRepository;
    private MagentoConfigurable $magentoConfigurable;
    private LoggerInterface $logger;

	/**
	 * @param QuoteExtension                           $quote
	 * @param Session                                  $session
	 * @param Cart                                     $cart
	 * @param ProductRepositoryInterface               $productRepository
	 * @param MagentoConfigurable                      $magentoConfigurable
	 * @param LoggerInterface                          $logger
	 */
    public function __construct(
	    QuoteExtension $quote,
	    Session $session,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        MagentoConfigurable $magentoConfigurable,
        LoggerInterface $logger
    ) {

        $this->productRepository   = $productRepository;
		$this->quote               = $quote;
		$this->session             = $session;
        $this->cart                = $cart;
        $this->magentoConfigurable = $magentoConfigurable;
        $this->logger              = $logger;
    }

	/**
	 * @param int  $parentId
	 * @param int  $childId
	 * @param bool $isQuote
	 *
	 * @return void
	 */
    protected function getAddComplexProductOptions(int $parentId, int $childId, bool $isQuote): void
    {
        try {
            $parent = $this->productRepository->getById($parentId);
            $child = $this->productRepository->getById($childId);
			if($isQuote) {
				$cart = $this->quote;
			} else {
				$cart = $this->cart;
			}

            if($this->magentoConfigurable->getParentIdsByChild($child->getId())) {
                // Configurable Variant
                $prodId = $parent->getId();
            } else {
                // Simple
                $prodId = $child->getId();
            }

            $params = [];
            $params['product'] = $prodId;
            $params['qty'] = '1';

            if($this->magentoConfigurable->getParentIdsByChild($child->getId())) {
                $options = [];

                $productAttributeOptions = $parent->getTypeInstance()->getConfigurableAttributesAsArray($parent);

                foreach($productAttributeOptions as $option) {
                    $options[$option['attribute_id']] = $child->getData($option['attribute_code']);
                }

                $params['super_attribute'] = $options;
            }

			if ($isQuote) {
				/** @var QuoteExtension $cart */
				$cart->addProduct($prodId, $params);
			} else {
				/** @var Cart $cart */
				$cart->addProduct($prodId, $params);
			}
            $cart->save();
        } catch(NoSuchEntityException|LocalizedException|Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

	/**
	 * @param int   $parentId
	 * @param array $optionIds
	 * @param bool  $isQuote
	 *
	 * @return void
	 */
    public function addComplexProductToQuote( int $parentId, array $optionIds, bool $isQuote=false): void
    {
        if(count($optionIds)) {
            try {
                $parent = $this->productRepository->getById($parentId);

                foreach($optionIds as $optionId) {
                    if(str_contains($optionId, '|')) {
                        $optionArr = explode('|', $optionId);
                        $parent = $this->productRepository->getById($optionArr[1]);
                        $childProd = $this->productRepository->getById($optionArr[0]);
                    } else {
                        $childProd = $this->productRepository->getById($optionId);
                    }

                    // Add extra product options, if any added.
                    $this->getAddComplexProductOptions((int)$parent->getId(), (int)$childProd->getId(), $isQuote);

                    if(!$isQuote) {
                        $this->cart->getQuote()->collectTotals();
                    } else {
                        $cartData = [];
                        $items = $this->quote->getItems();
                        /** @var \Magento\Quote\Model\Quote\Item $item */
                        foreach ($items as $item) {
                            $cartData[$item->getId()]['qty'] = $item->getQty();
                        }

                        if (!$this->quote->getCustomerSession()->getCustomerId()
                            && $this->session->getQuoteExtension()->getCustomerId()) {
                            $this->session->getQuoteExtension()->setCustomerId(null);
                        }
                        $cartData = $this->quote->suggestItemsQty($cartData);
                        $this->quote->updateItems($cartData)->save();
                        $this->quote->getQuote()->collectTotals();
                        $this->quote->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
                    }
                }
            } catch(NoSuchEntityException|Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
