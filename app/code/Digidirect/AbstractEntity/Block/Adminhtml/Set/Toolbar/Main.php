<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml\Set\Toolbar;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Widget\Button;

class Main extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main
{
    const ADD_ACTION = 'digidirect_abstractentity/*/add';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var AbstractBlock $toolbar */
        $toolbar = $this->getToolbar();
        $toolbar->unsetChild('addButton');
        $toolbar->addChild(
            'add_button',
            Button::class,
            [
                'label' => __('Add New Entity'),
                'onclick' => 'setLocation(\'' . $this->getUrl(static::ADD_ACTION) . '\')',
                'class' => 'add primary add-set'
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getHeader()
    {
        return __('Abstract Entities');
    }
}
