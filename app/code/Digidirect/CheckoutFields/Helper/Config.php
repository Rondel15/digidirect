<?php

namespace Digidirect\CheckoutFields\Helper;

use Digidirect\CheckoutFields\Block\Adminhtml\System\Config\Fields;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Unserialize\Unserialize;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 *
 * @package Digidirect\CheckoutFields\Helper
 */
class Config
{
    const CHECKOUT_FIELDS = 'checkout_fields/checkout_fields/checkout_fields';
    const CHECKOUT_FIELDS_ENABLE = 'checkout_fields/checkout_fields/enable_checkout_fields';

    const FIELDSET_ID_QUOTE = 'custom_quote_checkout_fields';
    const FIELDSET_ASPECT_TO_QUOTE = 'to_quote';

    const FIELDSET_ID_ORDER = 'custom_order_checkout_fields';
    const FIELDSET_ASPECT_FROM_ORDER = 'from_order';
    const FIELDSET_ASPECT_FROM_QUOTE = 'from_quote';

    /**
     * @var ScopeConfigInterface|null
     */
    protected $scopeConfig = null;

    /**
     * @var Unserialize
     */
    protected $_unserializer;

    /**
     * @var StoreManagerInterface|Store
     */
    protected $appState;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Unserialize $unserialize
     * @param State $state
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Unserialize $unserialize,
        State $state
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_unserializer = $unserialize;
        $this->appState = $state;
    }

    /**
     * @param int|null $scopeId
     * @return array
     */
    public function getCheckoutFields($scopeId)
    {
        $value = $this->scopeConfig->getValue(self::CHECKOUT_FIELDS, ScopeInterface::SCOPE_STORE, $scopeId);
        if (!$value) {
            return [];
        }
        try {
            $value = unserialize($value);
        } catch (\Exception $e) {
            return [];
        }

        return (array)$value;
    }

    /**
     * @param int|null $scopeId
     * @return array
     */
    public function getActiveCheckoutFields($scopeId)
    {
        $fields = $this->getCheckoutFields($scopeId);
        $result = [];
        foreach ($fields as $code => $options) {
            if ($this->isFieldActive($options)) {
                $result[$code] = $options;
            }
        }

        return $result;
    }

    /**
     * @param [] $options
     * @return bool
     */
    protected function isFieldActive(array $options = [])
    {
        $hideOnStorefront = $options[Fields::HIDE_ON_STOREFRONT] ?? false;
        if ($this->appState->getAreaCode() == Area::AREA_ADMINHTML) {
            $hideOnStorefront = false;
        }
        $active = $options[Fields::ACTIVE] ?? false;

        return $active && !$hideOnStorefront;
    }

    /**
     * @param int|null $scopeId
     * @return array
     */
    public function getActivePDPFields($scopeId)
    {
        $fields = $this->getCheckoutFields($scopeId);
        $result = [];
        foreach ($fields as $code => $options) {
            if ($this->isFieldActive($options) && $this->isFieldShowOnPDP($options)) {
                $result[$code] = $options;
            }
        }

        return $result;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function isFieldShowOnPDP(array $options = [])
    {
        return $options[Fields::SHOW_ON_PDP] ?? false;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CHECKOUT_FIELDS_ENABLE);
    }

    /**
     * @param string $fieldId
     * @param string $scope
     * @return bool
     */
    public function isAllowEditOnODP($fieldId, $scope)
    {
        $fields = $this->getCheckoutFields($scope);
        $field = $fields[$fieldId] ?? [];
        return $field[Fields::ALLOW_EDIT_ON_ORDER_DETAIL_PAGE] ?? false;
    }
}
