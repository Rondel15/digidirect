<?php
namespace Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule;

use Digidirect\ExtendedCatalogPriceRule\Model\ExtendedCatalogRule;
use Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule as ExtendedCatalogRuleResource;

/**
 * Class Collection
 * @package Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule
 */
class Collection extends \Magento\CatalogRule\Model\ResourceModel\Rule\Collection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ExtendedCatalogRule::class, ExtendedCatalogRuleResource::class);
    }
}
