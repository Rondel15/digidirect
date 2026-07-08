<?php
namespace Digidirect\AbstractEntity\Api\Data;

interface AbstractEntitySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get AbstractEntity list.
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface[]
     */
    public function getItems();

    /**
     * Set identifier list.
     * @param \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
