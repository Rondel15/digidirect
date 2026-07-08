<?php

namespace Digidirect\Locator\Model\Config;

class Reader extends \Digidirect\Utilities\Model\Config\Reader
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = ['/config/entity' => 'name'];
}
