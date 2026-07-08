<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml\Set\Toolbar;

use Digidirect\AbstractEntity\Block\Adminhtml\Set\Main\Formset;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\View\Element\AbstractBlock;

class Add extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Add
{
    const BACK_ACTION = 'digidirect_abstractentity/*/';

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/attribute/set/toolbar/add.phtml';

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        if ($this->getToolbar()) {
            /** @var AbstractBlock $toolbar */
            $toolbar = $this->getToolbar();
            $toolbar->addChild(
                'save_button',
                Button::class,
                [
                    'label' => __('Save'),
                    'class' => 'save primary save-attribute-set',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save', 'target' => '#set-prop-form']],
                    ]
                ]
            );

            $toolbar->addChild(
                'back_button',
                Button::class,
                [
                    'label' => __('Back'),
                    'onclick' => 'setLocation(\'' . $this->getUrl(static::BACK_ACTION) . '\')',
                    'class' => 'back'
                ]
            );
        }

        $this->addChild('setForm', Formset::class);
        return AbstractBlock::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getHeader()
    {
        return __('Add New Entity');
    }
}
