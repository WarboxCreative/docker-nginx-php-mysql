<?php
declare(strict_types=1);

namespace Warbox\Attributes\Observer;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class ProductDeleteBefore
 * @package Warbox\Attributes\Observer
 */
class ProductDeleteBefore implements ObserverInterface
{
    protected ManagerInterface $messageManager;
    protected ProductRepositoryInterface $productRepository;

    /**
     * @param ManagerInterface           $messageManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->messageManager    = $messageManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Stops PIM products from being deleted from the admin.
     *
     * @param Observer $observer
     *
     * @return void
     *
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute( Observer $observer): void
    {
        $_product = $observer->getProduct();
        $product = $this->productRepository->get($_product->getSku());
        if ($product->getGpcPimProduct()) {
            $this->messageManager->addErrorMessage('You cannot delete GPC provided products.');
            throw new Exception('You cannot delete GPC provided products');
        }
    }
}
