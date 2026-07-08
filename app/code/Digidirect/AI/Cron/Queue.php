<?php

namespace Digidirect\AI\Cron;

use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Model\Engine\Queue\QueueProcessor;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;
use Digidirect\AI\Model\Engine\Queue\Status as QueueStatus;
use Digidirect\AI\Model\ResourceModel\Queue\Queue as QueueResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Queue
 *
 * @package Digidirect\AI\Cron
 */
class Queue
{
    const DEFAULT_INTERVAL_SEC_ATTEMPTS = 900;

    /**
     * @var QueueRepository
     */
    protected $queueRepository;

    /**
     * @var QueueProcessor
     */
    protected $queueProcessor;

    /**
     * @var QueueResource
     */
    protected $queueResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var int
     */
    protected $intervalSecAttempts;

    /**
     * @var int
     */
    protected $intervalSecAttemptsQueueDepends;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * Queue constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param QueueRepository $queueRepository
     * @param QueueProcessor $queueProcessor
     * @param QueueResource $queueResource
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        QueueRepository $queueRepository,
        QueueProcessor $queueProcessor,
        QueueResource $queueResource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->queueRepository = $queueRepository;
        $this->queueProcessor = $queueProcessor;
        $this->queueResource = $queueResource;
        $this->scopeConfig = $scopeConfig;
        $this->intervalSecAttempts = (int)$this->scopeConfig->getValue('digidirect_ai/queue/interval_of_attempts');
        $this->intervalSecAttemptsQueueDepends = (int)$this->scopeConfig->getValue(
            'digidirect_ai/queue/interval_of_attempts_for_pending_depends'
        );
        $this->batchSize = (int)$this->scopeConfig->getValue('digidirect_ai/queue/batch_size');
    }

    /**
     * @return array
     */
    protected function getLastRunAtFilters()
    {
        /**
         * @var $adapter \Magento\Framework\DB\Adapter\Pdo\Mysql
         */
        $lastRunAtOrConditions = [
            $this->filterBuilder->setField(QueueInterface::LAST_RUN_AT)->setConditionType('null')->create(),
        ];

        $dateTime = new \DateTime();
        $nowTimestamp = $dateTime->getTimestamp();

        $dateTime->setTimestamp($nowTimestamp - $this->intervalSecAttempts);
        $lastRunAtPending = $dateTime->format('Y-m-d H:i:s');

        if ($this->intervalSecAttempts == $this->intervalSecAttemptsQueueDepends) {
            $lastRunAtOrConditions[] = $this->filterBuilder->setField(QueueInterface::LAST_RUN_AT)
                ->setValue($lastRunAtPending)
                ->setConditionType('lteq')
                ->create();
        } else {
            $dateTime->setTimestamp($nowTimestamp - $this->intervalSecAttemptsQueueDepends);
            $lastRunAtPendingDepends = $dateTime->format('Y-m-d H:i:s');

            $adapter = $this->queueResource->getConnection();
            $cases = [
                $adapter->quoteInto(QueueInterface::STATE . ' = ?', QueueState::STATE_PENDING_DEPENDS) =>
                    $adapter->getCheckSql(
                        $adapter->quoteInto(QueueInterface::LAST_RUN_AT . ' < ?', $lastRunAtPendingDepends),
                        1,
                        0
                    ),
            ];
            $default = $adapter->getCheckSql(
                $adapter->quoteInto(QueueInterface::LAST_RUN_AT . ' < ?', $lastRunAtPending),
                1,
                0
            );
            $sql = $adapter->getCaseSql('', $cases, $default);
            $lastRunAtOrConditions[] = $this->filterBuilder->setField($sql)
                ->setValue(1)
                ->setConditionType('eq')
                ->create();
        }

        return $lastRunAtOrConditions;
    }

    /**
     * @return \Magento\Framework\Api\SearchCriteria
     */
    protected function getSearchCriteria()
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(QueueInterface::SEQUENCE)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(QueueInterface::STATE, [QueueState::STATE_PENDING, QueueState::STATE_PENDING_DEPENDS], 'in')
            ->addFilters($this->getLastRunAtFilters())
            ->addSortOrder($sortOrder)
            ->setPageSize($this->batchSize);

        return $searchCriteria->create();
    }

    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        do {
            $searchCriteria = $this->getSearchCriteria();
            $list = $this->queueRepository->getList($searchCriteria);
            if (!$list->getTotalCount()) {
                //all items processed
                break;
            }
            foreach ($list->getItems() as $queueItem) {
                /* @var QueueInterface $queueItem */
                $this->queueProcessor->processQueueItem($queueItem);
            }
        } while (true);

        return $this;
    }
}
