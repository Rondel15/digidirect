<?php
namespace Digidirect\AddressVerification\Model\Config\Source;

/**
 * Class Type
 * @package Digidirect\AddressVerification\Model\Config\Source
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    const DISABLED = 0;
    const AU_POST = 1;
    const GOOGLE = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DISABLED, 'label' => __('Disable')],
            ['value' => self::AU_POST, 'label' => __('Enable Suburb&Postcode Autocomplete')],
            ['value' => self::GOOGLE, 'label' => __('Enable Google API Autocomplete')],
        ];
    }
}
