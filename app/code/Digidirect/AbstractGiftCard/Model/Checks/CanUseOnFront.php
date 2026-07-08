<?php

namespace Digidirect\AbstractGiftCard\Model\Checks;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Magento\Quote\Model\Quote;

class CanUseOnFront implements SpecificationInterface
{
    /**
     * Check whether service is applicable to quote
     *
     * @param ServiceInterface $service
     * @param Quote $quote
     * @return bool
     */
    public function isApplicable(ServiceInterface $service, Quote $quote)
    {
        return $service->canUseOnFront();
    }
}
