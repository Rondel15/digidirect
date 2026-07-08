<?php
namespace Digidirect\AbstractAttributes\Block\Adminhtml\Catalog\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;

/**
 * Class ExtendedProperties
 * @package Digidirect\AbstractAttributes\Block\Adminhtml\Catalog\Product\Attribute\Edit\Tab
 */
class AdvancedOptionsProperties extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attribute;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Yesno $yesNo,
        array $data = []
    ) {
        $this->_yesNo = $yesNo;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $id = $this->getAttributeObject()->getId();
        $frontendInput = $this->getAttributeObject()->getData('frontend_input');
        return !$id || ($id && ('multiselect' == $frontendInput
                                || 'select' == $frontendInput
                                || 'swatch_visual' == $frontendInput
                                || 'swatch_text' == $frontendInput));
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Advanced Options Properties');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Advanced Options Properties');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check if attribute is new
     * @return bool
     */
    public function isNew()
    {
        return !$this->getAttributeObject()->getId();
    }

    /**
     * Retrieve attribute object from registry
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function getAttributeObject()
    {
        if (null === $this->_attribute) {
            $this->_attribute = $this->_coreRegistry->registry('entity_attribute');
        }
        return $this->_attribute;
    }
}
