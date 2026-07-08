<?php

namespace Digidirect\AbstractEntity\Model\Indexer;

use Digidirect\AbstractEntity\Model\Indexer\AbstractEntity\Action;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Digidirect\AbstractEntity\Helper\Config as ConfigHelper;

class AbstractEntity implements IndexerActionInterface, MviewActionInterface
{
    const INDEXER_ID = 'digidirect_abstractentity_index';

    /**
     * @var \Digidirect\AbstractEntity\Model\Indexer\AbstractEntity\Action
     */
    protected $action;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * Cms constructor.
     *
     * @param Action $action
     * @param IndexerRegistry $indexerRegistry
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Action $action,
        IndexerRegistry $indexerRegistry,
        ConfigHelper $configHelper
    ) {
        $this->action = $action;
        $this->configHelper = $configHelper;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute($ids)
    {
        $indexer = $this->getIndexer();
        if ($indexer->isInvalid()) {
            return;
        }
        $this->action->reindex($ids);
    }

    /**
     * Execute full indexation
     *
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }

    /**
     * @param AbstractEntityInterface $ae
     * @param  array $arguments
     * @return void
     */
    public function reindexRow(AbstractEntityInterface $ae, $arguments = [])
    {
        if (!$this->configHelper->isIndexTableEnableForEntity($ae->getAttributeSetId())) {
            return;
        }

        if ($this->isIndexerScheduled()) {
            $this->markIndexerAsInvalid();
            return;
        }

        $this->action->reindexEntityInfo($ae, $arguments);
    }

    /**
     * Mark Abstract Entity indexer as invalid
     *
     * @return void
     */
    public function markIndexerAsInvalid()
    {
        $this->getIndexer()->invalidate();
    }

    /**
     * Get indexer
     *
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    public function getIndexer()
    {
        return $this->indexerRegistry->get(static::INDEXER_ID);
    }

    /**
     * Check if indexer is on scheduled
     *
     * @return bool
     */
    public function isIndexerScheduled()
    {
        return $this->getIndexer()->isScheduled();
    }
}
