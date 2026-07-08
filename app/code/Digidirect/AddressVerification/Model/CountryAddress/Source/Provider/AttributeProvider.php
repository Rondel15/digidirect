<?php
namespace Digidirect\AddressVerification\Model\CountryAddress\Source\Provider;

use Digidirect\AddressVerification\Model\CountryAddress\Source\SourceProviderInterface;

/**
 * Class AttributeProvider
 * @package Digidirect\AddressVerification\Model\CountryAddress\Source\Provider
 */
class AttributeProvider implements SourceProviderInterface
{
    const ATTRIBUTE_POSTCODE = 'postcode';
    const ATTRIBUTE_REGION = 'region';
    const ATTRIBUTE_SUBURB = 'suburb';

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            ['value' => self::ATTRIBUTE_POSTCODE, 'label' => __('Postcode')],
            ['value' => self::ATTRIBUTE_REGION, 'label' => __('Region (State)')],
            ['value' => self::ATTRIBUTE_SUBURB, 'label' => __('City')],
        ];
    }
}
