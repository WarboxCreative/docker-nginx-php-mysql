<?php declare(strict_types=1);

namespace Warbox\Import\Model\ResourceModel\Type;

use Wyomind\MassStockUpdate\Model\ResourceModel\Type\AbstractResource;
use Zend_Db_Expr;

/**
 * Class ConfigurableProduct
 *
 * @package Warbox\Import\Model\ResourceModel\Type
 */
class ConfigurableProduct extends \Wyomind\MassProductImport\Model\ResourceModel\Type\ConfigurableProduct
{
    public $fields;

    /**
     *
     */
    public const ITEM_SEPARATOR = ',';
    /**
     * @var array
     */
    public $_configurableAttributes = [];
    /**
     * @var array
     */
    public $_configurableAttributeLabels = [];

    public $tableCpe;
    public $tableCpr;
    public $tableCpsl;
    public $tableCpsa;
    public $tableCpsal;

    /**
     * {@inheritdoc}
     */
    public function collect($productId, $value, $strategy, $profile) : void
    {
        [ $field ] = $strategy[ 'option' ];
        switch ($field) {
            case 'parentSku':
                if ($value != '') {
                    if ($this->framework->moduleIsEnabled('Magento_Enterprise')) {
                        $data = [
                            'parent_id' => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1))',
                            'child_id' => $productId,
                        ];
                    } else {
                        $data = [
                            'parent_id' => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1)',
                            'child_id' => $productId,
                        ];
                    }
                    $this->queries[ $this->queryIndexer ][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data, true);

                    if ($this->framework->moduleIsEnabled('Magento_Enterprise')) {
                        $data = [
                            'product_id' => $productId,
                            'parent_id' => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1))',
                        ];
                    } else {
                        $data = [
                            'product_id' => $productId,
                            'parent_id' => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1)',
                        ];
                    }
                    $this->queries[ $this->queryIndexer ][] = $this->createInsertOnDuplicateUpdate($this->tableCpsl, $data, true);

                    $this->fields[ 'sku' ] = $value;
                }
                return;
            case 'childrenSkus':
                $values = explode(self::ITEM_SEPARATOR, $value);
                foreach ($values as $value) {
                    if ($value != '') {
                        if ($this->framework->moduleIsEnabled('Magento_Enterprise')) {
                            $data = [
                                'parent_id' => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=$productId)",
                                'child_id' => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1)',
                            ];
                        } else {
                            $data = [
                                'parent_id' => $productId,
                                'child_id' => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1)',
                            ];
                        }
                        $this->queries[ $this->queryIndexer ][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data, true);

                        if ($this->framework->moduleIsEnabled('Magento_Enterprise')) {
                            $data = [
                                'product_id' => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1)',
                                'parent_id' => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=$productId)",
                            ];
                        } else {
                            $data = [
                                'product_id' => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . ' LIMIT 1)',
                                'parent_id' => $productId,
                            ];
                        }
                        $this->queries[ $this->queryIndexer ][] = $this->createInsertOnDuplicateUpdate($this->tableCpsl, $data, true);
                    }
                }
                return;
            case 'attributes':
                if ($this->framework->moduleIsEnabled('Magento_Enterprise')) {
                    $productId = "(SELECT MAX(row_id) from $this->tableCpe where entity_id=$productId)";
                }

                $values = explode(self::ITEM_SEPARATOR, $value);

                if($values && count($values) && $values[0] !== '') {
                    $this->queries[ $this->queryIndexer ][] = 'DELETE FROM ' . $this->tableCpsa . " WHERE product_id=$productId;";

                    foreach ( $values as $position => $value ) {
                        $origValue = $value;
                        $value = $this->helperData->getValue($value);

                        if ( isset($this->_configurableAttributes[ $value ]) || in_array($value, $this->_configurableAttributes) ) {
                            if ( in_array($value, $this->_configurableAttributes) ) {
                                $value = array_search($value, $this->_configurableAttributes);
                            }

                            $label = $this->_configurableAttributeLabels[ $value ];
                            $fields = [
                                'product_id' => new Zend_Db_Expr($productId),
                                'attribute_id' => '' . $value . '',
                                'position' => $position
                            ];
                            $data = $this->helperData->prepareFields($fields, $origValue, 'position');

                            $this->queries[ $this->queryIndexer ][] = $this->createInsertOnDuplicateUpdate($this->tableCpsa, $data);
                            $this->queries[ $this->queryIndexer ][] = 'SELECT @product_super_attribute_id:= product_super_attribute_id FROM ' . $this->tableCpsa . " WHERE product_id=$productId AND attribute_id='" . $value . "';";

                            $data = [
                                'product_super_attribute_id' => '@product_super_attribute_id',
                                'value' => $this->helperData->sanitizeField($label),
                            ];
                            $this->queries[ $this->queryIndexer ][] = $this->createInsertOnDuplicateUpdate($this->tableCpsal, $data);
                        }
                    }
                }
                return;
        }

        AbstractResource::collect($productId, $value, $strategy, $profile);
    }
}
