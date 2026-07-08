<?php

namespace Digidirect\AI\Model\Engine;

use Digidirect\AI\Model\Engine\Exception\EngineException;
use Digidirect\AI\Model\Engine\Exception\QueueDependsException;
use Digidirect\AI\Model\Engine\Processor\Exception\ProcessException;
use Digidirect\AI\Model\Engine\Processor\ProcessorAbstract;
use Digidirect\AI\Model\Logger\Exception\LoggerException;
use Digidirect\AI\Model\Logger\Logger;
use Digidirect\AI\Model\Integrations\Integrations;
use Magento\Framework\DataObject;
use Digidirect\AI\Model\Engine\Processor\ProcessorInterface;

/**
 * Class Engine
 *
 * @package Digidirect\AI\Model\Engine
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Engine extends DataObject
{
    /**
     * Integrations
     *
     * @var \Digidirect\AI\Model\Integrations\Integrations
     */
    protected $_integration;

    /**
     * Initiator
     *
     * @var string
     */
    protected $_initiator;

    /**
     * Initial params
     *
     * @var []
     */
    protected $_initParams = [];

    /**
     * ManagerInterface
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Helper Engine
     *
     * @var \Digidirect\AI\Model\Engine\Mail\MailFactory
     */
    protected $_mailer;

    /**
     * DataObject
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_resultObj;

    /**
     * IntegrationsFactory
     *
     * @var \Digidirect\AI\Model\Integrations\IntegrationsFactory
     */
    protected $_integrationsFactory;

    /**
     * Helper Logger
     *
     * @var \Digidirect\AI\Helper\Logger
     */
    protected $_loggerHelper;

    /**
     * LoggerFactory
     *
     * @var \Digidirect\AI\Model\Logger\LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * Logger instance
     *
     * @var \Digidirect\AI\Model\Logger\Logger
     */
    protected $_logger;

    /**
     * ProcessorFactory Factory
     *
     * @var \Digidirect\AI\Model\Engine\Processor\ProcessorFactoryFactory
     */
    protected $_processorFactory;

    /**
     * Process code
     *
     * @var string
     */
    protected $_processCode;

    /**
     * Schedule Factory
     *
     * @var \Digidirect\AI\Model\Integrations\ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     * Queue Helper
     *
     * @var \Digidirect\AI\Helper\Queue
     */
    protected $_queueHelper;

    /**
     * Engine constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Digidirect\AI\Model\Engine\Mail\MailFactory $mailer
     * @param \Digidirect\AI\Helper\Logger $loggerHelper
     * @param \Digidirect\AI\Model\Integrations\IntegrationsFactory $integrationsFactory
     * @param \Digidirect\AI\Model\Logger\LoggerFactory $loggerFactory
     * @param Processor\ProcessorFactoryFactory $processorFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory
     * @param \Digidirect\AI\Helper\Queue $queueHelper
     * @param string $processCode
     * @param null $initiator
     * @param bool $isCronRun
     * @param null|int $queueId
     * @param null|int $scheduleId
     * @param bool $isAdminRun
     * @param array $runOptions
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Digidirect\AI\Model\Engine\Mail\MailFactory $mailer,
        \Digidirect\AI\Helper\Logger $loggerHelper,
        \Digidirect\AI\Model\Integrations\IntegrationsFactory $integrationsFactory,
        \Digidirect\AI\Model\Logger\LoggerFactory $loggerFactory,
        \Digidirect\AI\Model\Engine\Processor\ProcessorFactoryFactory $processorFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory,
        \Digidirect\AI\Helper\Queue $queueHelper,
        $processCode,
        $initiator = null,
        $isCronRun = false,
        $queueId = null,
        $scheduleId = null,
        $isAdminRun = false,
        $runOptions = [],
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_mailer = $mailer;
        $this->_loggerHelper = $loggerHelper;
        $this->_integrationsFactory = $integrationsFactory;
        $this->_loggerFactory = $loggerFactory;
        $this->_scheduleFactory = $scheduleFactory;
        $this->_queueHelper = $queueHelper;

        /** @var  \Digidirect\AI\Model\Engine\Processor\ProcessorFactory */
        $this->_processorFactory = $processorFactory;
        $this->_resultObj = $dataObjectFactory->create();
        $this->_processCode = $processCode;

        $this->setInitiator($initiator);
        $this->init($processCode, $runOptions, $isCronRun, $queueId, $scheduleId, $isAdminRun);

        parent::__construct($data);
    }

    /**
     * @return $this
     * @throws LoggerException
     */
    protected function _processQueueBeforeRun()
    {
        if (empty($this->_initParams['queue_id'])) {
            return $this;
        }

        try {
            $logId = $this->_logger->getLogRecordIdentifier();
            $this->_queueHelper->saveQueueLogRelation($this->_initParams['queue_id'], $logId);
        } catch (\Throwable $e) {
            throw new LoggerException(__('Could not save relation between log and queue'));
        }

        return $this;
    }

    /**
     * Run engine
     *
     * @return \Magento\Framework\DataObject
     */
    public function run()
    {
        $this->_logger->addHeader('Initiator: ' . $this->getInitiator() . '. ');

        try {
            $this->_processQueueBeforeRun();
            $this->_checkStartConditions();
        } catch (\Throwable $e) {
            $this->doOnCatch($e);
            return $this->_resultObj;
        }

        if (!empty($this->_initParams['schedule_id'])) {
            $schedule = $this->_scheduleFactory->create();
            $schedule->getResource()->load($schedule, $this->_initParams['schedule_id'])
                ->delete($schedule);
        }

        return $this->_run();
    }

    /**
     * Init params
     *
     * @param string $code
     * @param array $initParams
     * @param bool $isCronRun
     * @param int $queueId
     * @param int $scheduleId
     * @param bool $isAdminRun
     * @return \Magento\Framework\DataObject
     */
    protected function init(
        $code,
        $initParams = [],
        $isCronRun = false,
        $queueId = null,
        $scheduleId = null,
        $isAdminRun = false
    ) {
        $this->_resultObj->setData(
            [
                'result' => true,
                'message' => '',
                'exception' => null,
                'context' => null
            ]
        );

        /* Load Integration Object */
        $this->_integration = $this->_integrationsFactory->create();
        $this->_integration->getResource()->load($this->_integration, $code, 'process_code');
        $this->_logger = $this->_loggerFactory->create(['integration' => $this->_integration]);

        $this->_initParams['is_cron_run'] = $isCronRun;
        $this->_initParams['queue_id'] = $queueId;
        $this->_initParams['schedule_id'] = $scheduleId;
        $this->_initParams['is_admin_run'] = $isAdminRun;
        $this->_initParams['run_options'] = $initParams;
        $this->_initParams['logger'] = $this->_logger;
        $this->_initParams['integration'] = $this->_integration;

        return $this;
    }

    /**
     * @return ProcessorInterface
     */
    protected function _getProcessor()
    {
        $processor = $this->_processorFactory
            ->create()
            ->getProcessor(
                $this->_integration->getProcessClass(),
                $this->_initParams
            );

        return $processor;
    }

    /**
     * TODO: add started_at finished_at
     * Run processor
     *
     * @see digidirect_ai_integrations table
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    private function _run()
    {
        //used for shell script.
        try {
            $this->_integration->setProcessingStatus();

            $this->_logger->addHeader('Process Started.');
            $this->addRunOptionsLog($this->_initParams['run_options']);

            $this->_eventManager->dispatch(
                'digidirect_ai_engine_get_processor_before_' . $this->_integration->getProcessCode(),
                ['engine' => $this]
            );

            $processor = $this->_getProcessor();

            $this->_eventManager->dispatch('digidirect_ai_engine_process_run_before', ['processor' => $processor]);
            $this->_eventManager->dispatch(
                'digidirect_ai_' . $this->_integration->getProcessCode() . '_run_before',
                [
                    'processor' => $processor,
                    'engine' => $this
                ]
            );

            $processorResult = $processor->process();
            $this->_resultObj->setResult($processorResult);

            $this->_eventManager->dispatch('digidirect_ai_engine_process_run_after', ['processor' => $processor]);
            $this->_eventManager->dispatch(
                'digidirect_ai_' . $this->_integration->getProcessCode() . '_run_after',
                [
                    'processor' => $processor,
                    'engine' => $this
                ]
            );

            /**
             * Set last success date
             */
            $dateTime = new \DateTime();
            $this->_integration->setData(
                Integrations::SUCCESS_FINISH_DATE,
                $dateTime->getTimestamp()
            );
        } catch (\Throwable $e) {
            $this->doOnCatch($e);
        } finally {
            if (isset($processor) && $processor instanceof ProcessorAbstract) {
                $this->_processFlags($processor);
                $this->_resultObj->setData('context', $processor->getResult());
            }

            $this->_integration->setPendingStatus();
            $this->_logger->addHeader('Process Finished.');
        }

        /* Start Chain Process if it is */
        if ($this->_resultObj->getResult()) {
            if (trim($this->_integration->getChildProcessCode())) {
                $childProcessCode = $this->_integration->getChildProcessCode();
                $this->init($childProcessCode);
                $this->_logger->addCallStack($childProcessCode);

                $this->run();
            }
        }

        return $this->_resultObj;
    }

    /**
     * Process Flags
     *
     * @param ProcessorAbstract $processor
     * @return void
     */
    protected function _processFlags(ProcessorAbstract $processor)
    {
        //put queue
        if ($processor->getFlag(ProcessorAbstract::FLAG_PUT_TU_QUEUE)) {
            $this->_queueHelper->put($this->getInitiator(), $processor);
        }
        // send log emails
        if ($processor->getFlag(ProcessorAbstract::FLAG_SEND_LOGS_TO_EMAIL)) {
            $this->_mailer->sendProcessLogsEmail($this->_logger);
        }
    }

    /**
     * @return void
     * @throws EngineException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _checkStartConditions()
    {
        if (!$this->_integration->getProcessClass() || !$this->_integration->getProcessCode()) {
            throw new EngineException(
                __('Process data not fulfilled completely. Integration can not be run.')
            );
        }

        if ($this->_integration->getStatus() == Integrations::STATUS_DISABLED) {
            throw new EngineException(
                __('Integration can not be run, it\'s currently disabled.')
            );
        }

        if (empty($this->_integration['multiple_run'])
            && $this->_integration->getStatus() == Integrations::STATUS_PROCESSING
        ) {
            throw new EngineException(
                __('Integration can not be run, it\'s currently in process.')
            );
        }

        if (isset($this->_integration['run_options'])) {
            $missed = [];
            foreach ($this->_integration['run_options'] as $code => $params) {
                if (empty($params['required'])) {
                    continue;
                }
                if (!isset($this->_initParams['run_options'][$code])) {
                    $missed[] = $code;
                    continue;
                }
                $param = $this->_initParams['run_options'][$code];
                if (($param === null || is_scalar($param)) && !strlen($param)) {
                    $missed[] = $code;
                }
            }
            $result = count($missed) == 0;
            if (!$result) {
                throw new EngineException(
                    __('Could not run process: required run options are not set: %1', implode(', ', $missed))
                );
            }
        }
    }

    /**
     * @param \Throwable $e
     * @return string
     */
    protected function getExceptionIn(\Throwable $e)
    {
        return str_replace(BP . DIRECTORY_SEPARATOR, '', $e->getFile()) . ':' . $e->getLine();
    }

    /**
     * @param \Throwable $e
     * @param array $messages
     * @return array
     */
    protected function getPreviousExceptionMessages(\Throwable $e, $messages = [])
    {
        $ePrevious = $e->getPrevious();
        if ($ePrevious instanceof \Throwable) {
            $messages[] = 'Previous Exception in ' . $this->getExceptionIn($ePrevious);
            $messages = $this->getPreviousExceptionMessages($ePrevious, $messages);
        }
        return $messages;
    }

    /**
     * @param \Throwable $e
     * @return $this
     */
    protected function doOnCatch($e)
    {
        $loggerException = false;
        $logStackTrace = true;
        $sendEmail = true;
        switch (true) {
            case $e instanceof QueueDependsException:
                $message = __('Queue Exception');
                $logStackTrace = false;
                $sendEmail = false;
                break;
            case $e instanceof EngineException:
                $message = __('Engine Exception');
                break;
            case $e instanceof ProcessException:
                $message = __('Process Exception');
                break;
            case $e instanceof LoggerException:
                $loggerException = true;
                $message = __('Logger Exception');
                break;
            default:
                $message = __('System Exception');
                break;
        }

        $fullMessage = $message . ': ' . $e->getMessage() . ' in ' . $this->getExceptionIn($e);

        $previousExceptionMessages = $this->getPreviousExceptionMessages($e);
        if (!empty($previousExceptionMessages)) {
            $fullMessage .= PHP_EOL . implode(PHP_EOL, $previousExceptionMessages);
        }

        if (!$loggerException) {
            $this->_logger->addHeader(
                $fullMessage,
                \Digidirect\AI\Helper\Logger::RECORD_TYPE_ERROR_CODE,
                Logger::LOG_PLACE_DB
            );
        }

        if (!$loggerException) {
            if ($dbLogId = $this->_logger->getLogRecordIdentifier(Logger::LOG_PLACE_DB)) {
                $fullMessage = '[Log Record: #' . $dbLogId . '] ' . $fullMessage;
            }
            $this->_logger->error($fullMessage, [], Logger::LOG_PLACE_FILE);
        }

        $this->_resultObj->setData(
            [
                'result' => false,
                'message' => $fullMessage,
                'exception' => $e
            ]
        );

        if ($logStackTrace) {
            $this->_logger->info($e->__toString(), [], Logger::LOG_PLACE_FILE);
        }

        if ($sendEmail) {
            /**
             * @var $mailer \Digidirect\AI\Model\Engine\Mail\Mail
             */
            $mailer = $this->_mailer->create();
            if (!$loggerException) {
                $mailer->sendFailEmail($fullMessage, $this->_logger->getDbLogger());
            } else {
                $mailer->sendFailEmail($fullMessage);
            }
        }

        return $this;
    }

    /**
     * Set initiator
     *
     * @param string $name
     * @return $this
     */
    protected function setInitiator($name)
    {
        $this->_initiator = (string)$name;
        return $this;
    }

    /**
     * Get initiator
     *
     * @return string
     */
    protected function getInitiator()
    {
        if (!$this->_initiator) {
            $this->_initiator = php_sapi_name();
        }

        return $this->_initiator;
    }

    /**
     * @param array $runOptions
     * @return $this
     */
    protected function addRunOptionsLog($runOptions)
    {
        if (!empty($runOptions)) {
            $this->_logger->info(__('Run Options: '), $runOptions);
        } else {
            $this->_logger->info(__('Options are not set'));
        }

        return $this;
    }
}
