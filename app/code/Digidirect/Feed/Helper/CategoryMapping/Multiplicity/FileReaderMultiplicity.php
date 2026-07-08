<?php

namespace Digidirect\Feed\Helper\CategoryMapping\Multiplicity;

use \Magento\Framework\Module\Dir\Reader as DirReader;
use \Digidirect\Feed\Helper\CategoryMapping\FileReaderFactory;

class FileReaderMultiplicity extends ReaderMultiplicity
{
    /**
     * @var DirReader
     */
    protected $dirReader;

    /**
     * @var FileReaderFactory
     */
    protected $fileReaderFactory;

    /**
     * FileReaderMultiplicity constructor.
     * @param DirReader $dirReader
     * @param FileReaderFactory $fileReaderFactory
     */
    public function __construct(
        DirReader $dirReader,
        FileReaderFactory $fileReaderFactory
    ) {
        $this->dirReader = $dirReader;
        $this->fileReaderFactory = $fileReaderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $mappingPath = $this->getMappingPath();

        foreach (glob($mappingPath . "/*.txt") as $filename) {
            /** @var \Digidirect\Feed\Helper\CategoryMapping\FileInterface $fileReader */
            $fileReader = $this->getReader();
            $this->addItem($fileReader->setFile($filename));
        }

        return $this;
    }

    /**
     * @return \Digidirect\Feed\Helper\CategoryMapping\FileInterface
     */
    protected function getReader()
    {
        return $this->fileReaderFactory->create();
    }

    /**
     * @return string
     */
    protected function getMappingPath()
    {
        return realpath($this->dirReader->getModuleDir('etc', 'Digidirect_Feed') . '/../Setup/data/mapping/');
    }
}
