<?php
namespace Digidirect\AddressVerification\Model\Config\Source;

/**
 * Class PostcodeRange
 * @package Digidirect\AddressVerification\Model\Config\Source
 */
class PostcodeRange
{
    /**
     * @var array
     */
    protected $list;

    /**
     * PostcodeRange constructor.
     * @param array $list
     */
    public function __construct($list = [])
    {
        $this->list = (array)$list;
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }
}
