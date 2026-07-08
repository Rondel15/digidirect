<?php

namespace Digidirect\Localization\Model;

use Digidirect\Localization\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as jsonHelper;
use Magento\Directory\Model\CountryFactory;

class Configuration
{

    /**
     * Phone codes mask configuration
     */
    const XML_PATH_PHONE_CODES = 'digidirect_localization/localization/phone_codes';

    /**
     * Phone codes mask prefix
     */
    const MASK_PREFIX = 'mask_prefix';

    /**
     * Phone codes mask pattern
     */
    const MASK_PATTERN = 'mask_pattern';

    /**
     * Phone codes mask placeholder
     */
    const MASK_PLACEHOLDER = 'mask_placeholder';

    /**
     * Phone codes country code
     */
    const COUTNRY = 'country';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * Configuration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param jsonHelper $_jsonHelper
     * @param Data $dataHelper
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        jsonHelper $_jsonHelper,
        Data $dataHelper,
        CountryFactory $countryFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_jsonHelper = $_jsonHelper;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Get phone codes from admin settings
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return string
     */
    protected function _getPhoneCodes($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PHONE_CODES,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param $countryCode
     * @return mixed
     */
    public function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     * Returns formatted phone codes
     *
     * @return array
     */
    protected function _getFormattedPhoneCodes()
    {
        $result = [];
        if (!$this->dataHelper->isPhoneSuggestionEnabled()) {
            return $result;
        }
        if ($this->_getPhoneCodes()) {
            $phoneCodes = $this->_jsonHelper->unserialize($this->_getPhoneCodes());
            if (!empty($phoneCodes)) {
                foreach ($phoneCodes as $code) {
                    $countryName = $this->getCountryName($code['country']);
                    $result[$code['country']] = [
                        'prefix' => $code['mask_prefix'],
                        'pattern' => $code['mask_pattern'],
                        'placeholder' => $code['mask_placeholder'],
                        'countryName' => $countryName
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Returns string with json config for phone codes
     *
     * @return string
     */
    public function getPhoneJsConfig()
    {
        return $this->_jsonHelper->serialize($this->_getFormattedPhoneCodes());
    }
}
