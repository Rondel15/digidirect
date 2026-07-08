<?php

namespace Digidirect\AI\Model\Logger\Types;

use Digidirect\AI\Model\Integrations\Integrations as Integration;
use Digidirect\AI\Model\Logger\Exception\LoggerException;

/**
 * Class FileLogger
 * @package Digidirect\AI\Model\Logger
 */
class File extends \Magento\Framework\DataObject implements TypesInterface
{
    /**
     * Filename
     *
     * @var string
     */
    protected $_fileName;

    /**
     * @var string
     */
    protected $_fileDir;

    /**
     * Is file created
     *
     * @var bool
     */
    protected $_isFileCreated;

    /**
     * @var \Digidirect\AI\Model\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * File constructor.
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Digidirect\AI\Model\Logger\Logger $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Filesystem\Io\File $io,
        $logger,
        $data = []
    ) {
        $this->io = $io;
        $this->_logger = $logger;
        $this->_initFileLogger();

        parent::__construct($data);
    }

    /**
     * @return bool
     * @throws LoggerException
     */
    protected function _initFileLogger()
    {
        if (!$this->getLogger()->getIntegration() instanceof \Digidirect\AI\Model\Integrations\Integrations) {
            return false;
        }

        $this->_fileDir = $this->getHelper()->getLogFilePath()
            . DIRECTORY_SEPARATOR
            . $this->getIntegration()->getProcessCode()
            . DIRECTORY_SEPARATOR
            . $this->getHelper()->getNow('Y-m-d');

        $micro = (float)microtime();
        $micro = sprintf("%06d", $micro * 1000000);
        $uniqueHash = substr(md5(mt_rand(0, 999999999)), 0, 10);

        $this->_fileName = $this->getHelper()->getNow('H-i-s')
            . '.'
            . $micro
            . '_'
            . $uniqueHash
            . '.txt';

        $this->_isFileCreated = false;

        $this->checkDir();
        return true;
    }

    /**
     * @return \Digidirect\AI\Model\Logger\Logger
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return Integration|null
     */
    protected function getIntegration()
    {
        return $this->getLogger()->getIntegration();
    }

    /**
     * @return \Digidirect\AI\Helper\Logger
     */
    protected function getHelper()
    {
        return $this->getLogger()->getHelper();
    }

    /**
     * @return string
     */
    public function getRecordIdentifier()
    {
        return $this->_isFileCreated ? $this->getFullFilePath() : null;
    }

    /**
     * @param string $message
     * @param string $level
     * @return $this
     */
    public function addHeader($message, $level = null)
    {
        $level = $level ? $level : \Digidirect\AI\Helper\Logger::RECORD_TYPE_INFO_CODE;
        $this->record($this->getHelper()->_getLevelWrapper($level) . $message);
        return $this;
    }

    /**
     * @param string|array $identifiers
     * @return $this
     * @throws \Exception
     */
    public function addIdentifyingParams($identifiers)
    {
        return $this;
    }

    /**
     * Commit a message
     *
     * @param string $message
     * @param array $data
     * @return int|bool
     */
    public function record($message, $data = [])
    {
        $logger = $this->getLogger();
        if (!$logger->getIntegration() instanceof \Digidirect\AI\Model\Integrations\Integrations) {
            return false;
        }

        if (!file_exists($this->getFullFilePath())) {
            $f = fopen($this->getFullFilePath(), "w");
            fclose($f);
        }

        if (file_put_contents($this->getFullFilePath(), $message . PHP_EOL, FILE_APPEND | LOCK_EX)) {
            $this->_isFileCreated = true;
        }

        return true;
    }

    /**
     * Get is file created flag
     *
     * @return bool
     */
    public function getIsFileCreated()
    {
        return $this->_isFileCreated;
    }

    /**
     * Get full file path
     *
     * @return string
     */
    public function getFullFilePath()
    {
        $fileName = $this->_fileDir . DIRECTORY_SEPARATOR . $this->_fileName;
        return $fileName;
    }

    /**
     * Check whether directory exists otherwise create it
     *
     * @return void
     * @throws LoggerException
     */
    protected function checkDir()
    {
        if (!is_dir($this->_fileDir)) {
            $r = $this->io->mkdir($this->_fileDir, 0777, true);
            if (!$r) {
                throw new LoggerException('Unable to create logs folder' . $this->_fileDir);
            }
        }
    }
}
