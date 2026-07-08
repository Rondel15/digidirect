<?php

namespace Digidirect\AI\Model\Lib\Backup;

use Digidirect\AI\Model\Engine\Processor\Exception\ProcessException;

/**
 * Class Backup
 * @package Digidirect\AI\Model\Lib\Backup
 */
class Backup implements \Digidirect\AI\Model\Lib\Backup\BackupInterface
{
    /**
     * @var bool
     */
    protected $removeOriginal = false;

    /**
     * @var string
     */
    protected $archiveFilePath;

    /**
     * @var string
     */
    protected $archiveTempFolder;

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * @var mixed
     */
    protected $archive;

    /**
     * Backup constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param null $archiveType
     * @throws \Exception
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem\Io\File $io,
        $archiveType
    ) {
        $this->io = $io;
        $this->archive = $archiveType;
        if (isset($archiveType['instanceObject'])) {
            $this->archive = $archiveType['instanceObject'];
        } else {
            $this->archive = $archiveType['instance'];
        }
    }

    /**
     * @param string $filePath
     * @throws ProcessException
     * @return void
     */
    public function setArchiveFilePath(string $filePath)
    {
        if (!trim($filePath)) {
            throw new ProcessException('Wrong archive file path');
        }
        $this->archiveFilePath = $filePath;
    }

    /**
     * @param bool $removeOriginal
     * @return void
     */
    public function setRemoveOriginal(bool $removeOriginal)
    {
        $this->removeOriginal = $removeOriginal;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function addFileToArchive(string $file)
    {
        $this->files[$file] = $file;
        return true;
    }

    /**
     * @return bool
     * @throws ProcessException
     */
    public function createArchive()
    {
        $dirName = dirname($this->archiveFilePath);
        $this->archiveTempFolder = $dirName . DIRECTORY_SEPARATOR . 'temp';

        if (!is_dir($this->archiveTempFolder) && !$this->io->mkdir($this->archiveTempFolder)) {
            $msg = __('Can not create Folder for Logs File Backups. ') . $dirName;
            throw new ProcessException($msg);
        }

        foreach ($this->files as $file) {
            $this->addToTempBackupFolder($file);
        }

        if (!$this->pack()) {
            return false;
        }

        $this->removeTempFolder();

        if ($this->removeOriginal) {
            $this->removeMovedToArchive();
        }

        $this->files = [];

        return true;
    }

    /**
     * @return bool
     */
    protected function removeMovedToArchive()
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    /**
     * @param string $filePath
     * @return bool
     * @throws ProcessException
     */
    protected function addToTempBackupFolder($filePath)
    {
        $r = copy($filePath, $this->archiveTempFolder . DIRECTORY_SEPARATOR . basename($filePath));
        if (!$r) {
            throw new ProcessException('Impossible move file to temporary folder');
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function pack()
    {
        if ($this->archive->pack($this->archiveTempFolder, $this->archiveFilePath)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function removeTempFolder()
    {
        $files = scandir($this->archiveTempFolder);
        foreach ($files as $file) {
            $fileName = $this->archiveTempFolder . DIRECTORY_SEPARATOR . $file;
            if (is_file($fileName)) {
                unlink($fileName);
            }
        }
        return true;
    }
}
