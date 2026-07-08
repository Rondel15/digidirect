<?php

namespace Digidirect\AI\Model\Logger;

use Digidirect\AI\Model\Logger\LoggerInterface as LoggerInterface;
use Digidirect\AI\Model\Integrations as Integration;
use Digidirect\AI\Model\Logger\Exception\LoggerException as LoggerException;
use Digidirect\AI\Helper\Logger as LoggerHelper;

/**
 * Class Logger
 *
 * @package Digidirect\AI\Model\Logger
 */
class Logger implements LoggerInterface
{
    const LOG_PLACE_DB = 'db';
    const LOG_PLACE_FILE = 'file';
    const LOG_PLACE_FILE_AND_DB = 'file_and_db';
    const LOG_PLACE_ALTERNATIVE = 'alternative';
    const LOG_PLACE_EVERYWHERE = 'all';
    const LOG_PLACE_EMAIL = 'email';

    /**
     * Message level
     *
     * @var string
     */
    protected $_messageLevel;

    /**
     * @var array
     */
    protected $_loggers = [];

    /**
     * @var array
     */
    protected $_alternativeLoggers = [];

    /**
     * @var \PSR\Log\LoggerInterface
     */
    protected $_psrLogger;

    /**
     * Integration
     *
     * @var \Digidirect\AI\Model\Integrations\Integrations
     */
    protected $_integration;

    /**
     * Object manager
     *
     * @var null
     */
    protected $_objectManager;

    /**
     * Logger helper
     *
     * @var LoggerHelper
     */
    protected $_logHelper;

    /**
     * Chain stack
     *
     * @var []
     */
    protected $_chainStack;

    /**
     * Logger constructor.
     * @param LoggerHelper $logHelper
     * @param Types\DbFactory $dbLoggerFactory
     * @param Types\FileFactory $fileLoggerFactory
     * @param \PSR\Log\LoggerInterface $psrLogger
     * @param null $integration
     * @param array $alternativeLoggers
     */
    public function __construct(
        \Digidirect\AI\Helper\Logger $logHelper,
        \Digidirect\AI\Model\Logger\Types\DbFactory $dbLoggerFactory,
        \Digidirect\AI\Model\Logger\Types\FileFactory $fileLoggerFactory,
        \PSR\Log\LoggerInterface $psrLogger,
        $integration = null,
        $alternativeLoggers = []
    ) {
        $this->_psrLogger = $psrLogger;
        $this->_integration = $integration;
        $this->_logHelper = $logHelper;
        $this->_alternativeLoggers = $alternativeLoggers;
        $this->_loggers[self::LOG_PLACE_FILE] = $fileLoggerFactory->create(['logger' => $this]);
        $this->_loggers[self::LOG_PLACE_DB] = $dbLoggerFactory->create(['data' => ['logger' => $this]]);
        $this->_loggers += $this->_alternativeLoggers;

        $this->_initLogger();
    }

    /**
     * Init integration
     *
     * @return $this
     */
    protected function _initLogger()
    {
        $this->_messageLevel = $this->getHelper()->getInfoCode();
        $this->_chainStack = [];

        return $this;
    }

    /**
     * Get helper
     *
     * @return LoggerHelper
     */

    public function getHelper()
    {
        return $this->_logHelper;
    }

    /**
     * @return mixed
     */
    public function getDbLogger()
    {
        return $this->_loggers[self::LOG_PLACE_DB];
    }

    /**
     * @return mixed
     */
    public function getFileLogger()
    {
        return $this->_loggers[self::LOG_PLACE_FILE];
    }

    /**
     * @return string
     */
    public function getMessageLevel()
    {
        return $this->_messageLevel;
    }

    /**
     * @return Integration\Integrations|null
     */
    public function getIntegration()
    {
        return $this->_integration;
    }

    /**
     * @return mixed
     */
    public function getCallStack()
    {
        return $this->_chainStack;
    }

    /**
     * @param string $processCode
     * @return void
     */
    public function addCallStack($processCode)
    {
        $this->_chainStack[] = $processCode;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getLogRecordIdentifier($type = self::LOG_PLACE_DB)
    {
        return $this->_loggers[$type]->getRecordIdentifier();
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function info($message, array $context = [], $place = self::LOG_PLACE_FILE_AND_DB)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_INFO_CODE, $context, $place);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function notice($message, array $context = [], $place = self::LOG_PLACE_FILE_AND_DB)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_NOTICE_CODE, $context, $place);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function alert($message, array $context = [], $place = self::LOG_PLACE_FILE_AND_DB)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_ALERT_CODE, $context, $place);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function warning($message, array $context = [], $place = self::LOG_PLACE_FILE_AND_DB)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_WARNING_CODE, $context, $place);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function debug($message, array $context = [], $place = self::LOG_PLACE_FILE)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_DEBUG_CODE, $context, $place);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function error($message, array $context = [], $place = self::LOG_PLACE_FILE_AND_DB)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_ERROR_CODE, $context, $place);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function critical($message, array $context = [], $place = self::LOG_PLACE_EVERYWHERE)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_CRITICAL_CODE, $context, $place);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function emergency($message, array $context = [], $place = self::LOG_PLACE_EVERYWHERE)
    {
        return $this->record($message, LoggerHelper::RECORD_TYPE_EMERGENCY_CODE, $context, $place);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @param string $place
     * @return Logger
     */
    public function log($level, $message, array $context = [], $place = self::LOG_PLACE_FILE)
    {
        return $this->record($message, $level, $context, $place);
    }

    /**
     * @param string $message
     * @param string $level
     * @param string $place
     * @return mixed
     */
    public function addHeader($message, $level = null, $place = null)
    {
        if ($level === null) {
            $level = LoggerHelper::RECORD_TYPE_INFO_CODE;
        }
        if ($place === null) {
            $place = self::LOG_PLACE_FILE_AND_DB;
        }
        $this->_processLevel($level);
        $loggerTypes = $this->getLoggerTypesByPlace($place);
        foreach ($loggerTypes as $loggerType) {
            $loggerType->addHeader($message, $level);
        }

        return $this;
    }

    /**
     * @param string|array $identifiers
     * @param string $place
     * @return mixed
     */
    public function addIdentifyingParams($identifiers, $place = null)
    {
        $level = LoggerHelper::RECORD_TYPE_INFO_CODE;
        if ($place === null) {
            $place = self::LOG_PLACE_FILE_AND_DB;
        }
        $this->_processLevel($level);
        $loggerTypes = $this->getLoggerTypesByPlace($place);
        foreach ($loggerTypes as $loggerType) {
            $loggerType->addIdentifyingParams($identifiers);
        }

        return $this;
    }

    /**
     * @param array|string $place
     * @return \Digidirect\AI\Model\Logger\Types\TypesInterface[]
     */
    protected function getLoggerTypesByPlace($place)
    {
        $places = [];
        if (!is_array($place)) {
            switch ($place) {
                case self::LOG_PLACE_FILE_AND_DB:
                    $places = [self::LOG_PLACE_FILE, self::LOG_PLACE_DB];
                    break;
                case self::LOG_PLACE_ALTERNATIVE:
                    $places = array_keys($this->_alternativeLoggers);
                    break;
                case self::LOG_PLACE_EVERYWHERE:
                    $places = array_keys($this->_loggers);
                    break;
                default:
                    $places[] = $place;
                    break;
            }
        }
        $loggerTypes = [];
        foreach ($places as $loggerType) {
            if (isset($this->_loggers[$loggerType])
                && $this->_loggers[$loggerType] instanceof \Digidirect\AI\Model\Logger\Types\TypesInterface
            ) {
                $loggerTypes[$loggerType] = $this->_loggers[$loggerType];
            }
        }
        return $loggerTypes;
    }

    /**
     * @param string $message
     * @param null $level
     * @param array $context
     * @param string $place
     * @return $this
     */
    protected function record($message, $level = null, array $context = [], $place = self::LOG_PLACE_FILE)
    {
        $this->_processLevel($level);
        $this->_formatMessage($message, $level, $context);

        $loggerTypes = $this->getLoggerTypesByPlace($place);
        foreach ($loggerTypes as $loggerType) {
            try {
                $loggerType->record(
                    $message,
                    [
                        'logger' => $this,
                        'level' => $level,
                        'context' => $context
                    ]
                );
            } catch (\Throwable $e) {
                $this->_psrLogger->error($e->getMessage(), $e);
            }
        }

        return $this;
    }

    /**
     * @param string $message
     * @param null $level
     * @param array $context
     * @return void
     */
    protected function _formatMessage(&$message, $level = null, array $context = [])
    {
        $message = trim($message);
        if ($levelWrapper = trim($this->getHelper()->_getLevelWrapper($level))) {
            $message = $levelWrapper . ' ' . $message;
        }

        $contextMsg = !empty($context) ? trim($this->getHelper()->_prepareContext($context)) : '';
        if (!empty($contextMsg)) {
            $message .= ' ' . $contextMsg;
        }
    }

    /**
     * Process Level
     *
     * @param string $newLevel
     * @return void
     */
    protected function _processLevel($newLevel)
    {
        if ($newLevel) {
            $levelCode = $this->getHelper()->getLevelCodeByType($newLevel);
            if ($levelCode > $this->_messageLevel) {
                $this->_messageLevel = $levelCode;
            }
        }
    }
}
