<?php

namespace Digidirect\AbstractGiftCard\Model\Service;

use Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface;
use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Digidirect\AbstractGiftCard\Service\Command\CommandManagerInterface;
use Digidirect\AbstractGiftCard\Service\Command\CommandPoolInterface;
use Digidirect\AbstractGiftCard\Service\Config\ValueHandlerPoolInterface;
use Digidirect\AbstractGiftCard\Service\Data\ServiceDataObjectFactory;
use Digidirect\AbstractGiftCard\Service\Validator\ValidatorPoolInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManager;

/**
 * service facade. Abstract method adapter
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Adapter implements ServiceInterface
{
    /**
     * @var ValueHandlerPoolInterface
     */
    private $_valueHandlerPool;

    /**
     * @var ValidatorPoolInterface
     */
    private $_validatorPool;

    /**
     * @var CommandPoolInterface
     */
    private $_commandPool;

    /**
     * @var int
     */
    private $_storeId;

    /**
     * @var string
     */
    private $_formBlockType;

    /**
     * @var string
     */
    private $_code;

    /**
     * @var ManagerInterface
     */
    private $_eventManager;

    /**
     * @var ServiceDataObjectFactory
     */
    private $_serviceDataObjectFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Service\Command\CommandManagerInterface
     */
    private $_commandExecutor;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface
     */
    private $_abstractGiftCardEntity;

    /**
     * @var \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntityFactory
     */
    private $_abstractGiftCardEntityFactory;

    /**
     * @var \Magento\GiftCardAccount\Model\Giftcardaccount
     */
    protected $_giftCardAccount;

    /**
     * @var null
     */
    protected $_token = null;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_abstractGiftCardHelper;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\Order|null
     */
    protected $order;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote;

    /**
     * Adapter constructor.
     *
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param ServiceDataObjectFactory $serviceDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntityFactory $abstractGiftCardEntityFactory
     * @param \Digidirect\AbstractGiftCard\Helper\Data $abstractGiftCardHelper
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param CommandManagerInterface|null $commandExecutor
     * @param StoreManager|null $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        ServiceDataObjectFactory $serviceDataObjectFactory,
        $code,
        $formBlockType,
        \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntityFactory $abstractGiftCardEntityFactory,
        \Digidirect\AbstractGiftCard\Helper\Data $abstractGiftCardHelper,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null,
        StoreManager $storeManager = null
    ) {
        $this->_valueHandlerPool = $valueHandlerPool;
        $this->_validatorPool = $validatorPool;
        $this->_commandPool = $commandPool;
        $this->_code = $code;
        $this->_formBlockType = $formBlockType;
        $this->_eventManager = $eventManager;
        $this->_serviceDataObjectFactory = $serviceDataObjectFactory;
        $this->_commandExecutor = $commandExecutor;
        $this->_abstractGiftCardEntityFactory = $abstractGiftCardEntityFactory;
        $this->_abstractGiftCardHelper = $abstractGiftCardHelper;
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreManager::class);
    }

    /**
     * Returns Validator pool
     *
     * @return ValidatorPoolInterface
     * @throws \DomainException
     */
    public function getValidatorPool()
    {
        if ($this->_validatorPool === null) {
            throw new \DomainException('Validator pool is not configured for use.');
        }

        return $this->_validatorPool;
    }

    /**
     * @inheritdoc
     */
    public function canCheckStatus()
    {
        return $this->_canPerformCommand('check_status');
    }

    /**
     * @inheritdoc
     */
    public function canHold()
    {
        return $this->_canPerformCommand('hold');
    }

    /**
     * @inheritdoc
     */
    public function canAccept()
    {
        return $this->_canPerformCommand('accept');
    }

    /**
     * @inheritdoc
     */
    public function canCancel()
    {
        return $this->_canPerformCommand('cancel');
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        return $this->_canPerformCommand('refund');
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return (bool)$this->_getConfiguredValue('can_use_internal');
    }

    /**
     * @inheritdoc
     */
    public function canUseOnFront()
    {
        return (bool)$this->_getConfiguredValue('can_use_on_front');
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        $checkResult = new DataObject();
        $checkResult->setData('is_available', true);
        try {
            $validator = $this->getValidatorPool()->get('availability');
            $result = $validator->validate(
                [
                    'service' => $this->_serviceDataObjectFactory->create($this),
                ]
            );

            $checkResult->setData('is_available', $result->isValid());
            // @codingStandardsIgnoreStart
        } catch (\Exception $e) {
            // pass
        }
        // @codingStandardsIgnoreEnd

        // for future use in observers
        $this->_eventManager->dispatch(
            'gitcard_service_is_active',
            [
                'result' => $checkResult,
                'service_instance' => $this,
                'quote' => $quote,
            ]
        );

        return $checkResult->getData('is_available');
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return $this->_getConfiguredValue('active', $storeId);
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        try {
            $validator = $this->getValidatorPool()->get('country');
        } catch (\Exception $e) {
            return true;
        }

        $result = $validator->validate(['country' => $country, 'storeId' => $this->getStore()]);

        return $result->isValid();
    }

    /**
     * @inheritdoc
     */
    public function canUseForCurrency($currencyCode)
    {
        try {
            $validator = $this->getValidatorPool()->get('currency');
        } catch (\Exception $e) {
            return true;
        }

        $result = $validator->validate(['currency' => $currencyCode, 'storeId' => $this->getStore()]);

        return $result->isValid();
    }

    /**
     * @param string $commandCode
     * @return bool
     */
    protected function _canPerformCommand($commandCode)
    {
        return $this->_abstractGiftCardHelper->isActive() && (bool)$this->_getConfiguredValue('can_' . $commandCode);
    }

    /**
     * Unifies configured value handling logic
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    private function _getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->_valueHandlerPool->get($field);
        $subject = [
            'field' => $field,
            'service' => $this->_serviceDataObjectFactory->create($this),
        ];

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }

    /**
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->_getConfiguredValue($field, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        try {
            $validator = $this->getValidatorPool()->get('global');
        } catch (\Exception $e) {
            return $this;
        }

        $result = $validator->validate(
            ['service' => $this, 'storeId' => $this->getStore()]
        );

        if (!$result->isValid()) {
            throw new LocalizedException(
                __(implode("\n", $result->getFailsDescription()))
            );
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function checkStatus()
    {
        $this->_executeCommand('check_status', ['service' => $this]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hold($amount)
    {
        $this->_executeCommand('hold', ['service' => $this, 'amount' => $amount]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function accept($amount, $token = null)
    {
        $this->_executeCommand('accept', ['service' => $this, 'amount' => $amount, 'token' => $token]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cancel($reason = 'cancel', $token = null, $amount = null)
    {
        $this->_executeCommand(
            'cancel',
            ['service' => $this, 'token' => $token, 'reason' => $reason, 'amount' => $amount]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function refund($amount)
    {
        $this->_executeCommand('refund', ['service' => $this, 'amount' => $amount]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _executeCommand($commandCode, array $arguments = [])
    {
        if (!$this->_canPerformCommand($commandCode)) {
            return null;
        }

        $currentStore = $this->storeManager->getStore();
        try {
            $store = $this->storeManager->getStore($this->getStore());
            $this->storeManager->setCurrentStore($store);

            /** @var ServiceInterface|null $service */
            $service = null;
            if (isset($arguments['service']) && $arguments['service'] instanceof ServiceInterface) {
                $service = $arguments['service'];
                $arguments['service'] = $this->_serviceDataObjectFactory->create($arguments['service']);
            }

            if ($this->_commandExecutor !== null) {
                return $this->_commandExecutor->executeByCode($commandCode, $service, $arguments);
            }

            if ($this->_commandPool === null) {
                throw new \DomainException('Command pool is not configured for use.');
            }

            $command = $this->_commandPool->get($commandCode);

            return $command->execute($arguments);
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_getConfiguredValue('title');
    }

    /**
     * @inheritdoc
     */
    public function setStore($storeId)
    {
        $this->_storeId = (int)$storeId;
    }

    /**
     * @inheritdoc
     */
    public function getStore()
    {
        return $this->_storeId;
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return $this->_formBlockType;
    }

    /**
     * @return null
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setGiftCardData($data = [])
    {
        $this->_giftCardData = $data;

        return $this;
    }

    /**
     * @param null $key
     * @return null
     */
    public function getGiftCardData($key = null)
    {
        if (($key !== null) && isset($this->_giftCardData[$key])) {
            return $this->_giftCardData[$key];
        } elseif ($key !== null) {
            return null;
        }

        return $this->_giftCardData;
    }

    /**
     * @param \Magento\GiftCardAccount\Model\Giftcardaccount $giftCardAccount
     * @return $this
     */
    public function setGiftCardAccount(\Magento\GiftCardAccount\Model\Giftcardaccount $giftCardAccount)
    {
        $this->_giftCardAccount = $giftCardAccount;

        return $this;
    }

    /**
     * @return \Magento\GiftCardAccount\Model\Giftcardaccount
     */
    public function getGiftCardAccount()
    {
        return $this->_giftCardAccount;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setLastToken($token)
    {
        $this->_token = $token;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastToken()
    {
        return $this->_token;
    }

    /**
     * @param AbstractGiftCardEntityInterface $entity
     * @return $this
     */
    public function setAbstractGiftCardEntity(\Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $entity)
    {
        $this->_abstractGiftCardEntity = $entity;

        return $this;
    }

    /**
     * @return AbstractGiftCardEntityInterface
     */
    public function getAbstractGiftCardEntity()
    {
        if (!$this->_abstractGiftCardEntity) {
            $this->_abstractGiftCardEntity = $this->_abstractGiftCardEntityFactory->create();
        }

        return $this->_abstractGiftCardEntity;
    }

    /**
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }
}
