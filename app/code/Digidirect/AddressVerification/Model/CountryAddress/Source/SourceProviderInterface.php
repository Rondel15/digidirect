<?php
namespace Digidirect\AddressVerification\Model\CountryAddress\Source;

/**
 * Interface SourceProviderInterface
 * @package Digidirect\AddressVerification\Model\CountryAddress
 */
interface SourceProviderInterface
{
    /**
     * @return array
     */
    public function getOptions();
}
