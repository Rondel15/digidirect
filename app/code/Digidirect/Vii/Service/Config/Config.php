<?php

namespace Digidirect\Vii\Service\Config;

use Digidirect\AbstractGiftCard\Service\Config\Config as AbstractGiftCardConfig;
use Digidirect\AbstractGiftCard\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Digidirect\Utilities\Model\System\Config\Backend\DefaultOptionModel;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Order;

/**
 * Class Config
 * @package Digidirect\Vii\Service\Config
 */
class Config extends AbstractGiftCardConfig
{
    const KEY_ACTIVE = 'active';
    const KEY_ENDPOINT_URL = 'endpoint_url';
    const KEY_MERCHANT_ACCOUNT_USER_NAME = 'user_name';
    const KEY_MERCHANT_ACCOUNT_PASSWORD = 'password';
    const KEY_MERCHANT_ACCOUNT_ID = 'account_id';
    const KEY_NOT_RESPONDING_MESSAGE = 'mapping_not_responding_message';
    const NOT_RESPONDING_DEFAULT_MESSAGE = 'Vii server is unavailable now, please try again later.';
    const KEY_CODE = 'code';
    const KEY_CANCELLATION_DELAY = 'cancellation_delay';

    /**
     * @var DefaultOptionModel
     */
    protected $defaultOptionModel;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param DefaultOptionModel $defaultOptionModel
     * @param Data $helper
     * @param null $serviceCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DefaultOptionModel $defaultOptionModel,
        Data $helper,
        $serviceCode = null,
        $pathPattern = AbstractGiftCardConfig::DEFAULT_PATH_PATTERN
    ) {
        AbstractGiftCardConfig::__construct($scopeConfig, $serviceCode, $pathPattern);
        $this->defaultOptionModel = $defaultOptionModel;
        $this->helper = $helper;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getUserName($storeId = null)
    {
        return $this->getValue(Config::KEY_MERCHANT_ACCOUNT_USER_NAME, $storeId);
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getPassword($storeId = null)
    {
        return $this->getValue(Config::KEY_MERCHANT_ACCOUNT_PASSWORD, $storeId);
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getEndpointUrl($storeId = null)
    {
        return $this->getValue(Config::KEY_ENDPOINT_URL, $storeId);
    }

    /**
     * Get service configuration status
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * Get Merchant account ID
     *
     * @param null|int $storeId
     * @return string
     */
    public function getMerchantAccountId($storeId = null)
    {
        return $this->getValue(self::KEY_MERCHANT_ACCOUNT_ID, $storeId);
    }

    /**
     * @param null $storeId
     * @return array
     */
    public function getNotRespondingMessageMapping($storeId = null)
    {
        $matrix = $this->getValue(self::KEY_NOT_RESPONDING_MESSAGE, $storeId);
        $result = [];
        $row = $this->defaultOptionModel->convertValueToArray($matrix);
        foreach ($row as $config) {
            $result[$config['request_type_column']] = $config['message_column'];
        }

        return $result;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getServiceCode($storeId = null)
    {
        return $this->getValue(self::KEY_CODE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getCancellationDelay($storeId = null)
    {
        return $this->getValue(self::KEY_CANCELLATION_DELAY, $storeId);
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getExpireDate()
    {
        $period = $this->getCancellationDelay();
        if (!$period) {
            return null;
        }
        $currentDate = new \DateTime();
        return $currentDate->modify('-'. $period . 'days')->format(DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isAcceptAvailable($order)
    {
        if ($this->helper->isAcceptOnlyForPaidOrders()) {
            $result = $order->getBaseTotalDue() <= 0 && $order->hasInvoices();
        } else {
            $result = $order->getBaseTotalDue() <= 0
                || $order->getState() == Order::STATE_PROCESSING
                || $order->getState() == Order::STATE_COMPLETE;
        }
        return $result;
    }
}
