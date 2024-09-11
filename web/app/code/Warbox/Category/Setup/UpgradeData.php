<?php declare(strict_types=1);

namespace Warbox\Category\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class UpgradeData
 * @package Warbox\Category\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    protected CategorySetupFactory $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $installer = $setup;
            $installer->startSetup();

            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->addAttribute(
                Category::ENTITY, 'hero_image', [
                    'type' => 'varchar',
                    'label' => 'Hero Image',
                    'input' => 'image',
                    'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                    'required' => false,
                    'sort_order' => 5,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General Information',
                    'note' => 'This will show as the Hero banner on a Category Landing page.'
                ]
            );

            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->addAttribute(
                Category::ENTITY, 'list_image', [
                    'type' => 'varchar',
                    'label' => 'List Image',
                    'input' => 'image',
                    'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                    'required' => false,
                    'sort_order' => 5,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General Information',
                    'note' => 'This will show as the Main top-left image on a Category List page.'
                ]
            );
            $categorySetup->addAttribute(
                Category::ENTITY, 'icon_image', [
                    'type' => 'varchar',
                    'label' => 'Icon Image',
                    'input' => 'image',
                    'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                    'required' => false,
                    'sort_order' => 5,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General Information',
                    'note' => 'This will show as the image icon on a Category List page, underneath the description.'
                ]
            );

            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->addAttribute(
                Category::ENTITY, 'seo_content', [
                    'type' => 'text',
                    'label' => 'SEO Content',
                    'input' => 'textarea',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'required' => false,
                    'wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true,
                    'sort_order' => 6,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General Information'
                ]
            );

            $installer->endSetup();
        }
    }
}
