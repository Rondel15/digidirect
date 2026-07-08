<?php
namespace Digidirect\CheckoutFields\Model\Condition;

/**
 * Interface ConditionInterface
 * @package Digidirect\CheckoutFields\Model
 */
interface ConditionInterface
{
    /**
     * @param string $code
     * @param array $options
     * @return bool
     */
    public function isValid($code, $options = []);
}
