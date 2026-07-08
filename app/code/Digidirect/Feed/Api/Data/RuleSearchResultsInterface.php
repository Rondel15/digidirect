<?php

namespace Digidirect\Feed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for feed rules search results.
 * @api
 */
interface RuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get rules list.
     *
     * @return RuleInterface[]
     */
    public function getItems();

    /**
     * Set rules list.
     *
     * @param RuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
