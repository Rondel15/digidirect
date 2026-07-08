<?php

namespace Digidirect\AbstractGiftCard\Service\Command\Result;

use Digidirect\AbstractGiftCard\Service\Command\ResultInterface;

/**
 * Class BoolResult
 */
class BoolResult implements ResultInterface
{
    /**
     * @var array
     */
    private $_result;

    /**
     * Constructor
     *
     * @param bool $result
     */
    public function __construct($result = true)
    {
        $this->_result = $result;
    }

    /**
     * Returns result interpretation
     *
     * @return mixed
     */
    public function get()
    {
        return (bool) $this->_result;
    }
}
