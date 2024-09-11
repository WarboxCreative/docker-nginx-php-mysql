<?php
declare(strict_types=1);

namespace Warbox\Attributes\Block\Adminhtml\Product\Attribute;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class Edit
 *
 * @package Warbox\Attributes\Block\Adminhtml\Product\Attribute\Edit
 */
class Edit extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit
{
    /**
     * Construct block
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_product_attribute';

        parent::_construct();

        /** @var AbstractAttribute $entityAttribute */
        $entityAttribute = $this->_coreRegistry->registry('entity_attribute');

        $this->buttonList->update('save', 'label', __('Save Attribute'));
        $this->buttonList->update('save', 'class', 'save primary');
        $this->buttonList->update(
            'save',
            'data_attribute',
            ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]
        );

        if ($this->getRequest()->getParam('popup')) {
            $this->buttonList->remove('back');
            if ($this->getRequest()->getParam('product_tab') != 'variations') {
                $this->addButton(
                    'save_in_new_set',
                    [
                        'label' => __('Save in New Attribute Set'),
                        'class' => 'save',
                        'onclick' => 'saveAttributeInNewSet(\'' . __('Enter Name for New Attribute Set') . '\')'
                    ],
                    100
                );
            }
            $this->buttonList->update('reset', 'level', 10);
            $this->buttonList->update('save', 'class', 'save action-secondary');
        } else {
            $this->addButton(
                'save_and_edit_button',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ]
            );
        }

        if ((!$entityAttribute || !$entityAttribute->getIsUserDefined()) || str_contains($entityAttribute->getAttributeCode(), 'gpc_')) {
            $this->buttonList->remove('delete');
        } else {
            $this->buttonList->update('delete', 'label', __('Delete Attribute'));
        }

        if (str_contains($entityAttribute->getAttributeCode(), 'gpc_')) {
            $this->buttonList->remove('save');
            $this->buttonList->remove('save_and_edit_button');
            $this->buttonList->remove('reset');
        }
    }
}
