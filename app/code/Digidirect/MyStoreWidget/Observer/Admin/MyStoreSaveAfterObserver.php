<?php
namespace Digidirect\MyStoreWidget\Observer\Admin;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\MyStoreWidget\Model\Indexer\Store as StoreIndexer;
use Digidirect\MyStoreWidget\Helper\Config as Helper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Indexer\IndexerRegistry;

class MyStoreSaveAfterObserver extends ReindexStoreDataObserver
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * MyStoreSaveAfterObserver constructor.
     * @param StoreIndexer $storeIndexer
     * @param Helper $helper
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        StoreIndexer $storeIndexer,
        Helper $helper,
        IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($storeIndexer, $indexerRegistry);
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var AbstractEntityInterface $entity */
        $entity = $observer->getEntity();
        if (in_array($entity->getAttributeSetId(), $this->helper->getEntities())) {
            $this->indexer->invalidate();
            if (!$this->indexer->isScheduled()) {
                parent::execute($observer);
            }
        }
        return $this;
    }
}
