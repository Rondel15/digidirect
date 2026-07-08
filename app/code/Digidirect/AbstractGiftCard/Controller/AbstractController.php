<?php

namespace Digidirect\AbstractGiftCard\Controller;

abstract class AbstractController extends \Magento\Checkout\Controller\Cart
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var mixed
     */
    protected $_serviceInstance;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * AbstractController constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Digidirect\AbstractGiftCard\Helper\Data $helper
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
        $this->_helper = $helper;
    }

    /**
     * @return \Digidirect\AbstractGiftCard\Model\ServiceInterface|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initService()
    {
        if ($serviceData = $this->getRequest()->getPost('giftcard_service')) {
            $serviceInstance = $this->getServiceInstanceByCode($serviceData['service_code']);
            $serviceInstance->getAbstractGiftCardEntity()->addData($serviceData);
            return $serviceInstance;
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Request Data'));
    }

    /**
     * @param string $serviceCode
     * @return \Digidirect\AbstractGiftCard\Model\ServiceInterface|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getServiceInstanceByCode($serviceCode)
    {
        if (!$this->_serviceInstance) {
            try {
                $this->_serviceInstance = $this->_helper->getServiceInstance($serviceCode);
            } catch (\UnexpectedValueException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Service Used'));
                throw $e;
            }
        }

        return $this->_serviceInstance;
    }
}
