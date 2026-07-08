<?php
namespace Digidirect\SEO\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for sitemap exclude search results.
 * @api
 */
interface SitemapExcludeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list.
     *
     * @return \Digidirect\SEO\Api\Data\SitemapExcludeInterface[]
     */
    public function getItems();

    /**
     * Set list.
     *
     * @param \Digidirect\SEO\Api\Data\SitemapExcludeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
