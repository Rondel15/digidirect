<?php

namespace Digidirect\AI\Model\Logger\Types\File;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Digidirect\AI\Helper\Logger as LogHelper;
use Magento\Framework\Filesystem\Glob as FilesystemGlob;
use Magento\Framework\Filesystem\Io\File as FilesystemIoFile;
use Magento\Framework\Archive\ArchiveInterface;

class Backup implements BackupInterface
{
    const XML_PATH_BACKUP_PATH = 'digidirect_ai/logs/backup_logs_path';
    const XML_PATH_BACKUP_AFTER_X_DAYS = 'digidirect_ai/logs/logs_files_backup_days';

    const BACKUP_SUB_DIR = 'backups';
    const BACKUP_FILE_PREFIX = 'backup';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LogHelper
     */
    protected $logHelper;

    /**
     * @var FilesystemIoFile
     */
    protected $io;

    /**
     * @var ArchiveInterface
     */
    protected $archive;

    /**
     * @var string
     */
    protected $archiveFileExtension;

    /**
     * @var ArchiveInterface
     */
    protected $postArchive;

    /**
     * @var string
     */
    protected $postArchiveFileExtension;

    /**
     * Backup constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LogHelper $logHelper
     * @param FilesystemIoFile $io
     * @param ArchiveInterface $archive
     * @param string $archiveFileExtension
     * @param ArchiveInterface $postArchive
     * @param string $postArchiveFileExtension
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LogHelper $logHelper,
        FilesystemIoFile $io,
        ArchiveInterface $archive,
        $archiveFileExtension,
        ArchiveInterface $postArchive = null,
        $postArchiveFileExtension = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logHelper = $logHelper;
        $this->io = $io;
        $this->archive = $archive;
        $this->archiveFileExtension = $archiveFileExtension;
        $this->postArchive = $postArchive;
        $this->postArchiveFileExtension = $postArchiveFileExtension;
    }

    /**
     * @return string
     */
    protected function getBackupFolder()
    {
        $configValue = trim($this->scopeConfig->getValue(self::XML_PATH_BACKUP_PATH));
        $path = $configValue ?: $this->logHelper->getLogFilePath() . DIRECTORY_SEPARATOR . self::BACKUP_SUB_DIR;

        return $path;
    }

    /**
     * @param string $dirName
     * @return void
     * @throws \Exception
     */
    protected function checkDirOrCreate($dirName)
    {
        if (!is_dir($dirName) && !$this->io->mkdir($dirName)) {
            throw new \Exception(__('Can not create Folder %1 for Logs File Backups ', $dirName));
        }
    }

    /**
     * @return bool
     */
    protected function isDisabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BACKUP_AFTER_X_DAYS) <= 0;
    }

    /**
     * @return array
     */
    protected function getDirectoryList()
    {
        $result = [];
        $days = (int)$this->scopeConfig->getValue(self::XML_PATH_BACKUP_AFTER_X_DAYS);

        if ($days > 0) {
            $now = $this->logHelper->getNow('Y-m-d');
            $dateTime = \DateTime::createFromFormat('Y-m-d', $now);
            $dateTime->sub(new \DateInterval(sprintf('P%dD', $days)));
            $archiveOlderThan = $dateTime->format('Y-m-d');

            $logFolder = $this->logHelper->getLogFilePath();
            $mainDirectories = $this->io->getDirectoriesList($logFolder);
            foreach ($mainDirectories as $key => $mainDirectory) {
                $code = basename($mainDirectory);
                if ($code == self::BACKUP_SUB_DIR) {
                    continue;
                }

                $dateDirectories = $this->io->getDirectoriesList($mainDirectory);
                foreach ($dateDirectories as $k => $directory) {
                    $date = basename($directory);
                    if ($date < $archiveOlderThan) {
                        $result[$code][$date] = $directory;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function backup()
    {
        if ($this->isDisabled()) {
            return;
        }

        $backupFolder = $this->getBackupFolder();
        $this->checkDirOrCreate($backupFolder);

        $directoryList = $this->getDirectoryList();
        if (!$directoryList) {
            return;
        }

        $tmpDir = $backupFolder
            . DIRECTORY_SEPARATOR
            . self::BACKUP_FILE_PREFIX
            . '_'
            . $this->logHelper->getNow('Y-m-d_H-i-s');

        $backupFileName = $tmpDir . '.' . $this->archiveFileExtension;

        try {
            foreach ($directoryList as $code => $dateDirectories) {
                $codeDir = $tmpDir . DIRECTORY_SEPARATOR . $code;
                foreach ($dateDirectories as $date => $directory) {
                    $dateDir = $codeDir . DIRECTORY_SEPARATOR . $date;
                    $files = FilesystemGlob::glob($directory . '/*.txt');
                    if ($files) {
                        $this->checkDirOrCreate($dateDir);
                        foreach ($files as $file) {
                            $this->io->cp($file, $dateDir . DIRECTORY_SEPARATOR . basename($file));
                        }
                    }
                }
            }

            if (!$this->archive->pack($tmpDir, $backupFileName)) {
                throw new \Exception('Could not create an archive.');
            }

            if ($this->postArchive instanceof ArchiveInterface) {
                $postBackupFileName = $backupFileName . '.' . $this->postArchiveFileExtension;
                try {
                    if (!$this->postArchive->pack($backupFileName, $postBackupFileName)) {
                        throw new \Exception('Could not create an archive.');
                    }
                } catch (\Throwable $e) {
                    throw $e;
                } finally {
                    $this->io->rm($backupFileName);
                }
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            //remove temporary directory that was archived
            if (is_dir($tmpDir)) {
                $this->io->rmdir($tmpDir, true);
            }
        }

        //remove original log files
        foreach ($directoryList as $code => $dateDirectories) {
            foreach ($dateDirectories as $date => $directory) {
                $this->io->rmdir($directory, true);
            }
        }
    }
}
