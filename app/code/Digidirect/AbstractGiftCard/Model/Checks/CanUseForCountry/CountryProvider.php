<?php

namespace Digidirect\AbstractGiftCard\Model\Checks\CanUseForCountry;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Quote\Model\Quote;

class CountryProvider
{
    /**
     * @var DirectoryHelper
     */
    protected $_directoryHelper;

    /**
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(DirectoryHelper $directoryHelper)
    {
        $this->_directoryHelper = $directoryHelper;
    }

    /**
     * Get service country
     *
     * @param Quote $quote
     *
     * @return string
     */
    public function getCountry(Quote $quote)
    {
        /** @var string $country */
        $country = $quote->getBillingAddress()->getCountry() ? :
            $quote->getShippingAddress()->getCountry();

        if (!$country) {
            $country = $this->_directoryHelper->getDefaultCountry();
        }

        return $country;
    }
}
