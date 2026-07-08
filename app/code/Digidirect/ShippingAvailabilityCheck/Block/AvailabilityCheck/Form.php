<?php

namespace Digidirect\ShippingAvailabilityCheck\Block\AvailabilityCheck;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Form
 * @package Digidirect\ShippingAvailabilityCheck\Block\AvailabilityCheck
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Customer\Api\Data\AddressInterface|null
     */
    protected $_address = null;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Digidirect\ShippingAvailabilityCheck\Helper\Data
     */
    protected $dataHelper;

    /**
     * Form constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = [],
        \Digidirect\ShippingAvailabilityCheck\Helper\Data $dataHelper = null
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
        $this->_customerSession = $customerSessionFactory;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->registry = $registry;
        $this->localeFormat = $localeFormat;
        $this->dataHelper = $dataHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Digidirect\ShippingAvailabilityCheck\Helper\Data::class);
    }

    /**
     * Return the associated address.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // Init address object
        if ($customer = $this->getCustomer()) {
            $defaultShippingAddress = $customer->getDefaultShippingAddress();
            if ($defaultShippingAddress) {
                $this->_address = $defaultShippingAddress;
            }
        }

        return $this;
    }

    /**
     * Return the country Id.
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($this->getAddress() && $countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }

        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address
     * @return string region name
     */
    public function getRegion()
    {
        if ($this->getAddress() && $region = $this->getAddress()->getRegion()) {
            return $region;
        }

        return null;
    }

    /**
     * Return the id of the region
     * @return int region id
     */
    public function getRegionId()
    {
        if ($this->getAddress() && $regionId = $this->getAddress()->getRegionId()) {
            return $regionId;
        }
        return null;
    }

    /**
     * Return the postcode
     * @return string $postcode
     */
    public function getPostcode()
    {
        if ($this->getAddress() && $postcode = $this->getAddress()->getPostcode()) {
            return $postcode;
        }
        return null;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->_customerSession->create()->getCustomer();
    }

    /**
     * @return mixed|null
     */
    public function getCustomerId()
    {
        try {
            $customer = $this->getCustomer();
        } catch (NoSuchEntityException $e) {
            $customer = null;
        }
        if ($customer) {
            return $customer->getId();
        }
        return null;
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        $product = $this->getCurrentProduct();
        return $product ? $product->getId() : null;
    }

    /**
     * @return string
     */
    public function getProductType()
    {
        $product = $this->getCurrentProduct();
        return $product ? $product->getTypeId() : '';
    }

    /**
     * Get store code
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @return array
     */
    public function getPriceFormat()
    {
        /**
         * @var $store StoreManagerInterface
         */
        $store = $this->_storeManager->getStore();
        return $this->localeFormat->getPriceFormat(null, $store->getCurrentCurrencyCode());
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $product = $this->getCurrentProduct();
        if (($product && !$this->dataHelper->isDisplayForOutOfStockEnabled() && !$product->isSalable())
            || $product->isVirtual()
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
