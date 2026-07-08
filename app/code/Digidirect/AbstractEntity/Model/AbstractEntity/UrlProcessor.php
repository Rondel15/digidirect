<?php

namespace Digidirect\AbstractEntity\Model\AbstractEntity;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Class UrlProcessor
 *
 * @package Digidirect\AbstractEntity\Model\AbstractEntity
 */
class UrlProcessor
{
    const URL_ENTITY_TYPE = 'abstractentity';
    const ROUTE_PATH = 'digidirect_abstractentity/abstractentity/view';
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
     * @var AbstractEntityRepository
     */
    protected $abstractEntityRepository;

    /**
     * ProcessorAbstract constructor.
     * @param StoreManagerInterface $storeManager
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param StorageInterface $storage
     * @param AbstractEntityRepository $abstractEntityRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlRewriteFactory $urlRewriteFactory,
        StorageInterface $storage,
        AbstractEntityRepository $abstractEntityRepository
    ) {
        $this->storeManager = $storeManager;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storage = $storage;
        $this->abstractEntityRepository = $abstractEntityRepository;
    }

    /**
     * Generate url rewrites
     *
     * @param AbstractEntityInterface $abstractEntity
     * @return true If urls were replaced
     */
    public function processUrlRewrites(AbstractEntityInterface $abstractEntity)
    {
        $urls = [];
        $aeId = $abstractEntity->getId();
        if (!$aeId) {
            return false;
        }

        $targetPath = sprintf(self::TARGET_PATH_PATTERN, $aeId);
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $abstractEntity = $this->abstractEntityRepository->getById($aeId, $store->getId());
            $posUrlKey = $abstractEntity->getUrlKey();
            if (!$posUrlKey) {
                continue;
            }

            $requestPath = sprintf(self::REQUEST_PATH_PATTERN, $posUrlKey);
            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(self::URL_ENTITY_TYPE)
                ->setEntityId($aeId)
                ->setRequestPath($requestPath)
                ->setTargetPath($targetPath)
                ->setStoreId($store->getId())
                ->setMetadata([
                    'posid' => $aeId
                ]);
        }

        $this->storage->replace($urls);
        return true;
    }

    /**
     * Delete url rewrites
     *
     * @param AbstractEntityInterface $abstractEntity
     * @return true If urls were replaced
     */
    public function deleteUrlRewrites(AbstractEntityInterface $abstractEntity)
    {
        $aeId = $abstractEntity->getId();
        if (!$aeId) {
            return false;
        }

        $this->storage->deleteByData([
            'entity_type' => self::URL_ENTITY_TYPE,
            'entity_id'   => $aeId
        ]);

        return true;
    }
}
