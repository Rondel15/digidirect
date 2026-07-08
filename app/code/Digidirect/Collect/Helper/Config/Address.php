<?php

namespace Digidirect\Collect\Helper\Config;

use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Address
 *
 * @package Digidirect\Collect\Helper\Config
 */
class Address extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_DEFAULT_SHIPPING_FIRSTNAME = 'carriers/collect/default_shipping_first_name';
    const XML_DEFAULT_SHIPPING_LASTNAME = 'carriers/collect/default_shipping_last_name';
    const XML_DEFAULT_SHIPPING_CITY = 'carriers/collect/default_shipping_city';
    const XML_DEFAULT_SHIPPING_TELEPHONE = 'carriers/collect/default_shipping_telephone';
    const XML_DEFAULT_SHIPPING_POSTCODE = 'carriers/collect/default_shipping_postcode';
    const XML_DEFAULT_SHIPPING_STREET = 'carriers/collect/default_shipping_street';
    const XML_DEFAULT_SHIPPING_COUNTRY = 'carriers/collect/country_id';
    const XML_DEFAULT_SHIPPING_REGION = 'carriers/collect/region_id';
    const XML_DEFAULT_ADDRESS_TEMPLATE = 'carriers/collect/default_address_template';

    const TEMPLATE_CODE = 'collect';

    /**
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $collectHelper;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var string
     */
    protected $defaultAddressHtml;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface
     */
    protected $defaultAddress;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionInterfaceFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionInterfaceFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Magento\Customer\Helper\Address $addressHelper,
        AddressMapper $addressMapper,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionInterfaceFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        parent::__construct($context);

        $this->collectHelper = $collectHelper;
        $this->addressHelper = $addressHelper;
        $this->addressMapper = $addressMapper;
        $this->addressFactory = $addressFactory;
        $this->regionInterfaceFactory = $regionInterfaceFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * GetDefaultShippingFirstname
     *
     * @return string
     */
    public function getDefaultShippingFirstname()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_FIRSTNAME);
    }

    /**
     * GetDefaultShippingLastname
     *
     * @return string
     */
    public function getDefaultShippingLastname()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_LASTNAME);
    }

    /**
     * GetDefaultShippingCity
     *
     * @return string
     */
    public function getDefaultShippingCity()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_CITY);
    }

    /**
     * GetDefaultShippingCountry
     *
     * @return string
     */
    public function getDefaultShippingCountry()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_COUNTRY);
    }

    /**
     * GetDefaultShippingRegion
     *
     * @return string
     */
    public function getDefaultShippingRegion()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_REGION);
    }

    /**
     * GetDefaultShippingPostcode
     *
     * @return string
     */
    public function getDefaultShippingPostcode()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_POSTCODE);
    }

    /**
     * GetDefaultShippingStreet
     *
     * @return string
     */
    public function getDefaultShippingStreet()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_STREET);
    }

    /**
     * GetDefaultShippingTelephone
     *
     * @return string
     */
    public function getDefaultShippingTelephone()
    {
        return $this->getDefaultShippingValue(self::XML_DEFAULT_SHIPPING_TELEPHONE);
    }

    /**
     * Get default address using template
     *
     * @return string
     */
    public function getDefaultAddressHtml()
    {
        if (null === $this->defaultAddressHtml) {
            $addressTemplate = $this->getDefaultShippingValue(self::XML_DEFAULT_ADDRESS_TEMPLATE);
            if(empty($addressTemplate))
            {
                $this->defaultAddressHtml = ''; 
            } else {
                $collectAddressType = new \Magento\Framework\DataObject();
                $collectAddressType->setCode('html')
                    ->setDefaultFormat($addressTemplate);

                /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
                $renderer = $this->addressHelper
                    ->getRenderer(\Magento\Customer\Model\Address\Config::DEFAULT_ADDRESS_RENDERER)
                    ->setType($collectAddressType)
                    ->setEscapeHtml(false);

                $this->defaultAddressHtml = $renderer->renderArray(
                    $this->getDefaultAddressArray()
                );
            }
        }
        return $this->defaultAddressHtml;
    }

    /**
     * Get default address using template
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getDefaultAddress()
    {
        if (null === $this->defaultAddress) {
            $this->defaultAddress = $this->addressFactory->create();
            $this->defaultAddress->setFirstname($this->getDefaultShippingFirstname());
            $this->defaultAddress->setLastname($this->getDefaultShippingLastname());
            $this->defaultAddress->setCity($this->getDefaultShippingCity());
            $this->defaultAddress->setTelephone($this->getDefaultShippingTelephone());
            $this->defaultAddress->setPostcode($this->getDefaultShippingPostcode());
            $this->defaultAddress->setStreet([(string)$this->getDefaultShippingStreet()]);
            $this->defaultAddress->setCountryId($this->getDefaultShippingCountry());
            if ($region = $this->getDefaultShippingRegion()) {
                $regionObject = $this->regionInterfaceFactory->create();
                if (is_numeric($region)) {
                    $region = (int)$region;
                    $this->defaultAddress->setRegionId($region);
                    $regionObject->setRegionId($region);
                    $region = $this->regionFactory->create()->load($region);
                    $region = $region->getCode();
                }
                $regionObject->setRegion($region);
                $this->defaultAddress->setRegion($regionObject);
            }
        }
        return $this->defaultAddress;
    }

    /**
     * Get default address array
     *
     * @return string[]
     */
    public function getDefaultAddressArray()
    {
        return $this->addressMapper->toFlatArray($this->getDefaultAddress());
    }

    /**
     * Get C&C main helper
     *
     * @return \Digidirect\Collect\Helper\Data
     */
    public function getCollectHelper()
    {
        return $this->collectHelper;
    }

    /**
     * Apply dummy address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function applyDummyAddress(\Magento\Quote\Model\Quote\Address $address)
    {
        if(!empty($address)){
            $qouteAddress = (array) $address;
            $address->addData($qouteAddress);
        }else{
            $address->addData($this->getDefaultAddressArray());
        }

        return $address;
    }

    /**
     * GetDefaultShippingValue
     *
     * @param string $value
     * @return string
     */
    protected function getDefaultShippingValue($value)
    {
        return $this->scopeConfig->getValue(
            $value,
            ScopeInterface::SCOPE_STORE
        );
    }
}
