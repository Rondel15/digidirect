<?php

namespace Digidirect\AbstractGiftCard\Model\Checks;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Magento\Quote\Model\Quote;

class CanUseInternal implements SpecificationInterface
{
    /**
     * Check whether service is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param ServiceInterface $service
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(ServiceInterface $service, Quote $quote)
    {
        return $service->canUseInternal();
    }
}
