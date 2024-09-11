<?php
namespace Warbox\WidgetCollection\Block;

class Newarrivals extends \Warbox\WidgetElements\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Warbox_WidgetCollection::widget/new-arrivals.phtml';

    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}
