<?php declare(strict_types=1);

namespace Warbox\Category\Block;

use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;

/**
 * Class Landing
 * @package Warbox\Category\Block
 */
class Landing extends Template
{
    protected CategoryFactory $categoryFactory;
    protected CategoryRepository $categoryRepository;
    protected CollectionFactory $collectionFactory;
    protected Resolver $layerResolver;
    protected Output $helper;

    /**
     * Constructor
     *
     * @param Context            $context
     * @param CategoryFactory    $categoryFactory
     * @param CategoryRepository $categoryRepository
     * @param CollectionFactory  $collectionFactory
     * @param Resolver           $layerResolver
     * @param Output             $helper
     * @param array              $data
     */
    public function __construct(
        Template\Context $context,
        CategoryFactory $categoryFactory,
        CategoryRepository $categoryRepository,
        CollectionFactory $collectionFactory,
        Resolver $layerResolver,
        Output $helper,
        array $data = []
    ) {
        $this->categoryFactory    = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory  = $collectionFactory;
        $this->layerResolver      = $layerResolver;
        $this->helper             = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return Store|int|string
     * @throws NoSuchEntityException
     */
    private function getStoreId(): Store|int|string
    {
        $storeId = $this->_storeManager->getStore();
        return $storeId->getId();
    }

    /**
     * @return Category
     */
    public function getCurrentCategory(): Category
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    /**
     * @return Collection|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCategoriesCollection(): ?Collection
    {
        $category = $this->categoryRepository->get($this->getCurrentCategory()->getId());

        if ($category) {
            if ($category->getChildren()) {
                $subCategories = explode(',', $category->getChildren());
                if($subCategories) {
                    return $this->collectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addIsActiveFilter()
                        ->addUrlRewriteToResult()
                        ->addIdFilter($subCategories)
                        ->setStore($this->getStoreId())
                        ->addAttributeToSort('position');
                }
            }

            return null;
        }

        return null;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getDescription(): string
    {
        $current = $this->getCurrentCategory();
        return /* @noEscape */ $this->helper->categoryAttribute(
            $current,
            $current->getDescription(),
            'description'
        );
    }
}
