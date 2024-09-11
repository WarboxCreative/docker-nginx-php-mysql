<?php
namespace Warbox\WidgetCollection\Block;

class Bestsellers extends \Warbox\WidgetElements\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Warbox_WidgetCollection::widget/bestsellers.phtml';

    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}
