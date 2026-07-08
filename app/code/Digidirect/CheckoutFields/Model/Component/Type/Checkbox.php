<?php

namespace Digidirect\CheckoutFields\Model\Component\Type;

/**
 * Class Checkbox
 * @package Digidirect\CheckoutFields\Model\Component\Type
 */
class Checkbox extends AbstractType
{
    /**
     * @var null
     */
    protected $_additionalForm = null;

    /**
     * Checkbox constructor.
     * @param \Digidirect\CheckoutFields\Model\Config\Source\Options $sourceOptions
     * @param \Digidirect\CheckoutFields\Block\Adminhtml\Order\Create\Form\AdditionalFields $additionalForm
     * @param array $data
     */
    public function __construct(
        \Digidirect\CheckoutFields\Model\Config\Source\Options $sourceOptions,
        \Digidirect\CheckoutFields\Block\Adminhtml\Order\Create\Form\AdditionalFields $additionalForm,
        array $data = []
    ) {
        $this->_additionalForm = $additionalForm;
        parent::__construct($sourceOptions, $data);
    }

    /**
     * @return string
     */
    protected function getComponent()
    {
        return 'Magento_Ui/js/form/element/single-checkbox';
    }

    /**
     * @return string
     */
    protected function getElementTemplate()
    {
        return 'ui/form/element/checkbox';
    }

    /**
     * @param array $data
     * @param string $key
     * @return array
     */
    public function prepareFieldConfig($data, $key)
    {
        $config = parent::prepareFieldConfig($data, $key);
        $config['data-form-part'] = $this->_additionalForm->getData('target_form');
        $config['onchange'] = 'this.value = this.checked;';
        return $config;
    }
}
