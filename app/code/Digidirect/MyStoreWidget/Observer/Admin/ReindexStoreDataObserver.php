<?php
namespace Digidirect\MyStoreWidget\Observer\Admin;

use Digidirect\MyStoreWidget\Model\Indexer\Store as StoreIndexer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\IndexerInterface;

class ReindexStoreDataObserver implements ObserverInterface
{
    /**
     * @var StoreIndexer
     */
    protected $storeIndexer;

    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * @param StoreIndexer $storeIndexer
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        StoreIndexer $storeIndexer,
        IndexerRegistry $indexerRegistry
    ) {
        $this->storeIndexer = $storeIndexer;
        $this->indexer = $indexerRegistry->get(StoreIndexer::INDEXER_ID);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $this->indexer->reindexAll();
        return $this;
    }
}
