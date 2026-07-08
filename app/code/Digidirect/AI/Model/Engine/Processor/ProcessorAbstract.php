<?php

namespace Digidirect\AI\Model\Engine\Processor;

use Magento\Framework\DataObject;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Digidirect\AI\Model\Logger;
use Digidirect\AI\Model\Engine\Exception\EngineException;
use Digidirect\AI\Helper\Queue as QueueHelper;

/**
 * Class ProcessorAbstract
 *
 * @package Digidirect\AI\Model\Engine\Processor
 */
abstract class ProcessorAbstract extends DataObject
{
    /***************flags*******************/
    const FLAG_SEND_LOGS_TO_EMAIL = 1;
    const FLAG_PUT_TU_QUEUE = 2;
    /**************************************/
    const ALLOWED_FLAGS = [
        self::FLAG_SEND_LOGS_TO_EMAIL,
        self::FLAG_PUT_TU_QUEUE
    ];

    /**
     * Flags list
     *
     * @var []
     */
    private $_flags = [];

    /**
     * @var array
     */
    protected $_result = [];

    /**
     * LoggerInterface
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Integrations
     *
     * @var \Digidirect\AI\Model\Integrations\Integrations
     */
    protected $_integration;

    /**
     * Run options
     *
     * @var []
     */
    protected $_runOptions;

    /**
     * Queue Repository
     *
     * @var QueueRepository
     */
    protected $_queueRepository;

    /**
     * Queue Member
     *
     * @var int
     */
    protected $_queueId;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var bool
     */
    protected $isCronRun = false;

    /**
     * @var bool
     */
    protected $isQueueRun = false;

    /**
     * @var bool
     */
    protected $isScheduleRun = false;

    /**
     * is admin run = true even if it is added by admin scheduled run.
     *
     * @var bool
     */
    protected $isAdminRun = false;

    /**
     * ProcessorAbstract constructor.
     *
     * @param null $initParams
     */
    public function __construct(
        $initParams = null
    ) {
        parent::__construct();
        $this->_objectManager = $initParams->getObjectManager();
        $this->_queueRepository = $initParams->getQueueRepository();
        $this->_logger = $initParams->getLogger();
        $this->_integration = $initParams->getIntegration();
        $this->_queueId = $initParams->getQueueId();
        $this->_runOptions = $initParams->getRunOptions();
        $this->isCronRun = (bool)$initParams->getIsCronRun();
        $this->isQueueRun = $initParams->getQueueId() > 0;
        $this->isScheduleRun = $initParams->getScheduleId() > 0;
        $this->isAdminRun = (bool)$initParams->getIsAdminRun();
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     * @throws EngineException
     */
    public function getLogger()
    {
        if (!$this->_logger) {
            throw new EngineException('Logger is not initialized');
        }

        return $this->_logger;
    }

    /**
     * Get integration obj
     *
     * @return \Digidirect\AI\Model\Integrations\Integrations
     */
    protected function getIntegrationObj()
    {
        return $this->_integration;
    }

    /**
     * Get run options
     *
     * @return []
     */
    public function getRunOptions()
    {
        return $this->_runOptions;
    }

    /**
     * Get run option
     *
     * @param string $option
     * @return mixed
     */
    public function getRunOption($option)
    {
        return $this->_runOptions[$option] ?? null;
    }

    /**
     * Set run options
     *
     * @param array $options
     * @return $this
     */
    public function setRunOptions(array $options)
    {
        $this->_runOptions = $options;
        return $this;
    }

    /**
     * Set run option
     *
     * @param string $option
     * @param mixed $value
     * @return $this
     */
    public function setRunOption($option, $value)
    {
        $this->_runOptions[$option] = $value;
        return $this;
    }

    /**
     * Set Flag
     *
     * @param int $flag
     * @param int|bool $value
     * @return $this
     * @throws EngineException
     */
    public function setFlag($flag, $value)
    {
        if (!in_array($flag, self::ALLOWED_FLAGS)) {
            throw new EngineException(__('Flag "%1" not allowed.', $flag));
        }
        $this->_flags[$flag] = $value;

        return $this;
    }

    /**
     * Get Flag for some scenario
     *
     * @param int $flag
     * @return int|bool|null
     */
    public function getFlag($flag)
    {
        return isset($this->_flags[$flag]) ? $this->_flags[$flag] : null;
    }

    /**
     * Get Process Code
     *
     * @return string
     */
    public function getProcessCode()
    {
        return $this->_integration->getProcessCode();
    }

    /**
     * Get Queue Id
     *
     * @return int|null
     */
    public function getQueueId()
    {
        return $this->_queueId;
    }

    /**
     * Get Data as string for Queue for run
     * NOTE: PHP function serialize should be used in the end!
     * @see \Digidirect\AI\Helper\Queue::getUnserializedProcessData()
     *
     * @return string|false
     */
    public function convertRunOptionsToQueueData()
    {
        return QueueHelper::getSerializedProcessData($this->getRunOptions());
    }

    /**
     * @param string $entity
     * @return void
     */
    protected function applyEntityRules($entity)
    {
        $rules = $this->_integration->getEntityRules();
        foreach ($rules as $rule) {
            $rule->apply($entity);
        }
    }
}
