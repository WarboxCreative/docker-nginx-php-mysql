<?php declare(strict_types=1);

namespace Warbox\Category\Plugin\Eav\Model\ResourceModel\Entity\Attribute\Option;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as MagentoCollection;

/**
 * Class Collection
 * @package Warbox\Category\Plugin\Eav\Model\ResourceModel\Entity\Attribute\Option
 */
class Collection
{
    public const SORT_ORDER_ASC = 'ASC';

    /**
     * @param MagentoCollection $subject
     * @param string            $dir
     * @param bool              $sortAlpha
     *
     * @return array
     */
    public function beforeSetPositionOrder(MagentoCollection $subject, string $dir = self::SORT_ORDER_ASC, bool $sortAlpha = false) : array
    {
        return [self::SORT_ORDER_ASC, true];
    }
}
