<?php
namespace Digidirect\ExtendedCatalogPriceRule\Api;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;

/**
 * Interface ExtendedCatalogRuleManagementInterface
 * @package Digidirect\ExtendedCatalogPriceRule\Api
 */
interface ExtendedCatalogRuleManagementInterface
{
    /**
     * @param int $ruleId
     * @return ExtendedCatalogRuleInterface
     */
    public function loadByCatalogRuleId($ruleId);

    /**
     * @param int $ruleId
     * @return bool
     */
    public function deleteByCatalogRuleId($ruleId);

    /**
     * @param array $productIds
     * @param string $actionCode
     * @param int|null $websiteId
     * @param int|null $customerGroupId
     * @param string|null $date
     * @return array
     */
    public function getExtendedRulesDataByProductIds(
        array $productIds,
        $actionCode,
        $websiteId = null,
        $customerGroupId = null,
        $date = null
    );

    /**
     * @param array $productIds
     * @param string $actionCode
     * @param int|null $websiteId
     * @param int|null $customerGroupId
     * @param string|null $date
     * @param bool $formatActionAmount
     * @return array
     */
    public function getExtendedRulesGroupedByProductIds(
        array $productIds,
        $actionCode,
        $websiteId = null,
        $customerGroupId = null,
        $date = null,
        $formatActionAmount = false
    );
}
