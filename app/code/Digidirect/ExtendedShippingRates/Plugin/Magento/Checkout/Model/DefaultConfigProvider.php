<?php
namespace Digidirect\ExtendedShippingRates\Plugin\Magento\Checkout\Model;

use Digidirect\ExtendedShippingRates\Model\Carrier\Shipping;

/**
 * Class DefaultConfigProvider
 * @package Digidirect\ExtendedShippingRates\Magento\Checkout\Model
 */
class DefaultConfigProvider
{
    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        if (!in_array(Shipping::SHIPPING, $result['activeCarriers'])) {
            array_push($result['activeCarriers'], Shipping::SHIPPING);
        }
        return $result;
    }
}
