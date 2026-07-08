<?php
namespace Digidirect\ExtendedShippingRates\Model\Carrier;

use Digidirect\ExtendedShippingRates\Api\Data\MethodInterface;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku;
use Magento\Catalog\Api\ProductRepositoryInterface;
/**
 * Class Artificial
 *
 * @package Digidirect\ExtendedShippingRates\Model\Carrier
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Artificial extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = null;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory
     */
    protected $_carrierCollectionFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\Collection
     */
    protected $_carriersCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $_loadedCarriers = [];

    /**
     * @var RateRequest
     */
    protected $_request;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Helper\Config|null
     */
    protected $_configHelper;

    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Digidirect\ExtendedShippingRates\Model\CarrierFactory $carrierFactory
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Digidirect\ExtendedShippingRates\Helper\Config|null $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Digidirect\ExtendedShippingRates\Model\CarrierFactory $carrierFactory,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Digidirect\ExtendedShippingRates\Helper\Config $configHelper,
        GetSourceItemsBySku $getSourceItemsBySku,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_carrierFactory = $carrierFactory;
        $this->_carrierCollectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_configHelper = $configHelper;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->productRepository = $productRepository;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->prepareCarriers();
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        $this->setRequest($request);

        $result = [];
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier $carrier */
        $carrier = $this->findCarrier();
        if (!$carrier) {
            return $result;
        }

        $this->addData($carrier->getData());
        $this->_code = $carrier->getData('carrier_code');

        $methods = $carrier->getMethods();
        if (empty($methods)) {
            return $result;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData */
        foreach ($methods as $methodData) {
            if (!$methodData->getActive()) {
                continue;
            }
            $methodData->afterLoad();
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->getId());
            $method->setCarrierTitle($carrier->getTitle());
            $method->setMethod($methodData->getData(MethodInterface::CODE));
            $method->setCost($methodData->getData(MethodInterface::COST));
            $method->setAlternativeTitle($methodData->getData(MethodInterface::ALTERNATIVE_TITLE));
            $method->setAlternativeCode($methodData->getData(MethodInterface::ALTERNATIVE_CODE));
            $method = $this->applyRates($method, $methodData);

            if ($method) {
                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        $carrier = $this->findCarrier();
        if (!$carrier) {
            return [];
        }

        return $carrier->getMethodsCollection()->toAllowedMethodsArray();
    }

    /**
     * Get allowed shipping methods as array of objects
     *
     * @return array
     */
    public function getAllowedMethodCollectionArray()
    {
        $carrier = $this->findCarrier();
        if (!$carrier) {
            return [];
        }

        return $carrier->getMethodsCollection()->toAllowedMethodsArray(false);
    }

    /**
     * @param string $field
     * @return false|mixed|string
     */
    public function getConfigData($field)
    {
        $carrier = $this->findCarrier();
        if ($carrier) {
            return $carrier->getData($field);
        }

        return parent::getConfigData($field);
    }

    /**
     * @param RateRequest|null $_request
     * @return $this
     */
    protected function setRequest(RateRequest $_request = null)
    {
        $this->_request = $_request;

        return $this;
    }

    /**
     * @return RateRequest
     */
    protected function getRequest()
    {
        return $this->_request;
    }

    /**
     * Find corresponding carrier in the collection
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Carrier|null
     */
    protected function findCarrier()
    {
        $carrier = $this->_carrierFactory->create();
        if ($this->getOnlyActive() &&
            $carrier->getResource() instanceof \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier) {
            $carrier->getResource()->setOnlyActiveMethodsFlag();
        }
        $carrier->load($this->getData('id'), 'carrier_code');

        return $carrier;
    }

    /**
     * Get all data of the carrier specified by code (carrier_code)
     * It's possible to get the specified parameter ($param) of the carrier
     *
     * @param string $code
     * @param null $param
     * @return mixed|null
     */
    protected function getSpecificCarrierData($code, $param = null)
    {
        $item = $this->_carriersCollection->getItemByColumnValue('carrier_code', $code);
        if (!$item) {
            return null;
        }

        if (!$param) {
            return $item->getData();
        }

        return $item->getData($param);
    }

    /**
     * Prepare carriers collection & load items
     *
     * @return void
     */
    protected function prepareCarriers()
    {
        if (empty($this->_loadedCarriers)) {
            /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\Collection $carriersCollection */
            $this->_carriersCollection = $this->_carrierCollectionFactory->create();
            $this->_loadedCarriers = $this->_carriersCollection->getItems();
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $method
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function applyRates(
        \Magento\Quote\Model\Quote\Address\RateResult\Method $method,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
    ) {
        $disableMethodWithoutValidRates = false;
        $request = $this->getRequest();
        $rates = $methodData->getRates();
        $ratesApplied = [];

        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $rate */
        foreach ($rates as $rate) {
            if (!$rate->validateRequest($request)) {
                continue;
            }
            $ratesApplied[] = $rate;
        }

        if ($ratesApplied) {
            $filteredRates = $this->filterRatesBeforeApply($ratesApplied, $request, $methodData);
            /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $validRate */
            foreach ($filteredRates as $validRate) {
                $method = $validRate->applyRateToMethod($method, $request, $methodData);
            }
        } elseif ($disableMethodWithoutValidRates) {
            return null;
        } else {
            /**
             * Modification
             * Check stocks sources from cart and display days
             */


//            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//            $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
            $request = $this->getRequest();
            $items = $request->getAllItems();

            $qty = 0;
            foreach ($items as $item) {

                $prodId = $item->getProductId();
                $product = $this->productRepository->getById($prodId);

//                $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//                $product = $_objectManager->get('\Magento\Catalog\Model\Product')->load($prodId);

                $sourceItems = $this->getSourceItemsBySku->execute($product->getSku());

                foreach ($sourceItems as $sourceItemId => $sourceItem) {
                    $qty += $sourceItem->getQuantity();
                }
            }

            $methodCode = $methodData->getData('code');

            if ($methodCode == "standard") {
                $method->setMethodTitle(" (6 to 9 Days)");
            }elseif ($methodCode == "express") {
                $method->setMethodTitle(" (1 to 3 Days)");
            }elseif ($methodCode == "nextdayship") {
                $method->setMethodTitle(" (Next Day Delivery)");
            }elseif ($methodCode == "intlshippingnz") {
                $method->setMethodTitle(" (5 to 9 Days)");
            }elseif ($methodCode == "intlshipping") {
                $method->setMethodTitle(" (5 to 9 Days)");
            }

            //end modification
            //
            //$method->setMethodTitle($methodData->getData('title'));
            $method->setPrice($methodData->getData('price'));

        }

        return $method;
    }

    /**
     * @param array $rates
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function filterRatesBeforeApply(
        $rates,
        RateRequest $request,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
    ) {
        if (!$rates) {
            return $rates;
        }

        $multipleRatesCalculationType = $this->_configHelper->getMultipleRatesPrice($this->_storeManager->getWebsite());
        switch ($multipleRatesCalculationType) {
            case Rate::MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRIORITY:
                $resultRate = $this->getRateWithMaxPriority($rates);
                break;
            case Rate::MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRICE:
                $resultRate = $this->getRateWithMaxPrice($rates, $request, $methodData);
                break;
            case Rate::MULTIPLE_RATES_PRICE_CALCULATION_MIN_PRICE:
                $resultRate = $this->getRateWithMinPrice($rates, $request, $methodData);
                break;
            default:
                return $rates;
        }

        $resultRates = [$resultRate->getId() => $resultRate];

        return $resultRates;
    }

    /**
     * Find rate with max priority in array of rates
     *
     * @param array $rates
     * @return \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate
     */
    protected function getRateWithMaxPriority($rates)
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $currentRate */
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $rate */
        foreach ($rates as $currentRate) {
            if (!isset($rate) || $rate->getPriority() <= $currentRate->getPriority()) {
                $rate = $currentRate;
            }
        }

        return $rate;
    }

    /**
     * Find rate with max price in array of rates
     *
     * @param array $rates
     * @param RateRequest $request
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
     * @return \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate
     */
    protected function getRateWithMaxPrice(
        $rates,
        RateRequest $request,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
    ) {
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $currentRate */
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $rate */
        $actualRateCalculatedPrice = 0;
        foreach ($rates as $currentRate) {
            $currentRatePrice = $currentRate->getCalculatedPrice($request, $methodData);
            if (!isset($rate) || $actualRateCalculatedPrice <= $currentRatePrice) {
                $rate = $currentRate;
                $actualRateCalculatedPrice = $currentRatePrice;
            }
        }

        return $rate;
    }

    /**
     * Find rate with min price in array of rates
     *
     * @param array $rates
     * @param RateRequest $request
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
     * @return \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate
     */
    protected function getRateWithMinPrice(
        $rates,
        RateRequest $request,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
    ) {
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $currentRate */
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $rate */
        $actualRateCalculatedPrice = 0;
        foreach ($rates as $currentRate) {
            $currentRatePrice = $currentRate->getCalculatedPrice($request, $methodData);
            if (!isset($rate) || $actualRateCalculatedPrice >= $currentRatePrice) {
                $rate = $currentRate;
                $actualRateCalculatedPrice = $currentRatePrice;
            }
        }

        return $rate;
    }
}
