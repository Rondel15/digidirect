<?php

namespace Digidirect\Collect\Model\Plugin\Quote\ShippingMethodManagement;

use Digidirect\Collect\Model\Carrier\Collectcarrier;
use Magento\Quote\Model\Quote;

class FilterRates
{
    /**
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * FilterRates constructor.
     *
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_collectHelper = $collectHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $this->setQuote($this->_checkoutSession->getQuote());
        }
        return $this->_quote;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote(Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * FilterRates
     *
     * @param  [] $rates
     * @return []
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function filterRates($rates)
    {
        if ($this->_collectHelper->isSingleVariation()
            || $this->_collectHelper->isSingleCartVariation()) {
            return $rates;
        }

        $quoteId = $this->getQuote()->getId();
        $isDeliveryItems = $this->_collectHelper->isDeliveryItems($quoteId);
        if (!$isDeliveryItems && $this->_collectHelper->isCollectItems($quoteId)) {
            foreach ($rates as $key => $rate) {
                /** @var $rate \Magento\Quote\Model\Cart\ShippingMethod */
                if ((is_array($rate) && $key != Collectcarrier::COLLECT_CARRIER_CODE)
                    || (is_object($rate) && $rate->getMethodCode() != Collectcarrier::COLLECT_CARRIER_CODE)) {
                    unset($rates[$key]);
                }
            }
        } elseif ($isDeliveryItems) {
            foreach ($rates as $key => $rate) {
                /** @var $rate \Magento\Quote\Model\Cart\ShippingMethod */
                if ((is_array($rate) && $key == Collectcarrier::COLLECT_CARRIER_CODE)
                    || (is_object($rate) && $rate->getMethodCode() == Collectcarrier::COLLECT_CARRIER_CODE)) {
                    unset($rates[$key]);
                }
            }
        }

        return $rates;
    }
}
