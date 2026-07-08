<?php

namespace Digidirect\Vii\Model\Service;

/**
 * Class Adapter
 * @package Digidirect\Vii\Model\Service
 */
class Adapter extends \Digidirect\AbstractGiftCard\Model\Service\Adapter
{
    /**
     * @var null|string
     */
    protected $transId = null;

    /**
     * @var null
     */
    protected $availableBalance = null;

    /**
     * @return |null
     */
    public function getAvailableBalance()
    {
        return $this->availableBalance;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setAvailableBalance($value)
    {
        $this->availableBalance = $value;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastTransId()
    {
        return $this->transId;
    }

    /**
     * @param string $transId
     * @return $this
     */
    public function setLastTransId($transId)
    {
        $this->transId = $transId;
        return $this;
    }

    /**
     * @return bool
     */
    public function canUndo()
    {
        return $this->_canPerformCommand('undo');
    }

    /**
     * @param string $transId
     * @return $this
     */
    public function undo($transId)
    {
        $this->_executeCommand('undo', ['service' => $this, 'trans_id' => $transId]);
        return $this;
    }
}
