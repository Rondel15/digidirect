<?php

namespace Digidirect\StoreLocator\Plugin\Sitemap\Model;

use Magento\Sitemap\Helper\Data as SitemapHelper;
use Magento\Framework\DataObject;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;
use \Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Sitemap
 * @package Digidirect\StoreLocator\Plugin\Sitemap\Model
 */
class Sitemap
{
    /**
     * @var SitemapHelper
     */
    protected $sitemapHelper;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * Sitemap constructor.
     * @param SitemapHelper $sitemapHelper
     * @param ConfigHelper $configHelper
     * @param DateTime $date
     */
    public function __construct(
        SitemapHelper $sitemapHelper,
        ConfigHelper $configHelper,
        DateTime $date
    ) {
        $this->sitemapHelper = $sitemapHelper;
        $this->configHelper = $configHelper;
        $this->date = $date;
    }

    /**
     * @param \Digidirect\Utilities\Model\Sitemap $sitemap
     * @return void
     */
    public function beforeGenerateXml(\Digidirect\Utilities\Model\Sitemap $sitemap)
    {
        $sitemap->addSiteMapItem(new DataObject([
            'changefreq' => $this->sitemapHelper->getCategoryChangefreq($sitemap->getStoreId()),
            'priority' => $this->sitemapHelper->getCategoryPriority($sitemap->getStoreId()),
            'collection' => [new DataObject([
                'url' => $this->configHelper->getPageUrl(),
                'updated_at' => $this->date->date()
            ])]
        ]));
    }
}
