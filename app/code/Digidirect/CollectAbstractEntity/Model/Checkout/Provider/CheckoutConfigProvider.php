<?php

namespace Digidirect\CollectAbstractEntity\Model\Checkout\Provider;

use Magento\Directory\Model\Data\RegionInformation;

/**
 * Class CheckoutConfigProvider
 *
 * @package Digidirect\CollectAbstractEntity\Model\Checkout\Provider
 */
class CheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const PREFILL_SHIPPING_FIELDS = 'collect_prefill_shipping_fields';
    const REGION_FIELD = 'region';

    /**
     * @var \Digidirect\CollectAbstractEntity\Helper\Config
     */
    protected $configHelper;

    /**
     * CheckoutConfigProvider constructor.
     *
     * @param \Digidirect\CollectAbstractEntity\Helper\Config $configHelper
     */
    public function __construct(\Digidirect\CollectAbstractEntity\Helper\Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $prefillFields = $this->configHelper->getPrefillShippingAddressFieldsMatrix();
        $prefillFieldsKeys = array_keys($prefillFields);
        if (isset($prefillFields[self::REGION_FIELD])) {
            array_push($prefillFieldsKeys, RegionInformation::KEY_REGION_ID);
        }
        return [
            'quoteData' => [
                self::PREFILL_SHIPPING_FIELDS => $prefillFieldsKeys
            ]
        ];
    }
}
