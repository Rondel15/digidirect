<?php

namespace Digidirect\Digi\Model\Plugin\Checkout;

/**
 * Class DefaultConfigProvider
 * @package Digidirect\Digi\Model\Plugin\Checkout
 */
class DefaultConfigProvider
{
    /**
     * @var \Digidirect\Digi\Helper\Quote
     */
    protected $quoteHelper;

    /**
     * DefaultConfigProvider constructor.
     * @param \Digidirect\Digi\Helper\Quote $quoteHelper
     */
    public function __construct(
        \Digidirect\Digi\Helper\Quote $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        $result
    ) {
        if (isset($result['quoteData'])) {
            $result['quoteData']['is_quote_has_web_only'] = $this->quoteHelper->isQuoteHasWebOnlyProducts();
        }
        return $result;
    }
}
