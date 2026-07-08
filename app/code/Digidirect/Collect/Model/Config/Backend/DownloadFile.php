<?php

namespace Digidirect\Collect\Model\Config\Backend;

class DownloadFile extends \Magento\Config\Model\Config\Backend\File
{

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    public function _getAllowedExtensions()
    {
        return ['csv'];
    }
}
