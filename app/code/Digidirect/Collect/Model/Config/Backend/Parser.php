<?php

namespace Digidirect\Collect\Model\Config\Backend;

use Digidirect\Collect\Model\PostCode as PostCodeModel;

/**
 * Class Parser
 *
 * @package Digidirect\Class\Model\Import\Source
 */
class Parser
{
    const DIRECTORY = 'collect';

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var int
     */
    protected $websiteId;

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileSystemDriver;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * Parser constructor.
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $fileSystemDriver
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileSystemDriver
    ) {
        $this->fileSystemDriver = $fileSystemDriver;
        $this->directoryList = $directoryList;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function parse()
    {
        $content = $this->fileGetContents();
        $rows = explode("\n", $content);
        $result = [];
        $countRows = count($rows);
        for ($i = 1; $i < $countRows; ++$i) {
            $data = $rows[$i];
            if (!empty($data)) {
                $data = preg_replace('/[\x00-\x1F\x7F]/', '', $data);
                $rowData = explode(',', $data);
                $result[] = [
                    PostCodeModel::TABLE_COLUMN_POST_CODE => $rowData[0],
                    PostCodeModel::TABLE_COLUMN_LOCALITY => $rowData[1],
                    PostCodeModel::TABLE_COLUMN_STATE => $rowData[2],
                    PostCodeModel::TABLE_COLUMN_COMMENTS => $rowData[3],
                    PostCodeModel::TABLE_COLUMN_CATEGORY => $rowData[4],
                    PostCodeModel::TABLE_COLUMN_LONGITUDE => $rowData[5],
                    PostCodeModel::TABLE_COLUMN_LATITUDE => $rowData[6]
                ];
            }
        }

        return $result;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $websiteId;

        return $this;
    }

    /**
     * @param int $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function fileGetContents()
    {
        return $this->fileSystemDriver->fileGetContents($this->getAbsoluteFilePath());
    }

    /**
     * @return string
     */
    protected function getAbsoluteFilePath()
    {
        return $this->directoryList->getPath('media')
            . DIRECTORY_SEPARATOR . self::DIRECTORY . DIRECTORY_SEPARATOR . $this->file;
    }
}
