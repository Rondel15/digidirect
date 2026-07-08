<?php

namespace Digidirect\AbstractGiftCard\Model\Service;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Digidirect\AbstractGiftCard\Api;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * GiftCard service abstract model
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractService extends \Magento\Framework\Model\AbstractExtensibleModel implements
    ServiceInterface
{
    const ACTION_CHECK_STATUS = 'check_status';

    const ACTION_HOLD = 'HOLD';

    const ACTION_ACCEPT = 'accept';

    const ACTION_CANCEL = 'cancel';

    const STATUS_UNKNOWN = 'UNKNOWN';

    const STATUS_ACCEPTED = 'ACCEPTED';

    const STATUS_ERROR = 'ERROR';

    const STATUS_DENIED = 'DENIED';

    const STATUS_SUCCESS = 'SUCCESS';

    /**
     * Different giftcard service checks.
     */
    const CHECK_USE_FOR_COUNTRY = 'country';

    const CHECK_USE_FOR_CURRENCY = 'currency';

    const CHECK_USE_ON_FRONT = 'on_front';

    const CHECK_USE_INTERNAL = 'internal';

    const CHECK_ORDER_TOTAL_MIN_MAX = 'total';

    /**
     * @var string
     */
    protected $_code;

    /**
     * @var string
     */
    protected $_formBlockType = \Digidirect\AbstractGiftCard\Block\Form::class;

    /**
     * GiftCard Service feature
     *
     * @var bool
     */
    protected $_canCheckStatus = false;

    /**
     * GiftCard Service feature
     *
     * @var bool
     */
    protected $_canHold = false;

    /**
     * GiftCard Service feature
     *
     * @var bool
     */
    protected $_canAccept = false;

    /**
     * GiftCard Service feature
     *
     * @var bool
     */
    protected $_canCancel = false;

    /**
     * GiftCard Service feature
     *
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * GiftCard Service feature
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * GiftCard Service feature
     *
     * @var bool
     */
    protected $_canUseOnFront = true;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface
     */
    protected $_logger;

    /**
     * @var array
     */
    private $_debugReplacePrivateDataKeys;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_initializeData($data);
    }

    /**
     * Initializes injected data
     *
     * @param array $data
     * @return void
     */
    protected function _initializeData($data = [])
    {
        if (!empty($data['formBlockType'])) {
            $this->_formBlockType = $data['formBlockType'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setStore($storeId)
    {
        $this->setData('store', (int)$storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getStore()
    {
        return $this->getData('store');
    }

    /**
     * Check authorize availability
     *
     * @return bool
     * @api
     */
    public function canCheckStatus()
    {
        return $this->_canCheckStatus;
    }

    /**
     * Check hold availability
     *
     * @return bool
     * @api
     */
    public function canHold()
    {
        return $this->_canHold;
    }

    /**
     * Check accept availability
     *
     * @return bool
     * @api
     */
    public function canAccept()
    {
        return $this->_canAccept;
    }

    /**
     * Check cancel availability
     *
     * @return bool
     * @api
     */
    public function canCancel()
    {
        return $this->_canCancel;
    }

    /**
     * Check refund availability
     *
     * @return bool
     * @api
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }

    /**
     * Using internal pages for input service data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return $this->_canUseInternal;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseOnFront()
    {
        return $this->_canUseOnFront;
    }

    /**
     * To check billing country is allowed for the giftcard service
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        /*
        for specific country, the flag will set up as 1
        */
        if ($this->getConfigData('allowspecific') == 1) {
            $availableCountries = explode(',', $this->getConfigData('specificcountry'));
            if (!in_array($country, $availableCountries)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    /**
     * Retrieve giftcard service code
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCode()
    {
        if (empty($this->_code)) {
            throw new LocalizedException(__('We cannot retrieve the giftcard service code.'));
        }
        return $this->_code;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        return $this->_formBlockType;
    }

    /**
     * Validate giftcard service information object
     *
     * @return $this
     * @throws LocalizedException
     * @api
     */
    public function validate()
    {
        // @TODO implement correct validation
        return $this;
    }

    /**
     * Gift card check status operation
     *
     * @return $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkStatus()
    {
        if (!$this->canCheckStatus()) {
            throw new LocalizedException(__('The check status operation is not available.'));
        }

        return $this;
    }

    /**
     * Hold funds operation
     *
     * @param double $amount
     * @return $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function hold($amount)
    {
        if (!$this->canHold()) {
            throw new LocalizedException(__('The hold operation is not available.'));
        }

        return $this;
    }

    /**
     * Accept payment operation
     *
     * @param double $amount
     * @param null $token
     * @return $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function accept($amount, $token = null)
    {
        if (!$this->canAccept()) {
            throw new LocalizedException(__('The accept operation is not available.'));
        }

        return $this;
    }

    /**
     * Cancel payment operation
     *
     * @param string $reason
     * @param null $token
     * @param null $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function cancel($reason = '', $token = null, $amount = null)
    {
        if (!$this->canCancel()) {
            throw new LocalizedException(__('The cancel operation is not available.'));
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param double $amount
     * @return $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refund($amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund operation is not available.'));
        }
        return $this;
    }

    /**
     * Retrieve giftcard service title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getConfigData('title');
    }

    /**
     * Retrieve information from service configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'giftcard_service/' . $this->getCode() . '/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check whether giftcard service can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        $checkResult = new DataObject();
        $checkResult->setData('is_available', true);

        // for future use in observers
        $this->_eventManager->dispatch(
            'giftcard_service_is_active',
            [
                'result' => $checkResult,
                'service_instance' => $this,
                'quote' => $quote
            ]
        );

        return $checkResult->getData('is_available');
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->getConfigData('active', $storeId);
    }

    /**
     * Log debug data to file
     *
     * @param array $debugData
     * @return void
     */
    protected function _debug($debugData)
    {
        $this->_logger->debug(
            $debugData,
            $this->getDebugReplacePrivateDataKeys(),
            $this->getDebugFlag()
        );
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @api
     */
    public function getDebugFlag()
    {
        return (bool)(int)$this->getConfigData('debug');
    }

    /**
     * Used to call debug method from not GiftCard Service context
     *
     * @param mixed $debugData
     * @return void
     * @api
     */
    public function debugData($debugData)
    {
        $this->_debug($debugData);
    }

    /**
     * Return replace keys for debug data
     *
     * @return array
     */
    public function getDebugReplacePrivateDataKeys()
    {
        return (array) $this->_debugReplacePrivateDataKeys;
    }
}
