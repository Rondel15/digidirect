<?php

namespace Digidirect\ExtendedCatalogPriceRule\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Config
 */
class Config extends AbstractHelper
{
    const XML_PATH_DYNAMIC_PRICE_CONFIG = 'digidirect_extendedcatalogpricerules/general/add_dynamic_price';

    /**
     * @return bool
     */
    public function isSetDynamicPrice()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DYNAMIC_PRICE_CONFIG
        );
    }
}
