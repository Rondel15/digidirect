<?php
namespace Digidirect\AddressVerification\Model\Import;

/**
 * Class AbstractSource
 * @package Digidirect\AddressVerification\Model\Import
 */
abstract class AbstractSource implements SourceAdapterInterface
{
    const DIRECTORY = 'addressautocomplete';

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileSystemDriver;

    /**
     * AbstractSource constructor.
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $fileSystemDriver
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileSystemDriver
    ) {
        $this->directoryList = $directoryList;
        $this->fileSystemDriver = $fileSystemDriver;
    }

    /**
     * @var string
     */
    protected $file;

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
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
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
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
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
        .  DIRECTORY_SEPARATOR  . self::DIRECTORY . DIRECTORY_SEPARATOR . $this->file;
    }
}
