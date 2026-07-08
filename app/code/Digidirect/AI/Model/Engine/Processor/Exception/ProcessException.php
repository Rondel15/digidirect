<?php

namespace Digidirect\AI\Model\Engine\Processor\Exception;

class ProcessException extends \Exception
{
    /**
     * Stopper
     *
     * @var integer
     */
    protected $_stopper;

    /**
     * Class constructor
     *
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     * @param int $stopper
     */
    public function __construct(
        $message,
        $code = 0,
        \Throwable $previous = null,
        $stopper = 0
    ) {
        parent::__construct($message, $code, $previous);
        $this->_stopper = $stopper;
    }

    /**
     * Get stopper state
     *
     * @return int
     */
    public function getIsStopper()
    {
        return $this->_stopper;
    }
}
