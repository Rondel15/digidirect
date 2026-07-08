<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class FtpProtocol implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('FTP / FTPS'),
                'value' => 'ftp',
            ],
            [
                'label' => __('SFTP'),
                'value' => 'sftp',
            ],
        ];
    }
}
