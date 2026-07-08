<?php

namespace Digidirect\AbstractGiftCard\Model\Checks;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Magento\Quote\Model\Quote;

/**
 * Specification checks interface
 */
interface SpecificationInterface
{
    /**
     * Check whether service is applicable to quote
     *
     * @param ServiceInterface $service
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(ServiceInterface $service, Quote $quote);
}
