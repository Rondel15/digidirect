<?php
namespace Digidirect\Collect\Api;

/**
 * Interface CollectAddToCartValidationInterface
 * @package Digidirect\Collect\Api
 */
interface CollectAddToCartValidationInterface
{
    /**
     * @return string
     */
    public function hasCollectItemInCart();
}
