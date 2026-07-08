<?php
namespace Digidirect\AddressVerification\Block;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Digidirect\AddressVerification\Helper\Autocomplete as AddressAutocompleteHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class AddressVerification
 * @package Digidirect\AddressVerification\Block
 */
class AddressAutocomplete extends Template
{
    /**
     * @var AddressVerificationHelper|AddressAutocompleteHelper
     */
    protected $addressAutocompleteHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * AddressAutocomplete constructor.
     * @param Template\Context $context
     * @param AddressAutocompleteHelper $helper
     * @param JsonHelper $jsonHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        AddressAutocompleteHelper $helper,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addressAutocompleteHelper = $helper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Get config contains api key, countries to auto suggest and fields to apply
     *
     * @return string
     */
    public function getConfig()
    {
        $config['gplaces_config'] = [];
        if ($this->addressAutocompleteHelper->isGoogleEnabled()) {
            $config = $this->_data['gplaces_config'] ?? [];
            $config['api_key'] = $this->addressAutocompleteHelper->getApiKey();
            $config['countries'] = $this->addressAutocompleteHelper->getAllowedCountries();
            $config['default_country'] = $this->addressAutocompleteHelper->getDefaultCountry();
        }

        $config = new DataObject($config);

        $this->_eventManager->dispatch('address_autocomplete_get_config_after', ['config' => $config]);

        return $this->jsonHelper->jsonEncode($config);
    }

    /**
     * @return string
     */
    public function getAupostSearchUrl()
    {
        return $this->getUrl('autocomplete/aupost/search');
    }

    /**
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('autocomplete/aupost/validate');
    }
}
