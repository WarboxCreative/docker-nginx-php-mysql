<?php declare(strict_types=1);

namespace Warbox\Category\Model\Category;

/**
 * Class DataProvider
 * @package Warbox\Category\Model\Category
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * List of fields groups and fields.
     *
     * @return array
     * @since 101.0.0
     */
    protected function getFieldsMap(): array
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'landing_image';
        $fields['content'][] = 'hero_image';
        $fields['content'][] = 'list_image';
        $fields['content'][] = 'icon_image';

        return $fields;
    }
}
