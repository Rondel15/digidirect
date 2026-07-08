<?php
namespace Digidirect\Collect\Api;

/**
 * Interface DeliveryAddToCartValidation
 * @package Digidirect\Collect\Api
 */
interface DeliveryAddToCartValidationInterface
{
    /**
     * @return string
     */
    public function hasDeliveryItemInCart();
}
