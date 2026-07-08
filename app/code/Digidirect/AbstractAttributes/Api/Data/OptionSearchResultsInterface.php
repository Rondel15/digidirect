<?php
namespace Digidirect\AbstractAttributes\Api\Data;

/**
 * Interface for option search results.
 * @api
 */
interface OptionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get list of options.
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface[]
     */
    public function getItems();

    /**
     * Set list of options.
     * @param \Digidirect\AbstractAttributes\Api\Data\OptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
