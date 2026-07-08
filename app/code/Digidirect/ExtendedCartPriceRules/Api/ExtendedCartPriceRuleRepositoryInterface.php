<?php
namespace Digidirect\ExtendedCartPriceRules\Api;

use Digidirect\ExtendedCartPriceRules\Api\Data\ExtendedCartPriceRuleInterface;
use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface ExtendedCartPriceRuleRepositoryInterface
{
    /**
     * @param ExtendedCartPriceRuleInterface $stock
     * @return static
     */
    public function save(ExtendedCartPriceRuleInterface $stock);

    /**
     * Load Stock data by given stockId and parameters
     *
     * @param int $id
     * @return ExtendedCartPriceRuleInterface
     */
    public function get($id);

    /**
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param ExtendedCartPriceRuleInterface $stock
     * @return bool
     */
    public function delete(ExtendedCartPriceRuleInterface $stock);
}
