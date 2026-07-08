<?php

namespace Digidirect\ShippingAvailabilityCheck\Model;

use Digidirect\ShippingAvailabilityCheck\Api\CheckManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Framework\Registry;

/**
 * Class CheckManagement
 * @package Digidirect\ShippingAvailabilityCheck\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckManagement implements CheckManagementInterface
{

    const KEY_SKU = 'sku';
    const KEY_SHIPPING_METHODS = 'shipping_methods';

    /**
     * @var \Digidirect\ShippingAvailabilityCheck\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Shipping method converter
     *
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    protected $converter;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @var \Digidirect\ShippingAvailabilityCheck\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CheckManagement constructor.
     * @param QuoteManagement $quoteManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Digidirect\ShippingAvailabilityCheck\Helper\Data $helper
     * @param ShippingMethodConverter $converter
     * @param Quote\TotalsCollector $totalsCollector
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Digidirect\ShippingAvailabilityCheck\Helper\Data $helper,
        \Digidirect\ShippingAvailabilityCheck\Model\ShippingMethodConverter $converter,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->converter = $converter;
        $this->totalsCollector = $totalsCollector;
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param EstimateAddressInterface $address
     * @param \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface $productData
     * @param null $customerId
     * @return array|\Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterface[]
     * @throws LocalizedException
     */
    public function getShippingMethodList($address, $productData, $customerId = null)
    {
        /**
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteManagement->getExistedOrCreateNewQuote($productData, $address, $customerId);
        $output = $this->estimateByAddress($quote, $address);

        if ($this->helper->allShippingMethodsToDisplay()) {
            $output = $this->prepareFullShippingMethodList($output, $quote);
        } else {
            $output = $this->excludeNotApplicableShippingMethods($output);
        }

        return $output;
    }

    /**
     * Adding not allowed shipping methods to output
     *
     * @param array $output
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareFullShippingMethodList($output, $quote)
    {
        $carrierKeys = [];
        $allovedCarrierMethodKeys = [];
        foreach ($output as $val) {
            if (!$val->getMethodCode()) {
                $carrierKeys[] = $val->getCarrierCode();
            } else {
                $allovedCarrierMethodKeys[] = $val->getCarrierCode() . '_' . $val->getMethodCode();
            }
        }

        $carriers = $this->shippingConfig->getActiveCarriers();

        $methods = [];
        foreach ($carriers as $carrierCode => $carrierModel) {
            $carrierMethods = $this->getAllowedMethods($carrierModel);
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            foreach ($carrierMethods as $methodCode => $methodInstance) {
                $carrierMethodKey = $carrierCode . '_' . $methodCode;
                $methodTitle = is_object($methodInstance) && !($methodInstance instanceof \Magento\Framework\Phrase)
                    ? $methodInstance->getTitle() : $methodInstance;
                if (in_array($carrierMethodKey, $allovedCarrierMethodKeys) ||
                    in_array($carrierCode, $carrierKeys) ||
                    $methodTitle === false) {
                    continue;
                }

                $data = [
                    'carrier_code' => $carrierCode,
                    'method_code' => $methodCode,
                    'carrier_title' => $carrierTitle,
                    'method_title' => (string)$methodTitle,
                    'not_available' => 1
                ];
                $method = new \Magento\Framework\DataObject();
                $method->setData($data);

                if (is_object($methodInstance) && !($methodInstance instanceof \Magento\Framework\Phrase)) {
                    $methodInstance->setAddress($quote->getShippingAddress());
                    $this->converter->addPriceDataToObject($method, $methodInstance, $quote->getQuoteCurrencyCode());
                }
                $methods[] = $method;
            }
        }

        $result = array_merge($output, $methods);

        return $result;
    }

    /**
     * @param array $output
     * @return mixed
     */
    public function excludeNotApplicableShippingMethods($output)
    {
        if (!empty($output)) {
            foreach ($output as $key => $method) {
                if (!$method->getAvailable()) {
                    unset($output[$key]);
                }
            }
        }
        return $output;
    }

    /**
     * @param \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface[] $productData
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @param int|null $customerId
     * @return array|\Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingMethodListForMultipleProducts($productData, $address, $customerId = null)
    {
        $outputArray = $temp = [];
        foreach ($productData as $product) {
            /**
             * @var \Magento\Quote\Model\Quote $quote
             */
            $quote = $this->quoteManagement->getExistedOrCreateNewQuote($product, $address, $customerId);
            $output = $this->estimateByAddress($quote, $address);
            $temp[self::KEY_SKU] = $product->getSku();
            $temp[self::KEY_SHIPPING_METHODS] = [];
            foreach ($output as $method) {
                $temp[self::KEY_SHIPPING_METHODS][] = $method->getData();
            }
            $outputArray[] = $temp;
        }

        return $outputArray;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param EstimateAddressInterface $address
     * @return array|\Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function estimateByAddress($quote, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        return $this->getShippingMethods($quote, $address);
    }

    /**
     * Get list of available shipping methods
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return \Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterface[]
     */
    private function getShippingMethods(Quote $quote, $address)
    {
        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($this->extractAddressData($address));
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->setLimitCarrier(false);
        $this->totalsCollector->collect($quote);

        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }

    /**
     * Get transform address interface into Array
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return array
     */
    private function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $className = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
        }
        return $this->getDataObjectProcessor()->buildOutputDataArray(
            $address,
            $className
        );
    }

    /**
     * Gets the data object processor
     *
     * @return \Magento\Framework\Reflection\DataObjectProcessor
     */
    private function getDataObjectProcessor()
    {
        if ($this->dataProcessor === null) {
            $this->dataProcessor = ObjectManager::getInstance()
                ->get(DataObjectProcessor::class);
        }
        return $this->dataProcessor;
    }

    /**
     * Get Allowed Methods
     *
     * @param $carrierModel
     * @return mixed
     */
    public function getAllowedMethods($carrierModel)
    {
        return $carrierModel->getAllowedMethods();
    }
}
