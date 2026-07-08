<?php

namespace Digidirect\AI\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Digidirect\AI\Helper
 */
class Backup extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Timezone Interface
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Directory List
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * Engine Helper
     *
     * @var \Digidirect\AI\Model\Engine\Mail\Mail
     */
    protected $mailer;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Digidirect\AI\Model\Engine\Mail\MailFactory $mailer
     * @param \Magento\Framework\Filesystem\Io\File $io
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Digidirect\AI\Model\Engine\Mail\MailFactory $mailer,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->directoryList = $directoryList;
        $this->scopeConfig = $context->getScopeConfig();
        $this->timezone = $timezone;
        $this->mailer = $mailer;
        $this->io = $io;

        parent::__construct($context);
    }

    /**
     * Get backup file path
     *
     * @param string $group
     * @param string $scopeCode
     * @param string $ext
     * @return string
     */
    public function getBackupPath($group, $scopeCode = null, $ext = 'txt')
    {
        $configValue = $this->scopeConfig->getValue(
            "digidirect_ai/backups/backups_path",
            ScopeInterface::SCOPE_WEBSITE,
            $scopeCode
        );

        $basePath = $configValue ? $configValue
            : $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . 'digidirect_integrations_backups';

        $backupPath = rtrim($basePath, '/') . DIRECTORY_SEPARATOR . $group . DIRECTORY_SEPARATOR
            . ($scopeCode ? $scopeCode : 'default') . DIRECTORY_SEPARATOR;

        if (!is_dir($backupPath)) {
            if (!$this->io->mkdir($backupPath)) {
                $msg = __('Can not create folder for Backups.') . $backupPath;
                $this->mailer->create()->sendFailEmail($msg, null, true);
                return false;
            }
        }

        return $backupPath;
    }

    /**
     * Get backup file path
     *
     * @param string $group
     * @param string $scopeCode
     * @param string $ext
     * @return string
     */
    public function getBackupFilePath($group, $scopeCode = null, $ext = 'txt')
    {
        $date = (new \DateTime())->setTimestamp($this->timezone->scopeTimeStamp());
        return $this->getBackupPath($group, $scopeCode, $ext) . $date->format('Y-m-d-H-i-s') . ".{$ext}";
    }
}
