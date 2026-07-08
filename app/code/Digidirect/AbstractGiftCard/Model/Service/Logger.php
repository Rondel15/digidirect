<?php

namespace Digidirect\AbstractGiftCard\Model\Service;

use Psr\Log\LoggerInterface;
use Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface;
use Monolog\Logger as MonologLogger;

/**
 * Class Logger
 */
class Logger implements AbstractGiftCardLoggerInterface
{
    const DEBUG_KEYS_MASK = '****';

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Digidirect\AbstractGiftCard\Service\ConfigInterface
     */
    private $_config;

    /**
     * @var bool
     */
    private $_forceDebug = false;

    /**
     * @var bool
     */
    private $_commandCode;

    /**
     * @param LoggerInterface $logger
     * @param bool $forceDebug
     * @param \Digidirect\AbstractGiftCard\Service\ConfigInterface $config
     */
    public function __construct(
        LoggerInterface $logger,
        $forceDebug = false,
        \Digidirect\AbstractGiftCard\Service\ConfigInterface $config = null
    ) {
        $this->_logger = $logger;
        $this->_config = $config;
        $this->_forceDebug = $forceDebug;
    }

    /**
     * @param array $message
     * @param int|string $level
     * @param array|null $maskKeys
     * @param bool|null $forceDebug
     * @return void
     */
    public function debug($message, $level = MonologLogger::DEBUG, array $maskKeys = null, $forceDebug = null)
    {
        $debugOn = $forceDebug !== null ? $forceDebug : $this->isDebugOn();
        if ($debugOn === true) {
            if (is_array($message)) {
                $maskKeys = $maskKeys !== null ? $maskKeys : $this->_getDebugReplaceFields();
                $message = $this->_filterDebugData(
                    $message,
                    $maskKeys
                );
                $message = var_export($message, true);
            }
            $this->_logger->log($level, $message);
        }
    }

    /**
     * Returns configured keys to be replaced with mask
     *
     * @return array
     */
    private function _getDebugReplaceFields()
    {
        if ($this->_config and $this->_config->getValue('debugReplaceKeys')) {
            return explode(',', $this->_config->getValue('debugReplaceKeys'));
        }
        return [];
    }

    /**
     * Whether debug is enabled in configuration
     *
     * @return bool
     */
    public function isDebugOn()
    {
        return $this->_forceDebug || ($this->_config && (bool)$this->_config->getValue('debug'));
    }

    /**
     * Recursive filter data by private conventions
     *
     * @param array $debugData
     * @param array $debugReplacePrivateDataKeys
     * @return array
     */
    protected function _filterDebugData(array $debugData, array $debugReplacePrivateDataKeys)
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $debugReplacePrivateDataKeys);

        foreach (array_keys($debugData) as $key) {
            if (in_array(strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = self::DEBUG_KEYS_MASK;
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->_filterDebugData($debugData[$key], $debugReplacePrivateDataKeys);
            }
        }
        return $debugData;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCommandCode($code)
    {
        $this->_commandCode = $code;
        if (method_exists($this->_logger, 'setCommandCode')) {
            $this->_logger->setCommandCode($this->_commandCode);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandCode()
    {
        return $this->_commandCode;
    }
}
