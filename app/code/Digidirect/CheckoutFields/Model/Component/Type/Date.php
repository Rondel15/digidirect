<?php

namespace Digidirect\CheckoutFields\Model\Component\Type;

use Digidirect\CheckoutFields\Helper\Data as Data;

/**
 * Class Date
 * @package Digidirect\CheckoutFields\Model\Component\Type
 */
class Date extends AbstractType
{
    /**
     * @var \Digidirect\CheckoutFields\Helper\Data
     */
    protected $_helperData;

    /**
     * Date constructor.
     * @param \Digidirect\CheckoutFields\Model\Config\Source\Options $sourceOptions
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Digidirect\CheckoutFields\Model\Config\Source\Options $sourceOptions,
        Data $helper,
        array $data = []
    ) {
        $this->_helperData = $helper;
        parent::__construct($sourceOptions, $data);
    }

    /**
     * @return string
     */
    protected function getComponent()
    {
        return 'Magento_Ui/js/form/element/date';
    }

    /**
     * @return string
     */
    protected function getElementTemplate()
    {
        return 'ui/form/element/date';
    }

    /**
     * @param array $data
     * @param string $key
     * @return array
     */
    public function prepareFieldConfig($data, $key)
    {
        $config = parent::prepareFieldConfig($data, $key);
        $config['date_format'] = $this->_helperData->getDateFormat();
        return $config;
    }
}
