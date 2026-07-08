<?php
namespace Digidirect\AbstractAttributes\Block\Adminhtml\Catalog\Product\Attribute\Edit;

use Magento\Backend\Block\Template\Context;

/**
 * Class Js
 * @package Digidirect\AbstractAttributes\Block\Adminhtml\Catalog\Product\Attribute\Edit
 */
class Js extends \Magento\Backend\Block\Template
{
    /**
     * @var AdvancedOptionsProperties
     */
    protected $_advancedOptionsPropertiesTab;

    /**
     * Js constructor.
     * @param Context $context
     * @param Tab\AdvancedOptionsProperties $advancedOptionsPropertiesTab
     * @param array $data
     */
    public function __construct(
        Context $context,
        Tab\AdvancedOptionsProperties $advancedOptionsPropertiesTab,
        array $data = []
    ) {
        $this->_advancedOptionsPropertiesTab = $advancedOptionsPropertiesTab;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if (!$this->_advancedOptionsPropertiesTab->canShowTab()) {
            return '';
        }

        return parent::toHtml();
    }
}
