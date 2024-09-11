<?php declare(strict_types=1);

namespace Warbox\Breadcrumbs\Block;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Registry;

/**
 * Class Breadcrumbs
 * @package Warbox\Breadcrumbs\Block
 */
class Breadcrumbs extends \Magento\Theme\Block\Html\Breadcrumbs
{
    protected Registry $registry;
    protected ?Data $catalogData = null;

    /**
     * @param Context  $context
     * @param Data     $catalogData
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Data $catalogData,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->catalogData = $catalogData;
        $this->registry    = $registry;
    }

    /**
     * Retrieve HTML title value separator (with space)
     *
     * @param bool|int|string|Store|null $store
     *
     * @return string
     */
    public function getTitleSeparator( Store|bool|int|string $store = null): string
    {
        $separator = (string)$this->_scopeConfig->getValue('catalog/seo/title_separator', ScopeInterface::SCOPE_STORE, $store);
        return ' ' . $separator . ' ';
    }

    /**
     * @return array
     */
    public function getCrumbs(): array
    {
        return $this->_crumbs;
    }

    /**
     * Preparing layout
     *
     * @return Breadcrumbs|\Magento\Catalog\Block\Breadcrumbs
     * @throws NoSuchEntityException|LocalizedException
     */
    protected function _prepareLayout(): Breadcrumbs|\Magento\Catalog\Block\Breadcrumbs
    {

        $title = [];
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'home', [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $path = $this->catalogData->getBreadcrumbPath();
            $product = $this->registry->registry('current_product');

            if ($product && count($path) == 1) {
                $categoryCollection = clone $product->getCategoryCollection();
                $categoryCollection->clear();
                $categoryCollection->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC)->addAttributeToFilter('path', array('like' => '1/' . $this->_storeManager->getStore()->getRootCategoryId() . '/%'));
                $categoryCollection->setPageSize(1);
                $breadcrumbCategories = $categoryCollection->getFirstItem()->getParentCategories();

                foreach ($breadcrumbCategories as $category) {
                    $catbreadcrumb = ['label' => $category->getName(), 'link' => $category->getUrl()];
                    $breadcrumbsBlock->addCrumb('category' . $category->getId(), $catbreadcrumb);
                    $title[] = $category->getName();
                }
                //add current product to breadcrumb
                $prodbreadcrumb = ['label' => $product->getName(), 'link' => ''];
                $breadcrumbsBlock->addCrumb('product' . $product->getId(), $prodbreadcrumb);
                $title[] = $product->getName();
            } else {
                foreach ($path as $name => $breadcrumb) {
                    $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                    $title[] = $breadcrumb['label'];
                }
            }
            $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($title)));

            return parent::_prepareLayout();
        }
        $path = $this->catalogData->getBreadcrumbPath();
        foreach ($path as $name => $breadcrumb) {
            $title[] = $breadcrumb['label'];
        }
        $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($title)));

        return parent::_prepareLayout();
    }
}
