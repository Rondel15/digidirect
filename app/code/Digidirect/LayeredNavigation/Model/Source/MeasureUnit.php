<?php
namespace Digidirect\LayeredNavigation\Model\Source;

class MeasureUnit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * CUSTOM
     */
    const CUSTOM = 0;

    /**
     * CURRENCY_SYMBOL
     */
    const CURRENCY_SYMBOL = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CURRENCY_SYMBOL,
                'label' => __('Store Currency')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom label')
            ]
        ];
    }
}
