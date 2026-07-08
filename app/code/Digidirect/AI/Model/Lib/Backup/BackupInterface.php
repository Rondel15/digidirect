<?php

/**
 * Interface for work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Digidirect\AI\Model\Lib\Backup;

/**
 * @api
 */
/**
 * Interface BackupInterface
 * @package Digidirect\AI\Model\Lib\Backup
 */
interface BackupInterface
{
    /**
     * Set Path for Archive File
     * @param string $filePath
     * @return mixed
     */
    public function setArchiveFilePath(string $filePath);

    /**
     * Set is remove original files after adding to archive
     * @param bool $removeOriginal
     * @return mixed
     */
    public function setRemoveOriginal(bool $removeOriginal);

    /** Add File path that will be added to Archive
     * @param string $file
     * @return mixed
     */
    public function addFileToArchive(string $file);

    /**
     * Create Archive Command
     * @return mixed
     */
    public function createArchive();
}
