<?php

namespace Digidirect\AbstractEntity\Model\AttributeSet;

use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Class UrlProcessor
 * @package Digidirect\AbstractEntity\Model\AttributeSet
 */
class UrlProcessor
{
    const URL_ENTITY_TYPE = 'set';
    const ROUTE_PATH = 'digidirect_abstractentity/set/view';
    const TARGET_PATH_PATTERN = self::ROUTE_PATH . '/id/%s';
    const REQUEST_PATH_PATTERN = '%s';

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
     * @param int $setId
     * @param string $urlKey
     * @return bool
     */
    public function processUrlRewrites($setId, $urlKey)
    {
        $urls = [];
        if (!$setId) {
            return false;
        }

        $targetPath = sprintf(self::TARGET_PATH_PATTERN, $setId);
        $stores = $this->storeManager->getStores();
        $posUrlKey = $urlKey;
        if (!$posUrlKey) {
            return false;
        }

        foreach ($stores as $store) {
            $requestPath = sprintf(self::REQUEST_PATH_PATTERN, $posUrlKey);
            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(self::URL_ENTITY_TYPE)
                ->setEntityId($setId)
                ->setRequestPath($requestPath)
                ->setTargetPath($targetPath)
                ->setStoreId($store->getId())
                ->setMetadata([
                    'posid' => $setId
                ]);
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
