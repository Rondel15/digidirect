<?php

namespace Digidirect\StoreLocator\Model\Frontend;

use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * @api
 * Introduced to have correct urls for indexed entities
 */
class Url
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var null|int
     */
    private $storeId = null;

    /**
     * @var array
     */
    private $urlByEntityIdAndStoreId = [];

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var UrlModifier
     */
    private $urlModifier;

    /**
     * Url constructor.
     * @param StorageInterface $storage
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     * @param UrlModifier $urlModifier
     */
    public function __construct(
        StorageInterface $storage,
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        UrlModifier $urlModifier
    ) {
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->storage = $storage;
        $this->urlModifier = $urlModifier;
    }

    /**
     * @param int $entityId
     * @return string
     */
    public function process(int $entityId): string
    {
        if (!$this->isUrlPresented($entityId)) {
            $this->findMultiple([$entityId]);
        }
        return $this->getUrl($entityId);
    }

    /**
     * @param array $entityIds
     * @return array
     */
    public function processMultiple(array $entityIds): array
    {
        $notPresentedEntities = [];
        $presentedEntities = [];
        foreach ($entityIds as $entityId) {
            if (!$this->isUrlPresented($entityId)) {
                $notPresentedEntities[] = $entityId;
            } else {
                $presentedEntities[$entityId] = $this->getUrl($entityId);
            }
        }

        if (!empty($notPresentedEntities)) {
            $this->findMultiple($entityIds);
            foreach ($notPresentedEntities as $notPresentedEntity) {
                $presentedEntities[$notPresentedEntity] = $this->makeNot404(
                    $notPresentedEntity,
                    $this->process($notPresentedEntity)
                );
            }
        }

        return $presentedEntities;
    }

    /**
     * @param int $entityId
     * @param string $url
     * @return string
     */
    private function makeNot404(int $entityId, string $url): string
    {
        if (empty($url)) {
            $url = $this->url->getUrl('digidirect_abstractentity/abstractentity/view/', ['id' => $entityId]);
        }
        return $this->urlModifier->modify($url);
    }


    /**
     * @param array $entityIds
     * @return Url
     */
    private function findMultiple(array $entityIds): self
    {
        if (!empty($entityIds)) {
            $urls = $this->storage->findAllByData(
                [
                    UrlRewrite::ENTITY_TYPE => ['abstractentity'],
                    UrlRewrite::STORE_ID => [$this->getStoreId()],
                    UrlRewrite::ENTITY_ID => [$entityIds]
                ]
            );

            foreach ($urls as $urlRewrite) {
                $this->saveUrl((int)$urlRewrite->getEntityId(), $urlRewrite->getRequestPath());
            }
        }

        return $this;
    }

    /**
     * @param int $entityId
     * @return bool
     */
    private function isUrlPresented(int $entityId): bool
    {
        return !empty($this->getUrl($entityId));
    }

    /**
     * @param int $entityId
     * @param string $url
     * @return Url
     */
    private function saveUrl(int $entityId, string $url): Url
    {
        $url = empty($url) ?: $this->url->getUrl('', ['_direct' => $url]);
        $this->urlByEntityIdAndStoreId[$this->getCacheKey($entityId)] = $url;
        return $this;
    }

    /**
     * @param int $entityId
     * @return string
     */
    private function getUrl(int $entityId): string
    {
        return $this->urlByEntityIdAndStoreId[$this->getCacheKey($entityId)] ?? '';
    }

    /**
     * @param int $entityId
     * @return string
     */
    private function getCacheKey(int $entityId): string
    {
        return $this->getStoreId() . '_' . $entityId;
    }

    /**
     * @return int
     */
    private function getStoreId(): int
    {
        if (null === $this->storeId) {
            $this->storeId = (int)$this->storeManager->getStore()->getId();
        }

        return $this->storeId;
    }
}
