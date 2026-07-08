<?php

namespace Digidirect\StoreLocator\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

class UrlProcessor
{
    const URL_ENTITY_TYPE = 'storelocator';
    const ROUTE_PATH = 'digidirect_storelocator/index/index';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository;

    /**
     * ProcessorAbstract constructor.
     * @param StoreManagerInterface $storeManager
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param StorageInterface $storage
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlRewriteFactory $urlRewriteFactory,
        StorageInterface $storage
    ) {
        $this->storeManager = $storeManager;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storage = $storage;
    }

    /**
     * @param string $urlKey
     * @return bool
     */
    public function processUrlRewrites($urlKey)
    {
        $urls = [];
        $targetPath = self::ROUTE_PATH;
        $stores = $this->storeManager->getStores();
        if (!$urlKey) {
            return false;
        }

        foreach ($stores as $store) {
            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(self::URL_ENTITY_TYPE)
                ->setRequestPath($urlKey)
                ->setTargetPath($targetPath)
                ->setStoreId($store->getId());
        }

        $this->storage->replace($urls);

        return true;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeSetInterface $set
     * @return bool
     */
    public function deleteUrlRewrites(\Magento\Eav\Api\Data\AttributeSetInterface $set)
    {
        $setId = $set->getId();
        if (!$setId) {
            return false;
        }

        $this->storage->deleteByData([
            'entity_type' => self::URL_ENTITY_TYPE,
            'entity_id'   => $setId
        ]);

        return true;
    }
}
