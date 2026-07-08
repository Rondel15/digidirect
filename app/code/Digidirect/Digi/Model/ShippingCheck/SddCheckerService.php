<?php

namespace Digidirect\Digi\Model\ShippingCheck;

use Digidirect\MyStoreWidget\Helper\Data as WidgetStoreHelper;
use Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterfaceFactory;
use Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface;
use Digidirect\ShippingAvailabilityCheck\Model\CheckManagement;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\EstimateAddressInterfaceFactory;
use Magento\Catalog\Model\SessionFactory as CatalogSessionFactory;

class SddCheckerService
{
    const SDD_PREFIX = 'sdd';

    /**
     * @var array
     */
    private $cachedResult = [];

    /**
     * @var WidgetStoreHelper
     */
    private $widgetStoreHelper;

    /**
     * @var CheckManagement
     */
    private $shipCheckManager;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var EstimateAddressInterfaceFactory
     */
    private $estimateAddressFactory;

    /**
     * @var ProductDataInterfaceFactory
     */
    private $productDataFactory;
    /**
     * @var CatalogSessionFactory
     */
    private $catalogSessionFactory;

    /**
     * SddCheckerService constructor.
     * @param WidgetStoreHelper $widgetStoreHelper
     * @param CheckManagement $shipCheckManager
     * @param SessionFactory $customerSessionFactory
     * @param EstimateAddressInterfaceFactory $estimateAddressFactory
     * @param ProductDataInterfaceFactory $productDataFactory
     * @param CatalogSessionFactory $catalogSessionFactory
     */
    public function __construct(
        WidgetStoreHelper $widgetStoreHelper,
        CheckManagement $shipCheckManager,
        SessionFactory $customerSessionFactory,
        EstimateAddressInterfaceFactory $estimateAddressFactory,
        ProductDataInterfaceFactory $productDataFactory,
        CatalogSessionFactory $catalogSessionFactory

    ) {
        $this->widgetStoreHelper = $widgetStoreHelper;
        $this->shipCheckManager = $shipCheckManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->estimateAddressFactory = $estimateAddressFactory;
        $this->productDataFactory = $productDataFactory;
        $this->catalogSessionFactory = $catalogSessionFactory;
    }

    /**
     * @param ProductDataInterface|null $productData
     * @return array|null
     */
    public function getCheckResult($productData = null)
    {
        $productData = $productData ?? $this->getProductData();
        if ($productData !== null && empty($this->cachedResult[$productData->getProductId()])) {
            $address = $this->getAddressData();
            $customerId = $this->getCustomerId();
            if ($address !== null && $productData !== null) {
                try {
                    $result = $this->shipCheckManager->getShippingMethodList($address, $productData, $customerId);
                } catch (LocalizedException $e) {
                    $result = [];
                }
                $machResult = array_reduce($result, static function ($acc, $ship) {
                    $data = $ship->getData();
                    if (isset($data['method_code'])
                        && !empty($data['method_code'])
                        && is_string($data['method_code'])
                    ) {
                        $methodCode = strtolower(trim($data['method_code']));
                        $acc[$data['method_code']] = mb_strpos($methodCode, self::SDD_PREFIX) === 0;
                    }
                    return $acc;
                }, []);
                $machResult = array_filter($machResult);
                $this->cachedResult[$productData->getProductId()] = count($machResult) > 0
                    ? [$productData->getProductId() => true]
                    : [$productData->getProductId() => false];
                return $this->cachedResult[$productData->getProductId()];
            }
            return null;
        }
        return $productData !== null ? $this->cachedResult[$productData->getProductId()] : null;
    }

    /**
     * @return \Magento\Quote\Api\Data\EstimateAddressInterface|null
     */
    public function getAddressData()
    {
        $addressData = null;
        $currentWidgetStore = $this->getCurrentStore();
        /**
         * @var $addressData \Magento\Quote\Api\Data\EstimateAddressInterface
         */
        if (!empty($currentWidgetStore)) {
            $addressData = $this->estimateAddressFactory->create();
            $addressData->setCountryId($currentWidgetStore['country']);
            $addressData->setPostcode($currentWidgetStore['postcode']);
        }
        return $addressData;
    }

    /**
     * @return \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface|null
     */
    public function getProductData()
    {
        $productData = null;
        $catalogSession = $this->catalogSessionFactory->create();
        $currentProductId = $catalogSession->getLastViewedProductId();
        /**
         * @var $productData \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface
         */
        if (!empty($currentProductId)) {
            $productData = $this->createProductData($currentProductId);
        }
        return $productData;
    }

    /**
     * @param string | int $productId
     * @param string| int $qty
     * @return ProductDataInterface
     */
    public function createProductData($productId, $qty = 1)
    {
        $productData = $this->productDataFactory->create();
        $productData->setProductId($productId);
        $productData->setParams(['qty' => $qty]);
        return $productData;
    }
    /**
     * @return array|null
     */
    public function getCurrentStore()
    {
        $currentStore = $this->widgetStoreHelper->getCurrentStore();
        if ($currentStore) {
            return $currentStore->getData();
        }
        return null;
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
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSessionFactory->create()->getCustomer();
    }
}
