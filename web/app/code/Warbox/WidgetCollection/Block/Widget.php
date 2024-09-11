<?php
namespace Warbox\WidgetCollection\Block;

class Widget extends \Warbox\WidgetElements\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = "Warbox_WidgetCollection::widget/basic-two-columns.phtml";

    /**
     * @var array
     */
    public $editorParameters = [
        'title',
        'text',
        'content',
        'content_left',
        'content_right',
        'image',
        'image_url',
        'image_tag',
        'image_upload',
        'link_label'
    ];

    /**
     * @inheritDoc
     */
    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}
