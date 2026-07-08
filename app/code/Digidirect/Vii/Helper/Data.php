<?php

namespace Digidirect\Vii\Helper;

use Digidirect\Utilities\Model\System\Config\Backend\DefaultOptionModel;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoresConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Digidirect\Vii\Model\ResourceModel\Customer\Visitor;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class Data
 * @package Digidirect\Vii\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_CARD_STATUS = 'giftcard_service/vii/mapping_card_status';
    const XML_PATH_ORDER_CANCEL_EMAIL_SENDER = 'giftcard_service/vii/order_cancel_email_sender';
    const XML_PATH_ORDER_CANCEL_EMAIL_TEMPLATE = 'giftcard_service/vii/order_cancel_email_template';

    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var DefaultOptionModel
     */
    protected $defaultOptionModel;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoresConfig $storesConfig
     * @param DefaultOptionModel $defaultOptionModel
     * @param SessionManagerInterface $sessionManager
     * @param Visitor $visitor
     */
    public function __construct(
        Context $context,
        StoresConfig $storesConfig,
        DefaultOptionModel $defaultOptionModel,
        SessionManagerInterface $sessionManager,
        Visitor $visitor
    ) {
        parent::__construct($context);
        $this->storesConfig = $storesConfig;
        $this->defaultOptionModel = $defaultOptionModel;
        $this->sessionManager = $sessionManager;
        $this->visitor = $visitor;
    }

    /**
     * @param null $storeId
     * @return array
     */
    public function getCardStatusesMatrix($storeId = null)
    {
        $matrix = $this->scopeConfig->getValue(
            self::XML_PATH_CARD_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = [];
        $row = $this->defaultOptionModel->convertValueToArray($matrix);
        foreach ($row as $config) {
            $result[$config['card_status_id_column']] = $config['card_status_column'];
        }

        return $result;
    }

    /**
     * @param int $statusId
     * @param int $storeId
     * @return mixed|null
     */
    public function getCardStatusLabel($statusId, $storeId = null)
    {
        return $this->getCardStatusesMatrix($storeId)[$statusId] ?? null;
    }

    /**
     * @param CartInterface $quote
     * @return bool
     */
    public function isCustomerAsGuest($quote)
    {
        if ($quote->getCustomerId()) {
            return true;
        }
        return true;
    }

    /**
     * @param CartInterface $quote
     * @return $this
     */
    public function saveVisitorData($quote)
    {
        $visitorData = $this->sessionManager->getVisitorData();
        if (isset($visitorData['visitor_id'])) {
            $visitorDataObject = new \Magento\Framework\DataObject();
            $visitorDataObject->setVisitorId($visitorData['visitor_id']);
            $visitorDataObject->setQuoteId($quote->getId());
            $this->visitor->saveVisitorData($visitorDataObject);
        }
        return $this;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getOrderCancelEmailSender($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_CANCEL_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getOrderCancelEmailTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_CANCEL_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
