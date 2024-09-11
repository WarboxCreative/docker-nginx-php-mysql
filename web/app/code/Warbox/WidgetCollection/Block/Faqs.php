<?php
namespace Warbox\WidgetCollection\Block;

class Faqs extends \Warbox\WidgetElements\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Warbox_WidgetCollection::widget/faqs.phtml';

    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}
