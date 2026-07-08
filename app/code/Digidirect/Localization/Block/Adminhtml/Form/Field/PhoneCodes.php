<?php

namespace Digidirect\Localization\Block\Adminhtml\Form\Field;

use Digidirect\Localization\Model\Configuration;

class PhoneCodes extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var Country
     */
    protected $_countryRenderer;

    /**
     * Retrieve country column select renderer
     *
     * @return Country
     */
    protected function _getCountryRenderer()
    {
        if (!$this->_countryRenderer) {
            $this->_countryRenderer = $this->getLayout()->createBlock(
                \Digidirect\Localization\Block\Adminhtml\Form\Field\Country::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_countryRenderer->setClass('validate-select');
        }
        return $this->_countryRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'country',
            ['label' => __('Country'), 'renderer' => $this->_getCountryRenderer()]
        );
        $this->addColumn(Configuration::MASK_PREFIX, ['label' => __('Mask Prefix')]);
        $this->addColumn(Configuration::MASK_PATTERN, ['label' => __('Mask Pattern')]);
        $this->addColumn(Configuration::MASK_PLACEHOLDER, ['label' => __('Mask Placeholder')]);
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
        $optionExtraAttr['option_' . $this->_getCountryRenderer()->calcOptionHash($row->getData('country'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
