<?php declare(strict_types=1);

namespace Warbox\Sitemap\Block;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

/**
 * Class Sitemap
 * @package Warbox\Sitemap\Block
 */
class Sitemap extends Template
{
    protected PageRepositoryInterface $pageRepository;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected CollectionFactory $collectionFactory;
    protected StoreManagerInterface $storeManager;
    protected PostCollectionFactory $postCollectionFactory;

    /**
     * Constructor
     *
     * @param Context                 $context
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder   $searchCriteriaBuilder
     * @param CollectionFactory       $collectionFactory
     * @param StoreManagerInterface   $storeManager
     * @param PostCollectionFactory   $postCollectionFactory
     * @param array                   $data
     */
    public function __construct(
        Template\Context $context,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        PostCollectionFactory $postCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageRepository        = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory     = $collectionFactory;
        $this->storeManager          = $storeManager;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getAllPages(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('is_active', '1')->create();
        return $this->pageRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllCategories(): Collection
    {
        $store = $this->storeManager->getStore()->getId();
        return $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()
            ->addUrlRewriteToResult()
            ->setStore($store)
            ->addAttributeToSort('position');
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getAllPosts(): \Mageplaza\Blog\Model\ResourceModel\Post\Collection
    {
        return $this->postCollectionFactory->create()
            ->addAttributeToSelect('*');
    }
}
