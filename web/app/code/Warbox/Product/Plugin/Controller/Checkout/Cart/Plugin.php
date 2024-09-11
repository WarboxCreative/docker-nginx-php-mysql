<?php declare(strict_types=1);
namespace Warbox\Product\Plugin\Controller\Checkout\Cart;

use Magento\Checkout\Controller\Cart\Add;
use Warbox\Product\Model\Configurable;

/**
 * Class Plugin
 * @package Warbox\Product\Plugin\Controller\Checkout\Cart
 */
class Plugin
{
    private Configurable $configurable;

    /**
     * @param Configurable        $configurable
     */
    public function __construct(
        Configurable $configurable
    ){
        $this->configurable = $configurable;
    }

    /**
     * @param Add      $add
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundExecute(Add $add, callable $proceed): mixed
    {
        $parentId = (int)$add->getRequest()->getParam('product');
        if ($parentId) {
            $optionIds = $add->getRequest()->getParam('workbench_options');
            if($optionIds) {
                $options = explode(',', $optionIds);
                $this->configurable->addComplexProductToQuote($parentId, $options);
            }
        }

        return $proceed();
    }
}
