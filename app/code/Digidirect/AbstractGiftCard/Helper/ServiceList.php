<?php

namespace Digidirect\AbstractGiftCard\Helper;

use Magento\Framework\App\ObjectManager;
use Digidirect\AbstractGiftCard\Model\Service\AbstractService;

/**
 * AbstractGiftCard module base helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceList extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\GiftCardServiceListInterface
     */
    private $_serviceList;

    /**
     * @var \Digidirect\AbstractGiftCard\Model\Service\InstanceFactory
     */
    private $_serviceInstanceFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Model\Checks\SpecificationFactory
     */
    private $_serviceSpecificationFactory;

    /**
     * @var array
     */
    protected $_additionalChecks;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Array of loaded services
     *
     * @var array
     */
    protected $_services = [];

    /**
     * Container constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Digidirect\AbstractGiftCard\Model\Checks\SpecificationFactory $serviceSpecificationFactory
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Digidirect\AbstractGiftCard\Model\Checks\SpecificationFactory $serviceSpecificationFactory,
        array $additionalChecks = []
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_additionalChecks = $additionalChecks;
        $this->_serviceSpecificationFactory = $serviceSpecificationFactory;
    }

    /**
     * Declare template for service form block
     *
     * @param string $method
     * @param string $template
     * @return $this
     */
    public function setServiceFormTemplate($method = '', $template = '')
    {
        if (!empty($method) && !empty($template)) {
            if ($block = $this->getChildBlock('giftcard.service.' . $method)) {
                $block->setTemplate($template);
            }
        }
        return $this;
    }

    /**
     * Check service model
     *
     * @param \Digidirect\AbstractGiftCard\Model\ServiceInterface $service
     * @return bool
     */
    protected function _canUseService($service)
    {
        return true;

        /**
         * @TODO Implement Check Validation
         */

        $checks = array_merge(
            [
                AbstractService::CHECK_USE_ON_FRONT,
                AbstractService::CHECK_USE_FOR_COUNTRY,
                AbstractService::CHECK_USE_FOR_CURRENCY,
                AbstractService::CHECK_ORDER_TOTAL_MIN_MAX,
            ],
            $this->_additionalChecks
        );

        return $this->_serviceSpecificationFactory->create($checks)->isApplicable(
            $service,
            $this->getQuote()
        );
    }

    /**
     * Retrieve available services
     *
     * @return array
     */
    public function getServices()
    {
        if (empty($this->_services)) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            foreach ($this->_getGiftCardServiceList()->getActiveList($store) as $service) {
                $serviceInstance = $this->_getGiftCardServiceInstanceFactory()->create($service);
                if (($serviceInstance->isAvailable($quote) && $this->_canUseService($serviceInstance))) {
                    $this->_services[] = $serviceInstance;
                }
            }
        }
        return $this->_services;
    }

    /**
     * Retrieve all services
     *
     * @return array
     */
    public function getAllServices()
    {
        $services = [];
        $quote = $this->getQuote();
        $store = $quote ? $quote->getStoreId() : null;
        foreach ($this->_getGiftCardServiceList()->getList($store) as $service) {
            $serviceInstance =  $this->_getGiftCardServiceInstanceFactory()->create($service);
            $services[$serviceInstance->getCode()] = $serviceInstance;
        }
        return $services;
    }

    /**
     * Get service list.
     *
     * @return \Digidirect\AbstractGiftCard\Api\GiftCardServiceListInterface
     */
    private function _getGiftCardServiceList()
    {
        if ($this->_serviceList === null) {
            $this->_serviceList = ObjectManager::getInstance()->get(
                \Digidirect\AbstractGiftCard\Api\GiftCardServiceListInterface::class
            );
        }
        return $this->_serviceList;
    }

    /**
     * Get service instance factory.
     *
     * @return \Digidirect\AbstractGiftCard\Model\Service\InstanceFactory
     * @deprecated
     */
    private function _getGiftCardServiceInstanceFactory()
    {
        if ($this->_serviceInstanceFactory === null) {
            $this->_serviceInstanceFactory = ObjectManager::getInstance()->get(
                \Digidirect\AbstractGiftCard\Model\Service\InstanceFactory::class
            );
        }
        return $this->_serviceInstanceFactory;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * @param array $checks
     * @return $this
     */
    public function addAdditionalChecks($checks)
    {
        array_merge($this->_additionalChecks, $checks);
        return $this;
    }
}
