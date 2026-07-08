<?php

namespace Digidirect\StoreLocator\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Digidirect\StoreLocator\Model\FulltextAttributes;

/**
 * Class CreateIndexTableBefore
 */
class CreateIndexTableBefore implements ObserverInterface
{
    /**
     * @var FulltextAttributes
     */
    protected $fulltextAttributes;

    /**
     * CreateIndexTableBefore constructor.
     * @param FulltextAttributes $fulltextAttributes
     */
    public function __construct(
        FulltextAttributes $fulltextAttributes
    ) {
        $this->fulltextAttributes = $fulltextAttributes;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        $this->fulltextAttributes->prepareFulltextAttributes($object);

        return $this;
    }
}
