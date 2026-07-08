<?php

namespace Digidirect\AddressVerification\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class PostcodeLength extends AbstractFieldArray
{
    const POSTCODE_LENGTH = 'postcode_length';

    /**
     * @var Country
     */
    protected $countryRenderer;

    /**
     * Retrieve country column select renderer
     *
     * @return Country
     */
    protected function getCountryRenderer()
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                \Digidirect\AddressVerification\Block\Adminhtml\System\Config\Country::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->countryRenderer->setClass('validate-select');
        }
        return $this->countryRenderer;
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
            ['label' => __('Country'), 'renderer' => $this->getCountryRenderer()]
        );
        $this->addColumn(self::POSTCODE_LENGTH, ['label' => __('Postcode Length'), 'class' => 'validate-number']);
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
        $optionExtraAttr['option_' . $this->getCountryRenderer()->calcOptionHash($row->getData('country'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
