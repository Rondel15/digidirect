<?php

namespace Digidirect\ShippingAvailabilityCheckCollect\Plugin\Collect\Helper;

/**
 * Class Data
 * @package Digidirect\ShippingAvailabilityCheckCollect\Plugin\Collect\Helper
 */
class Data
{
    /**
     * @var bool
     */
    protected $shippingAvailabilityCheckFlag = false;

    /**
     * @var \Digidirect\Collect\Model\Plugin\Quote\ShippingMethodManagement\FilterRates
     */
    protected $filterRates;

    /**
     * Data constructor.
     * @param \Digidirect\Collect\Model\Plugin\Quote\ShippingMethodManagement\FilterRates $filterRates
     */
    public function __construct(
        \Digidirect\Collect\Model\Plugin\Quote\ShippingMethodManagement\FilterRates $filterRates
    ) {
        $this->filterRates = $filterRates;
    }

    /**
     * @param \Digidirect\Collect\Helper\Data $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAreaForActiveQuote(\Digidirect\Collect\Helper\Data $subject, $result)
    {
        if ($this->shippingAvailabilityCheckFlag) {
            $result = false;
        }
        return $result;
    }

    /**
     * @param \Digidirect\ShippingAvailabilityCheck\Model\QuoteManagement $subject
     * @param |Magento\Quote\Model\Quote $quote
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetExistedOrCreateNewQuote(
        \Digidirect\ShippingAvailabilityCheck\Model\QuoteManagement $subject,
        $quote
    ) {
        $this->shippingAvailabilityCheckFlag = true;
        $this->filterRates->setQuote($quote);
        return $quote;
    }
}
