<?php
namespace Digidirect\AbstractAttributes\Model\UrlProcessor;

use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class Attribute
 * @package Digidirect\AbstractAttributes\Model\UrlProcessor
 */
class Attribute extends ProcessorAbstract
{
    const URL_ENTITY_TYPE = 'abstract-attribute';
    const ROUTE_PATH = 'eaa/attribute/index';
    const TARGET_PATH_PATTERN = self::ROUTE_PATH . '/_aa/%s';
    const REQUEST_PATH_PATTERN = '%s';

    /**
     * Generate url rewrites
     * @param AbstractAttributeInterface $attribute
     * @return true If urls were replaced
     */
    public function processUrlRewrites(AbstractAttributeInterface $attribute)
    {
        $urls = [];
        $attrId = $attribute->getAttributeId();
        if (!$attrId) {
            return false;
        }

        $targetPath = sprintf(self::TARGET_PATH_PATTERN, $attrId);
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $attribute = $this->abstractAttributeRepository->getByAttributeId($attrId, $store->getId());
            $attrUrlKey = $attribute->getUrlKey();
            if (!$attrUrlKey) {
                continue;
            }

            $urlSuffix = $this->urlHelper->getUrlSuffix($store->getId());
            $requestPath = sprintf(self::REQUEST_PATH_PATTERN, $attrUrlKey) . $urlSuffix;

            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(self::URL_ENTITY_TYPE)
                ->setEntityId($attrId)
                ->setRequestPath($requestPath)
                ->setTargetPath($targetPath)
                ->setStoreId($store->getId())
                ->setMetadata([
                    'aid' => $attrId
                ]);
        }

        $this->storage->replace($urls);
        return true;
    }

    /**
     * Delete url rewrites
     * @param AbstractAttributeInterface $attribute
     * @return true If urls were replaced
     */
    public function deleteUrlRewrites(AbstractAttributeInterface $attribute)
    {
        $attrId = $attribute->getAttributeId();
        if (!$attrId) {
            return false;
        }

        $options = $this->optionRepository->getAttributeOptions($attrId);
        foreach ($options as $option) {
            $option->deleteUrlRewrites();
        }

        $this->storage->deleteByData([
            'entity_type' => self::URL_ENTITY_TYPE,
            'entity_id'   => $attrId
        ]);

        return true;
    }
}
