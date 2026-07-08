<?php
namespace Digidirect\AI\Api\Data;

/**
 * Interface ScheduleSearchResultInterface
 *
 * @package Digidirect\AI\Api\Data
 * @api
 */
interface ScheduleSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Digidirect\AI\Api\Data\ScheduleInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Digidirect\AI\Api\Data\ScheduleInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
