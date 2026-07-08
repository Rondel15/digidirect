<?php

namespace Digidirect\AbstractGiftCard\Service\Helper;

class ErrorMapper
{
    /**
     * @var array
     */
    protected $_mappingArray = [];

    /**
     * ErrorMapper constructor.
     * @param array $mappingArray
     */
    public function __construct(array $mappingArray = [])
    {
        $this->_mappingArray = $mappingArray;
    }

    /**
     * @param string|int $code
     * @return bool|string
     */
    public function getErrorMessageByCode($code)
    {
        if (isset($this->_mappingArray[$code])) {
            return $this->_mappingArray[$code];
        }
        return __('Something went wrong');
    }
}
