<?php
namespace Digidirect\CheckoutFields\Model\Config;

use Magento\Framework\Config\Reader\Filesystem;

/**
 * Class Reader
 * @package Digidirect\CheckoutFields\Model\Config
 */
class Reader extends \Digidirect\Utilities\Model\Config\Reader
{
    /**
     * @var array
     */
    protected $_idAttributes = ['/config/field' => 'id'];
}
