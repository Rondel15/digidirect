<?php

namespace Digidirect\ProductOverlay\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for product overlay search results.
 * @api
 */
interface OverlaySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get overlays list.
     *
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface[]
     */
    public function getItems();

    /**
     * Set overlays list.
     *
     * @param \Digidirect\ProductOverlay\Api\Data\OverlayInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
