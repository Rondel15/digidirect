<?php
namespace Digidirect\AbstractAttributes\Observer\Adminhtml;

use Digidirect\AbstractAttributes\Block\Adminhtml\Catalog\Product\Attribute\Edit\Tab\AdvancedOptionsProperties;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ProductAttributeFormBuildMainTab
 * @package Digidirect\AbstractAttributes\Observer\Adminhtml
 */
class ProductAttributeFormBuildMainTab implements ObserverInterface
{
    /**
     * @var Enabledisable
     */
    protected $_enableDisable;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attribute;

    /**
     * @var AdvancedOptionsProperties
     */
    protected $_advancedOptionsPropertiesTab;

    /**
     * ProductAttributeFormBuildMainTab constructor.
     * @param Enabledisable $enableDisable
     * @param AdvancedOptionsProperties $advancedOptionsPropertiesTab
     */
    public function __construct(
        Enabledisable $enableDisable,
        AdvancedOptionsProperties $advancedOptionsPropertiesTab
    ) {
        $this->_enableDisable = $enableDisable;
        $this->_advancedOptionsPropertiesTab = $advancedOptionsPropertiesTab;
    }

    /**
     * Execute
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->_advancedOptionsPropertiesTab->canShowTab()) {
            return $this;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getForm();

        $fieldset = $form->getElement('base_fieldset');

        $fieldset->addField(
            'aa_status',
            'select',
            [
                'name'   => 'aa_status',
                'label'  => __('Advanced Options Management'),
                'title'  => __('Advanced Options Management'),
                'values' => $this->_enableDisable->toOptionArray(),
                'class'  => 'abstract-attributes'
            ]
        );

        return $this;
    }
}
