<?php
namespace Digidirect\ProductOverlay\Model\File;

/**
 * Class Uploader
 * @package Digidirect\ProductOverlay\Model\File
 */
class Uploader extends \Magento\MediaStorage\Model\File\Uploader
{
    /**
     * Used to check if uploaded file mime type is valid or not
     *
     * @param string[] $validTypes
     * @access public
     * @return bool
     */
    public function checkMimeType($validTypes = [])
    {
        return getimagesize($this->_file['tmp_name']) !== false && parent::checkMimeType($validTypes);
    }
}
