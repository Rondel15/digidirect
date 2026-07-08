<?php
namespace Digidirect\AbstractAttributes\Model\UrlProcessor;

use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Option
 * @package Digidirect\AbstractAttributes\Model\UrlProcessor
 */
class Option extends ProcessorAbstract
{
    const URL_ENTITY_TYPE = 'abstract-attribute-option';
    const ROUTE_PATH = 'eaa/attribute_option/index';
    const TARGET_PATH_PATTERN = self::ROUTE_PATH . '/_aa/%s/_option/%s';
    const REQUEST_PATH_PATTERN = '%s/%s';

    /**
     * Generate url rewrites
     * @param OptionInterface $option
     * @return true If urls were replaced
     */
    public function processUrlRewrites(OptionInterface $option)
    {
        $urls = [];
        $attrId = $option->getAttributeId();
        $optionId = $option->getOptionId();
        if (!$optionId || !$attrId) {
            return false;
        }

        $targetPath = sprintf(self::TARGET_PATH_PATTERN, $attrId, $optionId);
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $aAttribute = $this->abstractAttributeRepository->getByAttributeId($attrId, $store->getId());
            $option = $this->optionRepository->getByOptionId($optionId, $store->getId());
            $attrUrlKey = $aAttribute->getUrlKey();
            $optUrlKey = $option->getUrlKey();
            if (!$optUrlKey || !$attrUrlKey) {
                continue;
            }

            $urlSuffix = $this->urlHelper->getUrlSuffix($store->getId());
            $requestPath = sprintf(self::REQUEST_PATH_PATTERN, $attrUrlKey, $optUrlKey) . $urlSuffix;
            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(self::URL_ENTITY_TYPE)
                ->setEntityId($optionId)
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
     * @param OptionInterface $option
     * @return true If urls were replaced
     */
    public function deleteUrlRewrites(OptionInterface $option)
    {
        $optionId = $option->getOptionId();
        if (!$optionId) {
            return false;
        }

        $this->storage->deleteByData([
            'entity_type' => self::URL_ENTITY_TYPE,
            'entity_id'   => $optionId
        ]);

        return true;
    }
}
