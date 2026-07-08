<?php
namespace Digidirect\ExtendedShippingRates\Model\Config\Source;

use Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate as Rate;

class MultipleRatesPrice
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Rate::MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRIORITY,
                'label' => __('Use Rate with Max Priority')
            ],
            [
                'value' => Rate::MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRICE,
                'label' => __('Use Rate with Max Price')
            ],
            [
                'value' => Rate::MULTIPLE_RATES_PRICE_CALCULATION_MIN_PRICE,
                'label' => __('Use Rate with Min Price')
            ],
        ];
    }
}
