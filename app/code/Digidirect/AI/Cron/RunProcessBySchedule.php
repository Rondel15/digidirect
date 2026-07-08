<?php

namespace Digidirect\AI\Cron;

use Digidirect\AI\Model\Engine\EngineFactory;
use Digidirect\AI\Model\Integrations\ScheduleFactory;

/**
 * Class RunProcessBySchedule
 * @package Digidirect\AI\Cron
 */
class RunProcessBySchedule
{
    /**
     * @var EngineFactory
     */
    protected $integrationEngineFactory;

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * RunProcessBySchedule constructor.
     * @param EngineFactory $integrationEngineFactory
     * @param ScheduleFactory $scheduleFactory
     */
    public function __construct(
        EngineFactory $integrationEngineFactory,
        ScheduleFactory $scheduleFactory
    ) {
        $this->integrationEngineFactory = $integrationEngineFactory;
        $this->scheduleFactory = $scheduleFactory;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        /**
         * @var $schedule \Digidirect\AI\Model\Integrations\Schedule
         * @var $scheduleCollection \Digidirect\AI\Model\ResourceModel\Integrations\Schedule\Collection
         */
        $scheduleCollection = $this->scheduleFactory->create()->getCollection();

        foreach ($scheduleCollection as $schedule) {
            $integration = $this->integrationEngineFactory
                ->create(
                    [
                        'processCode' => $schedule->getProcessCode(),
                        'initiator' => 'CRON: Scheduled Run',
                        'runOptions' => $schedule->getRunOptions(),
                        'scheduleId' => $schedule->getId(),
                        'isAdminRun' => $schedule->getIsAddedByAdmin(),
                    ]
                );
            $integration->run();
            unset($integration);
        }

        return $this;
    }
}
