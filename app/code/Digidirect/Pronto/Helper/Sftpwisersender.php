<?php

namespace Digidirect\Pronto\Helper;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Filesystem\Io\File;

use Magento\Framework\App\Helper\AbstractHelper;

class Sftpwisersender extends AbstractHelper
{
    protected $sftp;
    protected $file;
    protected $directoryList;

    public function __construct(Sftp $sftp,File $file, \Magento\Framework\Filesystem\DirectoryList $directoryList)
    {
        $this->sftp = $sftp;
        $this->file = $file;
        $this->directoryList = $directoryList;
    }

    public function sendFile()
    {

        $destinationPath = '/uploads/wiserdata.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/export/wiserdata.csv';
        $sftpConfig = [
            'host' => 'sftp.360pi.com',
            'port' => '22',
            'username' => 'digidirect',
            'password' => '#uIHOZNk3&e9677c'
        ];

        try {
            $this->sftp->open($sftpConfig);
            $this->sftp->write($destinationPath, $this->file->read($filePath));
            $this->sftp->close();
            echo "send it";
            return true;
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the file transfer
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
