<?php
namespace Digidirect\AbstractAttributes\Plugin\Sitemap\Model;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Helper\Url as UrlHelper;
use Magento\Sitemap\Helper\Data as SitemapHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;

class Sitemap
{
    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $abstractAttributeRepository;

    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var SitemapHelper
     */
    protected $sitemapHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Sitemap constructor.
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     * @param OptionRepositoryInterface $optionRepository
     * @param UrlHelper $urlHelper
     * @param SitemapHelper $sitemapHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AbstractAttributeRepositoryInterface $abstractAttributeRepository,
        OptionRepositoryInterface $optionRepository,
        UrlHelper $urlHelper,
        SitemapHelper $sitemapHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        $this->optionRepository = $optionRepository;
        $this->urlHelper = $urlHelper;
        $this->sitemapHelper = $sitemapHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param \Digidirect\Utilities\Model\Sitemap $sitemap
     * @return void
     */
    public function beforeGenerateXml(
        \Digidirect\Utilities\Model\Sitemap $sitemap
    ) {
        $abstractAttributes = $this->abstractAttributeRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter(AbstractAttributeInterface::LISTING_ENABLED, 1)
                ->addFilter(AbstractAttributeInterface::STORE_ID, $sitemap->getStoreId())
                ->create()
        )->getItems();

        $options = $this->optionRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter(OptionInterface::STATUS, OptionInterface::STATUS_ENABLED)
                ->addFilter(OptionInterface::STORE_ID, $sitemap->getStoreId())
                ->create()
        )->getItems();

        $this->addSitemapItem($sitemap, $abstractAttributes);
        $this->addSitemapItem($sitemap, $options);
    }

    /**
     * @param \Digidirect\Utilities\Model\Sitemap $sitemap
     * @param array $collection
     * @return void
     */
    protected function addSitemapItem(\Digidirect\Utilities\Model\Sitemap $sitemap, $collection)
    {
        $sitemap->addSiteMapItem(new DataObject([
            'changefreq' => $this->sitemapHelper->getCategoryChangefreq($sitemap->getStoreId()),
            'priority' => $this->sitemapHelper->getCategoryPriority($sitemap->getStoreId()),
            'collection' => $this->prepareCollection($collection),
        ]));
    }

    /**
     * @param array $collection
     * @return array
     */
    protected function prepareCollection(array $collection)
    {
        $items = [];
        foreach ($collection as $item) {
            /** @var AbstractAttributeInterface|OptionInterface $item */
            $items[] = new DataObject([
                'url' => $item->getUrl(['_request_path_only' => 1]),
            ]);
        }
        return $items;
    }
}
