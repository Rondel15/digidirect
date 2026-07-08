<?php

namespace Digidirect\CheckoutFields\Model\Config;

/**
 * Class Data
 * @package Digidirect\CheckoutFields\Model\Config
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Get checkout fields
     *
     * @return array|mixed|null
     */
    public function getFields()
    {
        return $this->get('checkout_fields');
    }
}
