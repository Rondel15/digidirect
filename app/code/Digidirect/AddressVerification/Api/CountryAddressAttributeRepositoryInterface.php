<?php
namespace Digidirect\AddressVerification\Api;

/**
 * Interface CountryAddressAttributeRepositoryInterface
 * @package Digidirect\AddressVerification\Api
 */
interface CountryAddressAttributeRepositoryInterface
{
    /**
     * @param int $id
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getById($id);

    /**
     * @param int $id
     * @return $this
     */
    public function deleteById($id);

    /**
     * @param \Digidirect\AddressVerification\Model\CountryAddressAttribute $item
     * @return \Digidirect\AddressVerification\Model\CountryAddressAttribute
     */
    public function save(\Digidirect\AddressVerification\Model\CountryAddressAttribute $item);

    /**
     * @param string $code
     * @return \Digidirect\AddressVerification\Model\CountryAddressAttribute
     */
    public function getByCountryCode($code);

    /**
     * @param string $countryCode
     * @param bool $decode
     * @return array
     */
    public function getAddressAttributesByCountryCode($countryCode, $decode = true);

    /**
     * @return \Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute\Collection
     */
    public function getAll();
}
