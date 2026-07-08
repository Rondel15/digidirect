<?php
namespace Digidirect\CheckoutFields\Api\Data;

/**
 * Interface CronActionInterface
 * @package Digidirect\CheckoutFields\Api\Data
 */
interface CronActionInterface
{
    /**
     * @return void
     */
    public function runAction();

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getName();
}
