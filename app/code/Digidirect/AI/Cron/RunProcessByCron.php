<?php

namespace Digidirect\AI\Cron;

/**
 * Class LogBackup
 * @package Digidirect\AI\Cron
 */
class RunProcessByCron
{
    /**
     * @var \Digidirect\AI\Model\Engine\EngineFactory
     */
    protected $integrationEngine;

    /**
     * RunProcessByCron constructor.
     * @param \Digidirect\AI\Model\Engine\EngineFactory $integrationEngine
     */
    public function __construct(
        \Digidirect\AI\Model\Engine\EngineFactory $integrationEngine
    ) {
        $this->integrationEngine = $integrationEngine;
    }

    /**
     * @param string $processCode
     * @param array $arguments
     * @return $this
     */
    public function __call($processCode, $arguments = [])
    {
        $this->integrationEngine
            ->create(
                [
                    'processCode' => $processCode,
                    'initiator' => 'CRON',
                    'runOptions' => [],
                    'isCronRun' => true,
                ]
            )
            ->run();
        return $this;
    }
}
