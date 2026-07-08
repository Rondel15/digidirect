<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Newsletter\Helper\Data as NewsletterHelper;
use Magento\Newsletter\Model\Subscriber as NewsletterSubscriber;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\Data;

class Subscriber extends NewsletterSubscriber
{
    protected $_subscriberEncoded;
    protected $_dataHelper;
    protected $_messageManager;
    protected $_attributeMetadataDataProvider;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\HistoryManagement
     */
    private $historyManagement;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    public function __construct(
        Context $context,
        Registry $registry,
        NewsletterHelper $newsletterData,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        StateInterface $inlineTranslation,
        SubscriberEncoded $subscriberEncoded,
        Data $dataHelper,
        ManagerInterface $messageManager,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        HistoryManagement $historyManagement = null,
        Config $config = null
    ) {
        $this->_subscriberEncoded = $subscriberEncoded;
        $this->_dataHelper = $dataHelper;
        $this->_messageManager = $messageManager;
        $this->_attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->historyManagement = $historyManagement ?: ObjectManager::getInstance()->get(HistoryManagement::class);
        $this->config = $config ?: ObjectManager::getInstance()->get(Config::class);
        parent::__construct(
            $context,
            $registry,
            $newsletterData,
            $scopeConfig,
            $transportBuilder,
            $storeManager,
            $customerSession,
            $customerRepository,
            $customerAccountManagement,
            $inlineTranslation,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function customSubscribe($email, $controller, $data = [])
    {
        $customer = $this->_subscriberEncoded->validateCustomer($data);
        if ($customer === false) {
            return false;
        }

        $address = $this->_subscriberEncoded->validateAddress($data);
        if ($address === false) {
            return false;
        }

        if ($customerId = $this->_subscriberEncoded->tryRegisterCustomer($customer, $controller)) {
            $saveAddress = true;
            $systemItems = $this->_dataHelper->getPopupFormFields(0, false);
            foreach ($systemItems as $name => $value) {
                if (! $address->getData($name)
                    && $this->_attributeMetadataDataProvider->getAttribute('customer_address', $name)->getIsRequired()
                ) {
                    $saveAddress = false;
                    break;
                }
            }

            if ($saveAddress) {
                // save address
                $address->setCustomerId($customerId)
                    ->setIsDefaultBilling(false)
                    ->setIsDefaultShipping(false);

                $address->save();
            }
        }

        $status = $this->subscribe($email);

        // Fix for the subcriber update in 2.4, we check the loadBySubscriberEmail method to split old and new logic.
        if (method_exists($this, 'loadBySubscriberEmail')) {
            $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
            $subscriber = $this->loadBySubscriberEmail($email, $websiteId);
            $subscriber->addData(
                $this->_subscriberEncoded->getAdditionalData($data)
            );
            $subscriber->save();
        } else {
            $this->addData(
                $this->_subscriberEncoded->getAdditionalData($data)
            );
            $this->save();
        }

        if ($status == self::STATUS_NOT_ACTIVE) {
            $this->_subscriberEncoded->holdSubscribe($email, $data);
            $this->_messageManager->addSuccessMessage(
                __('Thank you for signing up. You will receive a confirmation email within 24-48 hours.')
            );
        } else {
            $this->_subscriberEncoded->subscribe($email, $data);
            if ($successText = $this->_subscriberEncoded->getPopup()->getPreparedTextSuccess()) {
                $this->_messageManager->addSuccessMessage($successText);
            }
        }
        return $status;
    }

    /**
     * @deprecated since 4.6.0
     * @see \Plumrocket\Newsletterpopup\Model\HistoryManagement::logCanceling
     */
    public function cancel()
    {
        if (! $this->config->isModuleEnabled() || $this->_dataHelper->isAdmin()) {
            return;
        }
        $popup = $this->_subscriberEncoded->getPopup();
        if (! $popup->getId()) {
            return;
        }
        $this->historyManagement->logCanceling($popup);
    }

    public function confirm($code)
    {
        $result = parent::confirm($code);
        if ($result && $this->config->isModuleEnabled()) {
            $this->_subscriberEncoded->releaseSubscribe($this);
        }
        return $result;
    }

    public function sendConfirmationSuccessEmail()
    {
        if ($this->_subscriberEncoded->getPopup()->getSendEmail()) {
            if ($this->config->isModuleEnabled()
                && $this->_subscriberEncoded->getPopup()->getId() > 0
            ) {
                return $this;
            }
            return parent::sendConfirmationSuccessEmail();
        }

        return $this;
    }
}
