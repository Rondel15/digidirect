<?php
namespace Digidirect\MyStoreWidget\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ShippingAddressInfo extends AbstractFieldArray
{
    /**
     * @var EntityRenderer
     */
    protected $_entityRenderer;

    /**
     * @var AttributeRenderer
     */
    protected $_attributeRenderer;

    /**
     * Retrieve attribute renderer
     *
     * @return AttributeRenderer
     */
    protected function _getAddressAttributeRenderer()
    {
        if (!$this->_entityRenderer) {
            $this->_entityRenderer = $this->getLayout()->createBlock(
                'Digidirect\MyStoreWidget\Block\Adminhtml\Config\Form\Field\AddressAttributeRenderer',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_entityRenderer->setClass('entity_select');
        }
        return $this->_entityRenderer;
    }

    /**
     * Retrieve attribute renderer
     *
     * @return AttributeRenderer
     */
    protected function _getAttributeRenderer()
    {
        if (!$this->_attributeRenderer) {
            $this->_attributeRenderer = $this->getLayout()->createBlock(
                'Digidirect\MyStoreWidget\Block\Adminhtml\Config\Form\Field\AttributeRenderer',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_attributeRenderer->setClass('attribute_select');
        }
        return $this->_attributeRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'shipping_field',
            ['label' => __('Shipping Address Field'), 'renderer' => $this->_getAddressAttributeRenderer()]
        );
        $this->addColumn(
            'entity_attribute',
            ['label' => __('Abstract Entity Attribute'), 'renderer' => $this->_getAttributeRenderer()]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getAddressAttributeRenderer()->calcOptionHash(
            $row->getData('shipping_field')
        )] = 'selected="selected"';

        $optionExtraAttr['option_' . $this->_getAttributeRenderer()->calcOptionHash(
            $row->getData('entity_attribute')
        )] = 'selected="selected"';

        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
