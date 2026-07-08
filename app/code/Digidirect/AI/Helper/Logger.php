<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digidirect\AI\Helper;

use Magento\Framework\DataObject;

/**
 * Class Logger
 *
 * @package Digidirect\AI\Helper
 */
class Logger extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LOG_DIRECTORY_POSTFIX = 'digidirect_integrations_logs';

    const RECORD_TYPE_INFO_CODE = 'info';

    const RECORD_TYPE_NOTICE_CODE = 'notice';

    const RECORD_TYPE_ALERT_CODE = 'alert';

    const RECORD_TYPE_EMERGENCY_CODE = 'emergency';

    const RECORD_TYPE_ERROR_CODE = 'error';

    const RECORD_TYPE_CRITICAL_CODE = 'critical';

    const RECORD_TYPE_WARNING_CODE = 'warning';

    const RECORD_TYPE_DEBUG_CODE = 'debug';

    const RECORD_TYPES_ARRAY = [
        1 => self::RECORD_TYPE_DEBUG_CODE,
        2 => self::RECORD_TYPE_INFO_CODE,
        3 => self::RECORD_TYPE_NOTICE_CODE,
        4 => self::RECORD_TYPE_ALERT_CODE,
        5 => self::RECORD_TYPE_WARNING_CODE,
        6 => self::RECORD_TYPE_ERROR_CODE,
        7 => self::RECORD_TYPE_CRITICAL_CODE,
        8 => self::RECORD_TYPE_EMERGENCY_CODE
    ];

    const DAYS_TO_BACKUP = 7;

    const BACKUP_FILE_PREFIX = 'backup';

    /**
     * Timezone Interface
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    /**
     * Scope Config Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Directory List
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * NormalizerFormatter
     *
     * @var []
     */
    protected $_normalizerFormatter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Logger constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Monolog\Formatter\NormalizerFormatter $normalizerFormatter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Monolog\Formatter\NormalizerFormatter $normalizerFormatter,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->_normalizerFormatter = $normalizerFormatter;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_directoryList = $directoryList;
        $this->_timezone = $timezone;
        $this->dateTime = $dateTime;
    }

    /**
     * Get record type const
     *
     * @param int $type
     * @return string
     */
    public function getRecordTypeConst($type)
    {
        return self::RECORD_TYPES_ARRAY[$type];
    }

    /**
     * Get level wrapper
     *
     * @param string $newLevel
     * @return string
     */
    public function _getLevelWrapper($newLevel)
    {
        $wrapper = '';
        if ($newLevel) {
            $wrapper = $this->getNow('j M H:i:s') . ' :: ' . strtoupper($newLevel) . ' :: ';
        }

        return $wrapper;
    }

    /**
     * Get now date string
     *
     * @param string $format
     * @return string
     */
    public function getNow($format = 'Y-m-d H:i:s')
    {
        $date = (new \DateTime())->setTimestamp($this->_timezone->scopeTimeStamp());
        return $date->format($format);
    }

    /**
     * Get now date string
     *
     * @return string
     */
    public function getGmtNow()
    {
        return $this->dateTime->gmtDate();
    }

    /**
     * Get now file format
     *
     * @return string
     */
    public function getNowFileFormat()
    {
        return $this->getNow('Y-m-d-H-i-s');
    }

    /**
     * Get now file format
     *
     * @return string
     */
    public function getNowFileWithMicroFormat()
    {
        $micro = (float)microtime();
        $micro = sprintf("%06d", $micro * 1000000);
        return $this->getNowFileFormat() . '.' . $micro;
    }

    /**
     * Get level code by type
     *
     * @param string $messageCode
     * @return string
     */
    public function getLevelCodeByType($messageCode)
    {
        $code = array_search($messageCode, $this->getRecordTypes());
        if (!$code) {
            return $this->getInfoCode();
        }

        return $code;
    }

    /**
     * Get info code
     *
     * @return string
     */
    public function getInfoCode()
    {
        return $this->getLevelCodeByType(self::RECORD_TYPE_INFO_CODE);
    }

    /**
     * Get record types
     *
     * @return []
     */
    public function getRecordTypes()
    {
        return self::RECORD_TYPES_ARRAY;
    }

    /**
     * @return string
     */
    public function getRemoveDbRecordsOlderThanDays()
    {
        $days = (int)$this->scopeConfig->getValue('digidirect_ai/logs/remove_db_logs_older_than_days');
        return $days;
    }

    /**
     * Get log file path
     *
     * @return string
     */
    public function getLogFilePath()
    {
        $configValue = "";
        if(!empty($this->getConfValue('digidirect_ai/logs/logs_path')))
        {
            $configValue = trim($this->getConfValue('digidirect_ai/logs/logs_path'));
        }
        $path = $configValue ? $configValue
            : $this->_directoryList->getPath('var');

        return $path . DIRECTORY_SEPARATOR . self::LOG_DIRECTORY_POSTFIX;
    }

    /**
     * Get log files backups path
     *
     * @return string
     */
    public function getLogFilesBackupFolder()
    {
        $configValue = "";
        if(!empty($this->getConfValue('digidirect_ai/logs/logs_path')))
        {
            $configValue = trim($this->getConfValue('digidirect_ai/logs/backup_logs_path'));
        }
        
        $path = $configValue ? $configValue
            : $this->getLogFilePath() .
            DIRECTORY_SEPARATOR . self::BACKUP_FILE_PREFIX;

        return $path;
    }

    /**
     * Get filename for backup of log files
     *
     * @return string
     */
    public function getLogFilesBackupFilename()
    {
        $path = $this->getLogFilesBackupFolder() .
            DIRECTORY_SEPARATOR .
            self::BACKUP_FILE_PREFIX .
            '_' .
            $this->getNowFileFormat() .
            '.tar';

        return $path;
    }

    /**
     * Get log files backups path
     *
     * @return string
     */
    public function getLogFileBackupsDays()
    {
        $configValue = (int)$this->getConfValue('digidirect_ai/logs/logs_files_backup_days');
        $days = $configValue ? $configValue : self::DAYS_TO_BACKUP;

        return $days;
    }

    /**
     * Get email template
     *
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->getConfValue('digidirect_ai/logs/logs_email_template');
    }

    /**
     * Get config value by path
     *
     * @param string $path
     * @return string
     */
    public function getConfValue($path)
    {
        return $this->_scopeConfig->getValue($path);
    }

    /**
     * Scan dir
     *
     * @param string $folder
     * @param mixed &$results
     * @return mixed
     */
    public function scanDir($folder, &$results = [])
    {
        if (!is_dir($folder)) {
            return [];
        }

        $files = scandir($folder);

        foreach ($files as $key => $value) {
            $path = realpath($folder . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else {
                if ($value != "." && $value != ".." && $value != self::BACKUP_FILE_PREFIX) {
                    $this->scanDir($path, $results);
                }
            }
        }
        return $results;
    }

    /**
     * Prepare Context
     *
     * @param array $context
     * @return string
     */
    public function _prepareContext(array $context = [])
    {
        if (!empty($context)) {
            return $this->varExportForLog($context);
        }

        return '';
    }

    /**
     * Convert To String
     *
     * @param []|string|int|object $data
     * @return string
     */
    public function convertToString($data)
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string)$data;
        }

        return \Zend_Json::encode($data, true);
    }

    /**
     * @param mixed $var
     * @param bool $complexObjectData
     * @param int $level
     * @param string $indent
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function varExportForLog($var, $complexObjectData = false, $level = 0, $indent = '    ')
    {
        if ($var === null || is_scalar($var)) {
            return var_export($var, true);
        }

        if (is_array($var)) {
            $code = 'array(';

            if (count($var) > 0) {
                foreach ($var as $key => $value) {
                    $exportKey = var_export($key, true);
                    $exportValue = $this->varExportForLog($value, $complexObjectData, $level + 1, $indent);
                    $exportValue = rtrim($exportValue);
                    $code .= PHP_EOL . str_repeat($indent, $level + 1) . $exportKey . ' => ' . $exportValue . ',';
                }
                $code .= PHP_EOL . str_repeat($indent, $level);
            }

            $code .= ')' . PHP_EOL;

            return $code;
        }

        if (is_object($var)) {
            $data = null;
            $class = get_class($var);
            $code = 'object ' . $class;
            if ($class === 'stdClass') {
                $data = json_decode(json_encode($var), true);
            }
            if ($var instanceof \Magento\Framework\Model\AbstractModel && $var->getId()) {
                $code .= ' #' . $var->getId();
            }
            if ($complexObjectData) {
                if ($var instanceof \Magento\Framework\DataObject) {
                    $data = $var->debug();
                }
            }
            if ($data !== null) {
                $code .= ' with data: ' . $this->varExportForLog($data, $complexObjectData, $level, $indent);
            }
            return $code;
        }

        if (is_resource($var)) {
            return sprintf('resource %s', get_resource_type($var));
        }

        return var_export($var, true);
    }

}
