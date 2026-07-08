<?php

namespace Digidirect\AbstractGiftCard\Model\Checks;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Magento\Quote\Model\Quote;
use Digidirect\AbstractGiftCard\Model\Checks\CanUseForCountry\CountryProvider;

class CanUseForCountry implements SpecificationInterface
{
    /**
     * @var CountryProvider
     */
    protected $_countryProvider;

    /**
     * @param CountryProvider $countryProvider
     */
    public function __construct(CountryProvider $countryProvider)
    {
        $this->_countryProvider = $countryProvider;
    }

    /**
     * Check whether service is applicable to quote
     * @param ServiceInterface $service
     * @param Quote $quote
     * @return bool
     */
    public function isApplicable(ServiceInterface $service, Quote $quote)
    {
        return $service->canUseForCountry($this->_countryProvider->getCountry($quote));
    }
}
