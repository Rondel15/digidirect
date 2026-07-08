<?php

namespace Digidirect\Collect\Model\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var []
     */
    protected $_idAttributes = ['/config/placestorage' => 'name'];
}
