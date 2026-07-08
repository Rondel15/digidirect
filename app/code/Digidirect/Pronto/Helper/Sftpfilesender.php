<?php

namespace Digidirect\Pronto\Helper;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Filesystem\Io\File;

use Magento\Framework\App\Helper\AbstractHelper;

class Sftpfilesender extends AbstractHelper
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

        $destinationPath = '/Import/Magento/mg_productmediagallery.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/export/mg_productmediagallery.csv';
        $sftpConfig = [
            'host' => 'mcj-w3r6klkpyd20t4htp-mw140m.ftp.marketingcloudops.com',
            'port' => '22',
            'username' => '534005454_2',
            'password' => 'q%F9#DNDfeT#A9@2'
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
