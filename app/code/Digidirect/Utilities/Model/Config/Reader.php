<?php
namespace Digidirect\Utilities\Model\Config;

use Magento\Framework\Config\Reader\Filesystem;

/**
 * Class Reader
 * @package Digidirect\Utilities\Model\Config
 */
class Reader extends Filesystem
{
    /**
     * Merge all files config
     *
     * @param [] $fileList
     * @param null $configMerger
     * @return \Magento\Framework\Config\Dom|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _mergeConfig($fileList, $configMerger = null)
    {
        /** @var \Magento\Framework\Config\Dom $configMerger */

        foreach ($fileList as $key => $content) {
            try {
                if (!$configMerger) {
                    $configMerger = $this->_createConfigMerger($this->_domDocumentClass, $content);
                } else {
                    $configMerger->merge($content);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XML in file %1:\n%2", [$key, $e->getMessage()])
                );
            }
        }

        return $configMerger;
    }

    /**
     * Read files
     *
     * @param array $fileList
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _readFiles($fileList)
    {
        /** @var \Magento\Framework\Config\Dom $configMerger */
        $configMerger = $this->_mergeConfig($fileList);

        $customFileList = $this->_fileResolver->getFilesFromCustomDirectory($this->_fileName);
        $configMerger = $this->_mergeConfig($customFileList, $configMerger);

        if ($this->validationState->isValidationRequired()) {
            $errors = [];
            if ($configMerger && !$configMerger->validate($this->_schemaFile, $errors)) {
                $message = "Invalid Document \n";
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase($message . implode("\n", $errors))
                );
            }
        }

        $output = [];
        if ($configMerger) {
            $output = $this->_converter->convert($configMerger->getDom());
        }
        return $output;
    }
}
