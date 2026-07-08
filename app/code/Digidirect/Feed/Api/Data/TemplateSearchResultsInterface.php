<?php

namespace Digidirect\Feed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for feed templates search results.
 * @api
 */
interface TemplateSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get templates list.
     *
     * @return TemplateInterface[]
     */
    public function getItems();

    /**
     * Set templates list.
     *
     * @param TemplateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
