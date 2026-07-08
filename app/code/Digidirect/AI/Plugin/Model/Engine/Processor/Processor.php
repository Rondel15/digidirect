<?php

namespace Digidirect\AI\Plugin\Model\Engine\Processor;

use Digidirect\AI\Model\Engine\Processor\ProcessorAbstract;
use Digidirect\AI\Model\Integrations\Config\Data;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mview\View\CollectionFactory;
use Magento\Framework\Mview\View\StateInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Indexer\StateInterface as IndexerStateInterface;

class Processor
{
    const INDEXER_STATUS_LOCKED = 'locked';

    /**
     * @var Data
     */
    protected $dataConfig;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $viewsFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Processor constructor.
     *
     * @param Data $dataConfig
     * @param CacheManager $cacheManager
     * @param IndexerRegistry $indexerRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $viewsFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $dataConfig,
        CacheManager $cacheManager,
        IndexerRegistry $indexerRegistry,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $viewsFactory,
        LoggerInterface $logger
    ) {
        $this->dataConfig = $dataConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->cacheManager = $cacheManager;
        $this->scopeConfig = $scopeConfig;
        $this->viewsFactory = $viewsFactory;
        $this->logger = $logger;
    }

    /**
     * We lock reindex to avoid deadlock exception and the invalidate it
     *
     * @param ProcessorAbstract $processor
     * @param \Closure $proceed
     * @return mixed result
     */
    public function aroundProcess(
        ProcessorAbstract $processor,
        \Closure $proceed
    ) {
        $processCode = $processor->getProcessCode();
        $integration = $this->dataConfig->getIntegrationByName($processCode);
        try {
            $this->suspendMviewIndexers($integration);
            $this->lockIndexers($integration);
            $result = $proceed();
        } finally {
            $this->runMviewIndexers($integration);
            $this->invalidateIndexers($integration);
            $this->flushCache($integration);
        }

        return $result;
    }

    /**
     * @param array $integration
     * @return void
     */
    protected function invalidateIndexers($integration)
    {
        $indexers = $integration['indexers'] ?? [];

        foreach ($indexers as $indexerId) {
            $indexer = $this->getIndexer($indexerId);
            if ($indexer) {
                $state = $indexer->isScheduled()
                    ? IndexerStateInterface::STATUS_VALID
                    : IndexerStateInterface::STATUS_INVALID;
                $indexer->getState()->setStatus($state)->save();
            }
        }
    }

    /**
     * @param array $integration
     * @return void
     */
    protected function flushCache($integration)
    {
        $caches = $integration['cache'] ?? [];
        if (!empty($caches)) {
            $this->cacheManager->flush($caches);
        }
    }

    /**
     * @param array $integration
     * @return void
     */
    protected function lockIndexers($integration)
    {
        $indexers = $integration['indexers'] ?? [];

        foreach ($indexers as $indexerId) {
            $indexer = $this->getIndexer($indexerId);
            if ($indexer) {
                $indexer->getState()->setStatus(self::INDEXER_STATUS_LOCKED)->save();
            }
        }
    }

    /**
     * @param array $integration
     * @return void
     */
    protected function suspendMviewIndexers($integration)
    {
        $mviews = array_flip($integration['mviews'] ?? []);

        foreach ($this->getIndexerViewsCollection() as $view) {
            if (isset($mviews[$view->getViewId()])) {
                $view->getState()
                    ->setStatus(StateInterface::STATUS_SUSPENDED)
                    ->save();
            }
        }
    }

    /**
     * @param array $integration
     * @return void
     */
    protected function runMviewIndexers($integration)
    {
        $mviews = array_flip($integration['mviews'] ?? []);

        foreach ($this->getIndexerViewsCollection() as $view) {
            if (isset($mviews[$view->getViewId()])
                && $view->getState()->getStatus() == StateInterface::STATUS_SUSPENDED
            ) {
                $view->getState()
                    ->setStatus(StateInterface::STATUS_IDLE)
                    ->save();
            }
        }
    }

    /**
     * @return \Magento\Framework\Mview\View\CollectionInterface
     */
    protected function getIndexerViewsCollection()
    {
        $collection = $this->viewsFactory->create();
        $collection->getItemsByColumnValue('group', 'indexer');
        return $collection;
    }

    /**
     * @param string $indexerId
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    protected function getIndexer($indexerId)
    {
        try {
            return $this->indexerRegistry->get($indexerId);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
        }

        return null;
    }
}
