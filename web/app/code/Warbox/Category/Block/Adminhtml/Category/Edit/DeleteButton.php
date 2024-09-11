<?php
declare(strict_types=1);

namespace Warbox\Category\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;

class DeleteButton extends \Magento\Catalog\Block\Adminhtml\Category\Edit\DeleteButton
{
    protected ManagerInterface $messageManager;
    protected CategoryFactory $categoryFactory;

    /**
     * @param CategoryFactory  $categoryFactory
     * @param ManagerInterface $messageManager
     * @param Context          $context
     * @param Tree             $categoryTree
     * @param Registry         $registry
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        ManagerInterface $messageManager,
        Context $context,
        Tree $categoryTree,
        Registry $registry
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->messageManager  = $messageManager;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory);
    }

    /**
     * Save button
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $_category = $this->getCategory();
        $catId = (int)$_category->getId();
        $category = $this->categoryFactory->create()->load($catId);

        if ($category->getId()) {
            $parents = explode('/', $category->getPath());
            array_shift($parents);
            if ($parents) {
                foreach ($parents as $parentId) {
                    $parent = $this->categoryFactory->create()->load((int)$parentId);
                    if ($parent->getData('is_pim_root_category') == 1) {
                        return [];
                    }
                }
            }
        }

        if ($catId && !in_array($catId, $this->getRootIds()) && $category->isDeleteable()) {
            return [
                'id' => 'delete',
                'label' => __('Delete'),
                'on_click' => "deleteConfirm('" .__('Are you sure you want to delete this category?') ."', '"
                    . $this->getDeleteUrl() . "', {data: {}})",
                'class' => 'delete',
                'sort_order' => 10
            ];
        }

        return [];
    }
}
