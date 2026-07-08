<?php
namespace Digidirect\Utilities\Model;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * Add a new sitemap item
     * @param \Magento\Framework\DataObject $item
     * @return $this
     */
    public function addSiteMapItem(\Magento\Framework\DataObject $item)
    {
        $this->_sitemapItems[] = $item;
        return $this;
    }
}
