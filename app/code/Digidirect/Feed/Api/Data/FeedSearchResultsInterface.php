<?php

namespace Digidirect\Feed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for feeds search results.
 * @api
 */
interface FeedSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get feeds list.
     *
     * @return FeedInterface[]
     */
    public function getItems();

    /**
     * Set feeds list.
     *
     * @param FeedInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
