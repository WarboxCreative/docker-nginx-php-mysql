<?php

namespace Warbox\Product\Plugin\Quote;

use Closure;
use Magento\Catalog\Model\ProductFactory;
use Magento\Quote\Model\Quote\Item;

class DefaultItem
{
    protected $_productloader;

    public function __construct(
        ProductFactory $_productloader
    ) {
        $this->_productloader = $_productloader;
    }

    public function aroundGetItemData($subject, Closure $proceed, Item $item)
    {
        $data = $proceed($item);

        $product = $this->_productloader->create()->load($item->getProduct()->getId());

        $atts = [
            'lead_time' => $product->getAttributeText('gpc_lead_time')
        ];

        return array_merge($data, $atts);
    }
}
