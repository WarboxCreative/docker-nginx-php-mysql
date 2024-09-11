<?php
declare(strict_types=1);

namespace Warbox\Catalogue\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Warbox\Catalogue\Helper
 */
class Data extends AbstractHelper
{
    public const XML_PATH = 'catalogue/';

    /**
     * @param $field
     * @param $storeId
     *
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     *
     * @return string
     */
    public function getCatalogueImg($storeId = null): string
    {
        return 'media/catalogue/' . $this->getConfigValue(self::XML_PATH .'general/image', $storeId);
    }

    /**
     * @param $storeId
     *
     * @return string|null
     */
    public function getCatalogueTitle($storeId = null): ?string
    {
        return $this->getConfigValue(self::XML_PATH .'general/title', $storeId);
    }
}
