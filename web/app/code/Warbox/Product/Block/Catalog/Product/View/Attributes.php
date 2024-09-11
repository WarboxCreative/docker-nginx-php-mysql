<?php declare(strict_types=1);

namespace Warbox\Product\Block\Catalog\Product\View;

use Magento\Framework\Phrase;

/**
 * Class Attributes
 *
 * @package Warbox\Product\Block\Catalog\Product\View
 */
class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{
    private const EXCLUDED_ATTRS = [
        'gpc_child_skus',
        'gpc_image_disclaimer',
        'status',
        'tax_class_id',
        'name',
        'sku',
        'weight',
        'visibility',
        'bss_request_quote',
        'description',
        'short_description',
        'shipment_type',
        'image',
        'small_image',
        'thumbnail',
        'url_key',
        'meta_title',
        'meta_description',
        'msrp_display_actual_price_type',
        'price_view',
        'page_layout',
        'options_container',
        'custom_design',
        'custom_layout',
        'gift_message_available',
        'price',
        'special_price',
        'final_price',
        'msrp',
        'base_price',
        'gpc_product_badges',
    ];

    /**
     * $excludeAttr is optional array of attribute codes to exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     */
    public function getAdditionalData(array $excludeAttr = []) : array
    {
        $excludeAttr = self::EXCLUDED_ATTRS;
        $data = [];
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getAttributeCode(), $excludeAttr) && $attribute->getStoreLabel() != '') {
                $value = $attribute->getFrontend()->getValue($product);

                if ($value instanceof Phrase) {
                    $value = (string) $value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen(trim($value))) {
                    $data[ $attribute->getAttributeCode() ] = [
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }

        uasort($data, function ($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $data;
    }
}
