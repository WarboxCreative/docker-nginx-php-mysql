<?php declare(strict_types=1);

namespace Warbox\Navigation\Block;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Navigation
 * @package Warbox\Navigation\Block
 */
class Navigation extends Template
{
    protected CategoryFactory $categoryFactory;
    protected CategoryRepository $categoryRepository;
    protected CollectionFactory $collectionFactory;

    /**
     * Constructor
     *
     * @param Context            $context
     * @param CategoryFactory    $categoryFactory
     * @param CategoryRepository $categoryRepository
     * @param CollectionFactory  $collectionFactory
     * @param array              $data
     */
    public function __construct(
        Template\Context $context,
        CategoryFactory $categoryFactory,
        CategoryRepository $categoryRepository,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->categoryFactory    = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory  = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getNavigationItems(): array
    {
        $navArray   = [];
        $categories = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()
            ->addRootLevelFilter()
            ->addUrlRewriteToResult()
            ->addAttributeToFilter('include_in_menu', ['eq' => 1])
            ->setStore($this->getStoreId());

        /** @var $category $first */
        foreach($categories as $category) {
            if ($category->getData('is_pim_root_category')) {
                $firstLevel = $this->getSubCategoriesCollection($category->getId());
                if (null !== $firstLevel) {
                    /** @var Category $first */
                    foreach($firstLevel as $first) {
                        $navArray[$first->getId()]['item']['id']   = $first->getId();
                        $navArray[$first->getId()]['item']['name'] = $first->getName();
                        $navArray[$first->getId()]['item']['url']  = $first->getUrl();
                        $navArray[$first->getId()]['children']     = [];
                        $secondLevel = $this->getSubCategoriesCollection($first->getId());
                        if (null !== $secondLevel) {
                            /** @var Category $second */
                            foreach($secondLevel as $second) {
                                $navArray[$first->getId()]['children'][$second->getId()]['item']['id']    = $second->getId();
                                $navArray[$first->getId()]['children'][$second->getId()]['item']['name']  = $second->getName();
                                $navArray[$first->getId()]['children'][$second->getId()]['item']['url']   = $second->getUrl();
                                $navArray[$first->getId()]['children'][$second->getId()]['item']['image'] = $second->getImageUrl();
                                $navArray[$first->getId()]['children'][$second->getId()]['children']      = [];
                                $thirdLevel = $this->getSubCategoriesCollection($second->getId());
                                if (null !== $thirdLevel) {
                                    /** @var Category $third */
                                    foreach($thirdLevel as $third) {
                                        $navArray[$first->getId()]['children'][$second->getId()]['children'][$third->getId()]['item']['id']   = $third->getId();
                                        $navArray[$first->getId()]['children'][$second->getId()]['children'][$third->getId()]['item']['name'] = $third->getName();
                                        $navArray[$first->getId()]['children'][$second->getId()]['children'][$third->getId()]['item']['url']  = $third->getUrl();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $navArray;
    }

    /**
     * @return int|\Magento\Store\Model\Store|string
     * @throws NoSuchEntityException
     */
    private function getStoreId()
    {
        $storeId = $this->_storeManager->getStore();
        return $storeId->getId();
    }

    /**
     * @param int|string $categoryId
     *
     * @return Collection|null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getSubCategoriesCollection( int|string $categoryId): ?Collection
    {
        $category = $this->categoryRepository->get($categoryId);

        if ($category) {
            if ($category->getChildren()) {
                $subCategories = explode(',', $category->getChildren());
                if($subCategories) {
                    return $this->collectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addIsActiveFilter()
                        ->addUrlRewriteToResult()
                        ->addAttributeToFilter('include_in_menu', ['eq' => 1])
                        ->addIdFilter($subCategories)
                        ->setStore($this->getStoreId())
                        ->addAttributeToSort('position');
                }
            }

            return null;
        }

        return null;
    }
}
