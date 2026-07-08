<?php

namespace Digidirect\AbstractGiftCard\Service\Data\Quote;

use Digidirect\AbstractGiftCard\Service\Data\AddressAdapterInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class AddressAdapter
 */
class AddressAdapter implements AddressAdapterInterface
{
    /**
     * @var AddressInterface
     */
    private $_address;

    /**
     * @param AddressInterface $address
     */
    public function __construct(AddressInterface $address)
    {
        $this->_address = $address;
    }

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->_address->getRegionCode();
    }

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->_address->getCountryId();
    }

    /**
     * Get street line 1
     *
     * @return string
     */
    public function getStreetLine1()
    {
        $street = $this->_address->getStreet();
        return isset($street[0]) ? $street[0]: '';
    }

    /**
     * Get street line 2
     *
     * @return string
     */
    public function getStreetLine2()
    {
        $street = $this->_address->getStreet();
        return isset($street[1]) ? $street[1]: '';
    }

    /**
     * Get telephone number
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->_address->getTelephone();
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->_address->getPostcode();
    }

    /**
     * Get city name
     *
     * @return string
     */
    public function getCity()
    {
        return $this->_address->getCity();
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_address->getFirstname();
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->_address->getLastname();
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->_address->getMiddlename();
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_address->getCustomerId();
    }

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_address->getEmail();
    }

    /**
     * Returns name prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_address->getPrefix();
    }

    /**
     * Returns name suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->_address->getSuffix();
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->_address->getCompany();
    }
}
