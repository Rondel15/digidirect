<?php
namespace Digidirect\ProductOverlay\Model\Overlay\Validator;

use Digidirect\ProductOverlay\Model\Overlays;
use Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\StockStatus;

/**
 * Class Stock
 * @package Digidirect\ProductOverlay\Model\Overlay\Validator
 */
class Stock implements \Zend_Validate_Interface
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Overlays $value
     * @return bool
     */
    public function isValid($value)
    {
        $stockStatus = $value->getStockStatus() == StockStatus::CUSTOM_STOCK;
        if (!$stockStatus) {
            return true;
        }

        $stockFrom = $value->getStockFrom();
        $stockTo = $value->getStockTo();

        if ($stockFrom && $stockTo && $stockFrom > $stockTo) {
            array_push($this->messages, __('"Stock From" can not be more than "Stock To"'));
            return false;
        }

        return true;
    }
}
