<?php

namespace Digidirect\AbstractGiftCard\Model;

use Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class AbstractGiftCardEntity
 * @method string|null  getToken()
 * @method $this        setToken($token)
 * @method float|null   getAmount()
 * @method $this        setAmount($amount)
 * @method int|null     getStatus()
 * @method $this        setStatus($status)
 *
 * @package Digidirect\AbstractGiftCard\Model
 */
class AbstractGiftCardEntity extends AbstractModel implements AbstractGiftCardEntityInterface
{
    const STATUS_HOLD = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_CANCEL = 3;

    /**
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory
     */
    protected $_giftCardAccountFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_entityOrderData = [];

    /**
     * AbstractGiftCardEntity constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_giftCardAccountFactory = $giftcardaccountFactory;
        $this->_helper = $helper;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order::class);
    }

    /**
     * Get related giftcard account.
     *
     * @return \Magento\GiftCardAccount\Model\Giftcardaccount
     */
    public function getGiftCardAccount()
    {
        $giftCardAccount = $this->_giftCardAccountFactory->create()->load($this->getGiftcardAccountId());
        $giftCardAccount->setAbstractGiftCardEntity($this);

        return $giftCardAccount;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * @return string
     */
    public function getServiceCode()
    {
        return $this->getData('service_code');
    }

    /**
     * @return \Digidirect\AbstractGiftCard\Model\ServiceInterface
     */
    public function getService()
    {
        if (!$this->getData('service')) {
            $service = $this->_helper->getServiceInstance($this->getServiceCode());
            $service->setAbstractGiftCardEntity($this);
            $this->setData('service', $service);
        }

        return $this->getData('service');
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setServiceCode($code)
    {
        $this->setData('service_code', $code);

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->setData('code', $code);

        return $this;
    }

    /**
     * @param string $pin
     * @return $this
     */
    public function setPin($pin)
    {
        $this->setData('pin', $pin);

        return $this;
    }

    /**
     * @return string
     */
    public function getPin()
    {
        return $this->getData('pin');
    }

    /**
     * @param int $orderId
     * @return \Magento\Framework\DataObject
     */
    public function getEntityOrderData($orderId)
    {
        if (!isset($this->_entityOrderData[$orderId])) {
            $this->_entityOrderData[$orderId] = new \Magento\Framework\DataObject();
        }

        return $this->_entityOrderData[$orderId];
    }

    /**
     * @param int $orderId
     * @param \Magento\Framework\DataObject $dataObject
     * @return $this
     */
    public function addEntityOrderData($orderId, $dataObject)
    {
        $this->_entityOrderData[$orderId] = $dataObject;

        return $this;
    }
}
