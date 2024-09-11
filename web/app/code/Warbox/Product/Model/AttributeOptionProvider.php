<?php declare(strict_types=1);
namespace Warbox\Product\Model;

use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Class AttributeOptionProvider
 * @package Warbox\Product\Model
 */
class AttributeOptionProvider extends \Magento\ConfigurableProduct\Model\AttributeOptionProvider
{
    private ScopeResolverInterface $scopeResolver;

    private Attribute $attributeResource;

    private OptionSelectBuilderInterface $optionSelectBuilder;

    /**
     * @param Attribute $attributeResource
     * @param ScopeResolverInterface $scopeResolver,
     * @param OptionSelectBuilderInterface $optionSelectBuilder
     */
    public function __construct(
        Attribute $attributeResource,
        ScopeResolverInterface $scopeResolver,
        OptionSelectBuilderInterface $optionSelectBuilder
    ) {
        parent::__construct($attributeResource, $scopeResolver, $optionSelectBuilder);
        $this->attributeResource = $attributeResource;
        $this->scopeResolver = $scopeResolver;
        $this->optionSelectBuilder = $optionSelectBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptions(AbstractAttribute $superAttribute, $productId) : array
    {
        $scope  = $this->scopeResolver->getScope();
        $select = $this->optionSelectBuilder->getSelect($superAttribute, (int)$productId, $scope);
        $data = $this->attributeResource->getConnection()->fetchAll($select);

        if ($superAttribute->getSourceModel()) {
            $options = $superAttribute->getSource()->getAllOptions(false);

            $optionLabels = [];
            foreach ($options as $option) {
                $optionLabels[$option['value']] = $option['label'];
            }

            foreach ($data as $key => $value) {
                $optionText = $optionLabels[ $value[ 'value_index' ] ] ?? false;
                $data[$key]['default_title'] = $optionText;
                $data[$key]['option_title'] = $optionText;
            }
        }

        usort($data, function ($a, $b) {
            if (is_string($a['option_title']) && is_string($b['option_title'])) {
                return strtolower($a[ 'option_title' ]) <=> strtolower($b[ 'option_title' ]);
            }

            return 0;
        });

        return $data;
    }
}
