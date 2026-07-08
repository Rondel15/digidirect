<?php
namespace Digidirect\AddressVerification\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 * @package Digidirect\AddressVerification\Model\Checkout
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Digidirect\AddressVerification\Helper\Aupost
     */
    protected $autocompleteHelper;

    /**
     * ConfigProvider constructor.
     * @param \Digidirect\AddressVerification\Helper\Autocomplete $autocompleteHelper
     */
    public function __construct(
        \Digidirect\AddressVerification\Helper\Autocomplete $autocompleteHelper
    ) {
        $this->autocompleteHelper = $autocompleteHelper;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return ['aupost_enabled' => $this->autocompleteHelper->isAuPostEnabled() ? 'on' : 'off'];
    }
}
