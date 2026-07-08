<?php

namespace Digidirect\StoreLocator\Observer;

use Digidirect\StoreLocator\Helper\Config;
use Digidirect\StoreLocator\Model\EntitiesSorter;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;

class EntitesAfterLoad implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var EntitiesSorter
     */
    protected $entitiesSorter;

    /**
     * EntitesAfterLoad constructor.
     * @param Config $configHelper
     * @param EntitiesSorter $entitiesSorter
     */
    public function __construct(Config $configHelper, EntitiesSorter $entitiesSorter)
    {
        $this->configHelper = $configHelper;
        $this->entitiesSorter = $entitiesSorter;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $resultObject = $observer->getResultObject();
        if ($this->configHelper->isGroupSearchResultByParentEntity()) {
            if ($resultObject instanceof DataObject) {
                $sortedData = $this->entitiesSorter->sort($resultObject->getData());
                $resultObject->addData($sortedData);
            }
        }

        return $this;
    }
}
