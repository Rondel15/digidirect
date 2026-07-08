<?php

namespace Digidirect\AbstractGiftCard\Model\Checks;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Magento\Quote\Model\Quote;

class TotalMinMax implements SpecificationInterface
{
    /**
     * Config value key for min order total
     */
    const MIN_ORDER_TOTAL = 'min_order_total';

    /**
     * Config value key for max order total
     */
    const MAX_ORDER_TOTAL = 'max_order_total';

    /**
     * Check whether service is applicable to quote
     *
     * @param ServiceInterface $service
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(ServiceInterface $service, Quote $quote)
    {
        $total = $quote->getBaseGrandTotal();
        $minTotal = $service->getConfigData(self::MIN_ORDER_TOTAL);
        $maxTotal = $service->getConfigData(self::MAX_ORDER_TOTAL);
        if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
            return false;
        }
        return true;
    }
}
