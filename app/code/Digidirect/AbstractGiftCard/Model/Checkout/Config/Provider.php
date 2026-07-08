<?php

namespace Digidirect\AbstractGiftCard\Model\Checkout\Config;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class Provider
 */
class Provider implements ConfigProviderInterface
{
    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\ServiceList
     */
    private $_serviceListHelper;

    /**
     * Provider constructor.
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Digidirect\AbstractGiftCard\Helper\ServiceList $serviceListHelper
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Digidirect\AbstractGiftCard\Helper\ServiceList $serviceListHelper
    ) {
        $this->_helper = $helper;
        $this->_serviceListHelper = $serviceListHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $services = ['isDefault' => $this->_helper->isNativeGiftCardsAllowed()];
        if ($this->_helper->isActive()) {
            /**
             * @var \Digidirect\AbstractGiftCard\Model\ServiceInterface $service
             */
            foreach ($this->_serviceListHelper->getAllServices() as $service) {
                $services[$service->getCode()] = ['active' => $service->isAvailable()];
            }
        }
        return ['abstractGiftCardServices' => $services];
    }
}
