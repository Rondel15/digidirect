<?php
namespace Digidirect\AbstractAttributes\Api\Data;

/**
 * Interface for AbstractAttribute search results.
 * @api
 */
interface AbstractAttributeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get abstract attributes list.
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface[]
     */
    public function getItems();

    /**
     * Set abstract attributes list.
     * @param \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
