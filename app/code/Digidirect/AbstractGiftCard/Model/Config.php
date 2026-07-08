<?php

namespace Digidirect\AbstractGiftCard\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * AbstractGiftCard configuration model
 *
 */
class Config
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    /**
     * Service factory
     *
     * @var \Digidirect\AbstractGiftCard\Model\Service\Factory
     */
    protected $_serviceFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Digidirect\AbstractGiftCard\Model\Service\Factory $serviceFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Digidirect\AbstractGiftCard\Model\Service\Factory $serviceFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_serviceFactory = $serviceFactory;
    }

    /**
     * Retrieve active system services
     *
     * @return array
     * @api
     */
    public function getActiveServices()
    {
        $services = [];
        $serviceConfig = $this->_scopeConfig->getValue('giftcard_service', ScopeInterface::SCOPE_STORE, null);
        foreach ($serviceConfig as $code => $data) {
            if (isset($data['active'], $data['model']) && (bool)$data['active']) {
                /** @var ServiceInterface $serviceModel Actually it's wrong interface */
                $serviceModel = $this->_serviceFactory->create($data['model']);
                $serviceModel->setStore(null);
                if ($serviceModel->getConfigData('active', null)) {
                    $services[$code] = $serviceModel;
                }
            }
        }
        return $services;
    }
}
