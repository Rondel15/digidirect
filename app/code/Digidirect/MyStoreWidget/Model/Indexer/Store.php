<?php
namespace Digidirect\MyStoreWidget\Model\Indexer;

use Digidirect\MyStoreWidget\Model\Indexer\Store\Action;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\IndexerRegistry;

class Store implements IndexerActionInterface, MviewActionInterface
{
    const INDEXER_ID = 'digidirect_mystorewidget_store';

    /**
     * @var \Digidirect\MyStoreWidget\Model\Indexer\Store\Action
     */
    protected $action;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * Cms constructor.
     * @param Action $action
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Action $action,
        IndexerRegistry $indexerRegistry
    ) {
        $this->action = $action;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $indexer = $this->indexerRegistry->get(self::INDEXER_ID);
        if ($indexer->isInvalid()) {
            return;
        }
        $this->action->reindex($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->action->reindex();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
