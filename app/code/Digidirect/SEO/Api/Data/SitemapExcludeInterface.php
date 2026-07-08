<?php
namespace Digidirect\SEO\Api\Data;

/**
 * Interface SitemapExcludeInterface
 * @api
 */
interface SitemapExcludeInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const EXCLUDE_ID    = 'id';
    const ITEM_TYPE     = 'item_type';
    const ITEM_ID       = 'item_id';
    const STORE_ID      = 'store_id';
    const STATUS        = 'status';
    /**#@-*/

    /**
     * Get exclude id
     *
     * @return int
     */
    public function getExcludeId();

    /**
     * Set exclude id
     *
     * @param int $excludeId
     * @return SitemapExcludeInterface
     */
    public function setExcludeId($excludeId);

    /**
     * Get item type
     *
     * @return string
     */
    public function getItemType();

    /**
     * Set item type
     *
     * @param string $itemType
     * @return SitemapExcludeInterface
     */
    public function setItemType($itemType);

    /**
     * Get item id
     *
     * @return int
     */
    public function getItemId();

    /**
     * Set item id
     *
     * @param int $itemId
     * @return SitemapExcludeInterface
     */
    public function setItemId($itemId);

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return SitemapExcludeInterface
     */
    public function setStoreId($storeId);

    /**
     * Get status
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return SitemapExcludeInterface
     */
    public function setStatus($status);
}
