<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Checkout\Provider;

/**
 * Class CheckoutConfigProvider
 * @package Digidirect\ExtendedCartPriceRules\Model\Checkout\Provider
 */
class CheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const PAYMENT_LIMITED_BY_RULES = 'paymentLimitedByRules';
    const EXTEND_RULES_DATA = 'extendRulesData';

    /**
     * @var \Digidirect\ExtendedCartPriceRules\Helper\Data
     */
    protected $_helper;

    /**
     * CheckoutConfigProvider constructor.
     * @param \Digidirect\ExtendedCartPriceRules\Helper\Data $_helper
     */
    public function __construct(
        \Digidirect\ExtendedCartPriceRules\Helper\Data $_helper
    ) {
        $this->_helper = $_helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            self::PAYMENT_LIMITED_BY_RULES => $this->getAvailableMethods(),
            self::EXTEND_RULES_DATA => $this->getExtendRulesData()
            ];
    }

    /**
     * @return array
     */
    public function getAvailableMethods()
    {
        return $this->_helper->getAvailableMethods();
    }

    /**
     * Get extend rules data
     *
     * @return array
     */
    public function getExtendRulesData()
    {
        return $this->_helper->getExtendRulesData();
    }
}
