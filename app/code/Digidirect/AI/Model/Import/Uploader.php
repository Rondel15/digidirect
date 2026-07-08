<?php
namespace Digidirect\AI\Model\Import;

class Uploader extends \Magento\CatalogImportExport\Model\Import\Uploader
{
    /**
     * Proceed moving a file from TMP to destination folder
     *
     * @param string $fileName
     * @param bool $renameFileOff
     * @return array
     */
    public function move($fileName, $renameFileOff = false)
    {
        if ($renameFileOff) {
            $this->setAllowRenameFiles(false);
        }
        if (preg_match('/\bhttps?:\/\//i', $fileName, $matches)) {
            return parent::move($fileName, $renameFileOff);
        }

        if (preg_match('/\bftps?:\/\//i', $fileName, $matches)) {
            $file = @file_get_contents($fileName);
            if (empty($file)) {
                return [];
            }
            $fileName = preg_replace('/[^a-z0-9\._-]+/i', '', $fileName);
            $this->_directory->writeFile(
                $this->_directory->getRelativePath($this->getTmpDir() . '/' . $fileName),
                $file
            );
        }

        $filePath = $this->_directory->getRelativePath($this->getTmpDir() . '/' . $fileName);
        $this->_setUploadFile($filePath);
        $destDir = $this->_directory->getAbsolutePath($this->getDestDir());
        $result = $this->save($destDir);
        $result['name'] = self::getCorrectFileName($result['name']);
        return $result;
    }
}
