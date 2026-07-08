<?php
namespace Digidirect\AI\Model\Integrations\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = ['/integrations/integration' => 'name'];
}
