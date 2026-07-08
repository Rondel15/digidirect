<?php

namespace Digidirect\StoreLocator\Observer;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\Locator\Model\Locator;
use Digidirect\StoreLocator\Data\ProcessorConstants as PC;
use Digidirect\StoreLocator\Model\Frontend\Url;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Load correct urls
 */
class ProcessUrlKey implements ObserverInterface
{
    /**
     * @var Url
     */
    private $url;

    /**
     * ProcessUrlKey constructor.
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->modifyUrls($observer->getData(Locator::RESULT_OBJECT_EVENT));
    }

    /**
     * @param mixed $dataObject
     * @return ProcessUrlKey
     */
    private function modifyUrls($dataObject): self
    {
        if (!($dataObject instanceof DataObject)) {
            return $this;
        }

        $ids = [];
        $entities = $dataObject->getData();
        foreach ($entities as $entityName => $entityData) {
            if (!empty($entityData[PC::ITEMS])) {
                foreach ($entityData[PC::ITEMS] as $entity) {
                    if (!isset($entity['url_key'])) {
                        continue;
                    }
                    $entityId = $entity[AbstractEntityInterface::ENTITY_ID] ?? 0;
                    $ids[$entityId] = $entityId;
                }
            }
        }

        if (empty($ids)) {
            return $this;
        }

        $resultUrls = $this->url->processMultiple($ids);
        foreach ($entities as $entityName => $entityData) {
            if (!empty($entityData[PC::ITEMS])) {
                foreach ($entityData[PC::ITEMS] as $key => $entity) {
                    $id = $entity[AbstractEntityInterface::ENTITY_ID] ?? 0;
                    if (!isset($ids[$id])) {
                        continue;
                    }
                    $entities[$entityName][PC::ITEMS][$key]['url_key'] = $resultUrls[$id] ?? $entity['url_key'];
                }
            }
        }
        $dataObject->setData($entities);
        return $this;
    }
}
