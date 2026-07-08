<?php

namespace Digidirect\Feed\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\Feed\Model\ResourceModel\Feed\History as HistoryResourceModel;

class CleanHistory
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HistoryResourceModel
     */
    protected $historyResourceModel;

    /**
     * CleanHistory constructor.
     * @param LoggerInterface $logger
     * @param HistoryResourceModel $historyResourceModel
     */
    public function __construct(
        LoggerInterface $logger,
        HistoryResourceModel $historyResourceModel
    ) {
        $this->logger = $logger;
        $this->historyResourceModel = $historyResourceModel;
    }

    /**
     * Execute
     * @return void
     */
    public function execute()
    {
        $date = new \Zend_Date();
        $date->subDay(3);

        try {
            $this->historyResourceModel->removeHistoryToDate($date->toString('Y-MM-dd H:mm:s'));
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
