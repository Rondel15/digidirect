<?php

namespace Digidirect\AI\Cron;

class CleanupDbLogs
{
    /**
     * @var \Digidirect\AI\Helper\Logger
     */
    protected $loggerHelper;

    /**
     * @var \Digidirect\AI\Model\ResourceModel\Logger\Logger
     */
    protected $loggerResource;

    /**
     * LogBackup constructor.
     * @param \Digidirect\AI\Helper\Logger $loggerHelper
     * @param \Digidirect\AI\Model\ResourceModel\Logger\Logger $loggerResource
     */
    public function __construct(
        \Digidirect\AI\Helper\Logger $loggerHelper,
        \Digidirect\AI\Model\ResourceModel\Logger\Logger $loggerResource
    ) {
        $this->loggerHelper = $loggerHelper;
        $this->loggerResource = $loggerResource;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $days = $this->loggerHelper->getRemoveDbRecordsOlderThanDays();
        if ($days > 0) {
            $this->loggerResource->removeRecordsOlderThanDays($days);
        }
    }
}
