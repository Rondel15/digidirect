<?php
namespace Digidirect\ExtendedCatalogPriceRule\Api;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;

/**
 * Interface ExtendedCatalogRuleRepositoryInterface
 * @package Digidirect\ExtendedCatalogPriceRule\Api
 */
interface ExtendedCatalogRuleRepositoryInterface
{
    /**
     * @param int $id Extended Catalog Rule entity ID
     * @return ExtendedCatalogRuleInterface
     */
    public function get($id);

    /**
     * @param ExtendedCatalogRuleInterface $rule
     * @return ExtendedCatalogRuleInterface
     */
    public function save(ExtendedCatalogRuleInterface $rule);

    /**
     * @param ExtendedCatalogRuleInterface $rule
     * @return bool
     */
    public function delete(ExtendedCatalogRuleInterface $rule);
}
