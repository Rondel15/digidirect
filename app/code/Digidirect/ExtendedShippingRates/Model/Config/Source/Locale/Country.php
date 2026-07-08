<?php
namespace Digidirect\ExtendedShippingRates\Model\Config\Source\Locale;

class Country extends \Magento\Config\Model\Config\Source\Locale\Country
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => __('Select Country'), 'value' => ''];
        $origCountryOptions = parent::toOptionArray();
        $options = array_merge($options, $origCountryOptions);

        return $options;
    }
}
