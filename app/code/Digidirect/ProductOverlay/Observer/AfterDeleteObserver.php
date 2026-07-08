<?php

namespace Digidirect\ProductOverlay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AfterDeleteObserver
 * @package Digidirect\ProductOverlay\Observer
 */
class AfterDeleteObserver implements ObserverInterface
{
    /**
     * @var \Digidirect\ProductOverlay\Model\Overlay\Product\AttributeManager
     */
    protected $attributeManager;

    /**
     * AfterDeleteObserver constructor.
     * @param \Digidirect\ProductOverlay\Model\Overlay\Product\AttributeManager $attributeManager
     */
    public function __construct(\Digidirect\ProductOverlay\Model\Overlay\Product\AttributeManager $attributeManager)
    {
        $this->attributeManager = $attributeManager;
    }

    /**
     * Remove product attribute value if overlay was deleted
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $overlay = $observer->getEvent()->getObject();
        $this->attributeManager->clearProductAttributeValue($overlay);
        return $this;
    }
}
