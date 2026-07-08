<?php
namespace Digidirect\SEO\Model;

use Digidirect\SEO\Api\SitemapExcludeRepositoryInterface;
use Digidirect\SEO\Api\Data;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Digidirect\SEO\Model\ResourceModel\SitemapExclude as ResourceSitemapExclude;
use Digidirect\SEO\Model\ResourceModel\SitemapExclude\CollectionFactory;
use Magento\Store\Model\Store;

/**
 * Class SitemapExcludeRepository
 * @package Digidirect\SEO\Model
 */
class SitemapExcludeRepository implements SitemapExcludeRepositoryInterface
{
    /**
     * @var ResourceSitemapExclude
     */
    protected $resource;

    /**
     * @var SitemapExcludeFactory
     */
    protected $sitemapExcludeFactory;

    /**
     * @var CollectionFactory
     */
    private $sitemapExcludeCollectionFactory;

    /**
     * SitemapExcludeRepository constructor.
     * @param ResourceSitemapExclude $resource
     * @param SitemapExcludeFactory $sitemapExcludeFactory
     * @param CollectionFactory $sitemapExcludeCollectionFactory
     */
    public function __construct(
        ResourceSitemapExclude $resource,
        SitemapExcludeFactory $sitemapExcludeFactory,
        CollectionFactory $sitemapExcludeCollectionFactory
    ) {
        $this->resource = $resource;
        $this->sitemapExcludeFactory = $sitemapExcludeFactory;
        $this->sitemapExcludeCollectionFactory = $sitemapExcludeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\SitemapExcludeInterface $sitemapExclude)
    {
        try {
            $this->resource->save($sitemapExclude);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $sitemapExclude;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sitemapExcludeId)
    {
        $sitemapExclude = $this->sitemapExcludeFactory->create();
        $this->resource->load($sitemapExclude, $sitemapExcludeId);
        if (!$sitemapExclude->getId()) {
            throw new NoSuchEntityException(__('Sitemap exclude with id "%1" does not exist.', $sitemapExcludeId));
        }
        return $sitemapExclude;
    }

    /**
     * {@inheritdoc}
     */
    public function getByItemId($itemType, $itemId, $storeId)
    {
        $sitemapExclude = $this->sitemapExcludeFactory->create();
        $this->resource->loadByItemId($sitemapExclude, $itemType, $itemId, $storeId);
        if (!$sitemapExclude->getId()) {
            throw new NoSuchEntityException(
                __('Sitemap exclude for "%1" with id="%2" on store_id=%3 does not exist.', $itemType, $itemId, $storeId)
            );
        }
        return $sitemapExclude;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded($itemType, $itemId, $storeId)
    {
        return $this->resource->isExcluded($itemType, $itemId, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function isDefaultSetting($itemType, $itemId, $storeId)
    {
        if ($storeId == Store::DEFAULT_STORE_ID) {
            return true;
        }
        return $this->resource->isDefaultSetting($itemType, $itemId, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getExcludedItemIds($itemType, $storeId)
    {
        $collection = $this->sitemapExcludeCollectionFactory->create();
        return $collection->getExcludedItemIds($itemType, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\SitemapExcludeInterface $sitemapExclude)
    {
        try {
            $this->resource->delete($sitemapExclude);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function processItemExclude($itemType, $itemId, $storeId, $shouldUseDefault, $shouldExclude)
    {
        $isExcluded = $this->isExcluded($itemType, $itemId, $storeId);
        if ($storeId == Store::DEFAULT_STORE_ID) {
            if ($shouldExclude && !$isExcluded) {
                $sitemapExclude = $this->sitemapExcludeFactory->create();
                $sitemapExclude->setItemType(SitemapExclude::ITEM_TYPE_CATEGORY)
                    ->setItemId($itemId)
                    ->setStoreId($storeId)
                    ->setStatus(SitemapExclude::STATUS_ENABLED);
                $this->save($sitemapExclude);
            } elseif (!$shouldExclude && $isExcluded) {
                $sitemapExclude = $this->getByItemId($itemType, $itemId, $storeId);
                $this->delete($sitemapExclude);
            }
            return $this;
        } else {
            try {
                $sitemapExclude = $this->getByItemId($itemType, $itemId, $storeId);
            } catch (NoSuchEntityException $e) {
                $sitemapExclude = $this->sitemapExcludeFactory->create();
            }
            if (!$shouldUseDefault) {
                $sitemapExclude->setItemType(SitemapExclude::ITEM_TYPE_CATEGORY)
                    ->setItemId($itemId)
                    ->setStoreId($storeId)
                    ->setStatus($shouldExclude ? SitemapExclude::STATUS_ENABLED : SitemapExclude::STATUS_DISABLED);
                $this->save($sitemapExclude);
            } elseif ($shouldUseDefault && $sitemapExclude->getId()) {
                $this->delete($sitemapExclude);
            }
        }

        return $this;
    }
}
