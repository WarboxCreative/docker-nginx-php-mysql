<?php declare(strict_types=1);

namespace Warbox\Category\Plugin\Catalog\Controller\Adminhtml\Category;

use Magento\Catalog\Controller\Adminhtml\Category\Save;

/**
 * Class SavePlugin
 * @package Warbox\Category\Plugin\Catalog\Controller\Adminhtml\Category
 */
class SavePlugin
{
    /**
     * Add additional images
     *
     * @param Save  $subject
     * @param array $data
     *
     * @return array
     */
    public function beforeImagePreprocessing( Save $subject, array $data): array
    {
        foreach ($this->getAdditionalImages() as $imageType) {
            if (empty($data[$imageType])) {
                unset($data[$imageType]);
                $data[$imageType]['delete'] = true;
            }
        }
        return [$data];
    }
    /**
     * Get additional Images
     *
     * @return array
     */
    protected function getAdditionalImages(): array
    {
        return ['landing_image', 'hero_image', 'list_image', 'icon_image'];
    }
}
