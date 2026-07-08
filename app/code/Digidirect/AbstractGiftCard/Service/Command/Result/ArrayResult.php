<?php

namespace Digidirect\AbstractGiftCard\Service\Command\Result;

use Digidirect\AbstractGiftCard\Service\Command\ResultInterface;

class ArrayResult implements ResultInterface
{
    /**
     * @var array
     */
    private $_array;

    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        $this->_array = $array;
    }

    /**
     * Returns result interpretation
     *
     * @return array
     */
    public function get()
    {
        return $this->_array;
    }
}
