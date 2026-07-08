<?php

namespace Digidirect\AbstractGiftCard\Helper;

use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use Digidirect\AbstractGiftCard\Block\Form;
use Digidirect\AbstractGiftCard\Model\InfoInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\LayoutFactory;
use Digidirect\AbstractGiftCard\Model\Service\AbstractService;
use Digidirect\AbstractGiftCard\Model\ServiceInterface;

/**
 * AbstractGiftCard module base helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GIFTCARD_SERVICES = 'giftcard_service';

    /**
     * @var \Digidirect\AbstractGiftCard\Model\Config
     */
    protected $_serviceConfig;

    /**
     * Layout
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Factory for service models
     *
     * @var \Digidirect\AbstractGiftCard\Model\Service\Factory
     */
    protected $_serviceFactory;

    /**
     * App emulation model
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param LayoutFactory $layoutFactory
     * @param \Digidirect\AbstractGiftCard\Model\Service\Factory $serviceFactory
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Digidirect\AbstractGiftCard\Model\Config $serviceConfig
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LayoutFactory $layoutFactory,
        \Digidirect\AbstractGiftCard\Model\Service\Factory $serviceFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Digidirect\AbstractGiftCard\Model\Config $serviceConfig,
        \Magento\Framework\App\Config\Initial $initialConfig
    ) {
        parent::__construct($context);
        $this->_layout = $layoutFactory->create();
        $this->_serviceFactory = $serviceFactory;
        $this->_appEmulation = $appEmulation;
        $this->_serviceConfig = $serviceConfig;
        $this->_initialConfig = $initialConfig;
    }

    /**
     * @param string $code
     * @return string
     */
    protected function _getServiceModelConfigName($code)
    {
        return sprintf('%s/%s/model', self::XML_PATH_GIFTCARD_SERVICES, $code);
    }

    /**
     * Retrieve method model object
     *
     * @param string $code
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return ServiceInterface
     */
    public function getServiceInstance($code)
    {
        $class = $this->scopeConfig->getValue(
            $this->_getServiceModelConfigName($code),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$class) {
            throw new \UnexpectedValueException('GiftCard model name is not provided in config!');
        }

        return $this->_serviceFactory->create($class);
    }

    /**
     * @param ServiceInterface $service
     * @param LayoutInterface $layout
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getServiceFormBlock(ServiceInterface $service, LayoutInterface $layout)
    {
        $block = $layout->createBlock($service->getFormBlockType(), $service->getCode());
        $block->setMethod($service);
        return $block;
    }

    /**
     * Retrieve all services
     *
     * @return array
     */
    public function getGiftCardServices()
    {
        return $this->_initialConfig->getData('default')[self::XML_PATH_GIFTCARD_SERVICES] ?? [];
    }

    /**
     * Retrieve all services list as an array
     *
     * Possible output:
     * 1) assoc array as <code> => <title>
     * 2) array of array('label' => <title>, 'value' => <code>)
     * 3) array of array(
     *                 array('value' => <code>, 'label' => <title>),
     *                 array('value' => array(
     *                     'value' => array(array(<code1> => <title1>, <code2> =>...),
     *                     'label' => <group name>
     *                 )),
     *                 array('value' => <code>, 'label' => <title>),
     *                 ...
     *             )
     *
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @param Store|null $store
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getGiftCardServiceList($sorted = true, $asLabelValue = false, $withGroups = false, $store = null)
    {
        $methods = [];
        $groups = [];
        $groupRelations = [];

        foreach ($this->getGiftCardServices() as $code => $data) {
            if (isset($data['title'])) {
                $methods[$code] = $data['title'];
            } else {
                $methods[$code] = $this->getServiceInstance($code)->getConfigData('title', $store);
            }
            if ($asLabelValue && $withGroups && isset($data['group'])) {
                $groupRelations[$code] = $data['group'];
            }
        }
        if ($asLabelValue && $withGroups) {
            $groups = $this->_serviceConfig->getGroups();
            foreach ($groups as $code => $title) {
                $methods[$code] = $title;
            }
        }
        if ($sorted) {
            asort($methods);
        }
        if ($asLabelValue) {
            $labelValues = [];
            foreach ($methods as $code => $title) {
                $labelValues[$code] = [];
            }
            foreach ($methods as $code => $title) {
                if (isset($groups[$code])) {
                    $labelValues[$code]['label'] = $title;
                } elseif (isset($groupRelations[$code])) {
                    unset($labelValues[$code]);
                    $labelValues[$groupRelations[$code]]['value'][$code] = ['value' => $code, 'label' => $title];
                } else {
                    $labelValues[$code] = ['value' => $code, 'label' => $title];
                }
            }
            return $labelValues;
        }

        return $methods;
    }

    /**
     * Check is native magento gift card account functionality allowed
     *
     * @return bool
     */
    public function isNativeGiftCardsAllowed()
    {
        if ($this->isActive()) {
            return $this->scopeConfig->isSetFlag(
                'digidirect_abstract_gift_card/settings/allow_native_gift_cards',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return true;
    }

    /**
     * Check is AbstractGiftCard extension enabled
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_abstract_gift_card/settings/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isAcceptOnlyForPaidOrders($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_abstract_gift_card/settings/accept_gift_cards_for_paid_orders',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
