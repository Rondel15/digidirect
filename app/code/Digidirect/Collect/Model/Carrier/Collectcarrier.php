<?php

namespace Digidirect\Collect\Model\Carrier;

use Digidirect\Collect\Helper\Config\Address as AddressHelper;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Collectcarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const COLLECT_CARRIER_CODE = 'collect';
    const COLLECT_SHIPPING_METHOD = 'collect_collect';

    /**
     * Carrier Code
     *
     * @var string
     */
    protected $_code = self::COLLECT_CARRIER_CODE;

    /**
     * Rate Result Factory
     *
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * Rate Method Factory
     *
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var \Digidirect\Collect\Helper\Config\Address
     */
    protected $addressHelper;

    /**
     * Shipping address validation flag
     *
     * @var bool
     */
    protected $_shippingAddressValidationRequiredFlag = false;

    /**
     * Collectcarrier constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Digidirect\Collect\Helper\Config\Address $addressHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        AddressHelper $addressHelper,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->addressHelper = $addressHelper;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return ['collect' => $this->getConfigData('name')];
    }

    /**
     * Collect rates
     *
     * @param RateRequest $request
     * @return bool|Result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getCarrierTitle());

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getMethodTitle());

        $amount = $this->getPriceCalculation();

        $method->setPrice($amount);
        $method->setCost($amount);

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        $result->append($method);

        return $result;
    }

    /**
     * Get carrier title
     *
     * @return string
     */
    public function getCarrierTitle()
    {
        $carrierTitle = $this->getConfigData('title');
        return $carrierTitle;
    }

    /**
     * Get method title
     *
     * @return string
     */
    public function getMethodTitle()
    {
        $methodTitle = $this->getConfigData('name');
        if ($this->addressHelper->getCollectHelper()->isSingleVariation()) {
            return $methodTitle . $this->getMethodDescription();
        }
        return $methodTitle;
    }

    /**
     * Get method description
     *
     * @return string
     */
    public function getMethodDescription()
    {
        if (!$methodDescription = $this->getConfigData('description')) {
            $methodDescription = $this->addressHelper->getDefaultAddressHtml();
        }
        return $methodDescription;
    }

    /**
     * Get price calculation
     *
     * @return float
     */
    public function getPriceCalculation()
    {
        return 0;
    }

    /**
     * Shipping address validation flag
     *
     * @return bool
     */
    public function isShippingAddressValidationRequired()
    {
        return $this->_shippingAddressValidationRequiredFlag;
    }
}
