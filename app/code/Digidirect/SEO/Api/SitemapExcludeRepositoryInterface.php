<?php
namespace Digidirect\SEO\Api;

/**
 * Sitemap exclude CRUD interface.
 * @api
 */
interface SitemapExcludeRepositoryInterface
{
    /**
     * Save sitemap exclude.
     *
     * @param Data\SitemapExcludeInterface $sitemapExclude
     * @return \Digidirect\SEO\Api\Data\SitemapExcludeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\SitemapExcludeInterface $sitemapExclude);

    /**
     * Retrieve sitemap exclude.
     *
     * @param int $sitemapExcludeId
     * @return \Digidirect\SEO\Api\Data\SitemapExcludeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($sitemapExcludeId);

    /**
     * Retrieve sitemap exclude for provided item item on provided store.
     *
     * @param string $itemType
     * @param int $itemId
     * @param int $storeId
     * @return \Digidirect\SEO\Api\Data\SitemapExcludeInterface
     */
    public function getByItemId($itemType, $itemId, $storeId);

    /**
     * Check if sitemap exclude exists for provided item on provided store.
     *
     * @param string $itemType
     * @param int $itemId
     * @param int $storeId
     * @return boolean
     */
    public function isExcluded($itemType, $itemId, $storeId);

    /**
     * Check if default store setting for sitemap exclude should be used.
     *
     * @param string $itemType
     * @param int $itemId
     * @param int $storeId
     * @return boolean
     */
    public function isDefaultSetting($itemType, $itemId, $storeId);

    /**
     * Retrieve array of excluded item IDs of provided type on provided store.
     *
     * @param string $itemType
     * @param int $storeId
     * @return []
     */
    public function getExcludedItemIds($itemType, $storeId);

    /**
     * Delete sitemap exclude.
     *
     * @param Data\SitemapExcludeInterface $sitemapExclude
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\SitemapExcludeInterface $sitemapExclude);

    /**
     * Process sitemap exclude for provided item on provided store
     *
     * @param string $itemType
     * @param int $itemId
     * @param int $storeId
     * @param bool $shouldUseDefault
     * @param bool $shouldExclude
     * @return SitemapExcludeRepositoryInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processItemExclude($itemType, $itemId, $storeId, $shouldUseDefault, $shouldExclude);
}
