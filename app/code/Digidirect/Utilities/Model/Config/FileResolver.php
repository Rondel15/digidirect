<?php
namespace Digidirect\Utilities\Model\Config;

/**
 * Class FileResolver
 * @package Digidirect\Utilities\Model\Config
 */
class FileResolver extends \Magento\Framework\App\Config\FileResolver
{
    const DIGIDIRECT_CUSTOM_DIRECTORY = 'digidirect_xml';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * FileResolver constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        parent::__construct($moduleReader, $filesystem, $iteratorFactory);
        $this->readFactory = $readFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * Get file from custom directory
     *
     * @param string $fileName
     * @return \Magento\Framework\Config\FileIterator
     */
    public function getFilesFromCustomDirectory($fileName)
    {
        /**
         * @var $directory \Magento\Framework\Filesystem\Directory\Read
         */
        $root = $this->directoryList->getRoot();
        $absolutePath = $root . '/' . self::DIGIDIRECT_CUSTOM_DIRECTORY;
        $directory = $this->readFactory->create($absolutePath);
        $absolutePaths = [];
        foreach ($directory->search('{' . $fileName . ',*/' . $fileName . '}') as $path) {
            $absolutePaths[] = $directory->getAbsolutePath($path);
        }
        $iterator = $this->iteratorFactory->create($absolutePaths);
        return $iterator;
    }
}
