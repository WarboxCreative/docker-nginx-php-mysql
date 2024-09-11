<?php
namespace Warbox\WidgetCollection\Block;

class Specialoffers extends \Warbox\WidgetElements\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Warbox_WidgetCollection::widget/special-offers.phtml';

    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}
