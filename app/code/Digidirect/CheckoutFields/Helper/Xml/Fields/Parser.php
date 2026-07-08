<?php

namespace Digidirect\CheckoutFields\Helper\Xml\Fields;

use \Digidirect\CheckoutFields\Helper\Config;
use Digidirect\CheckoutFields\Model\Component\Type\AbstractType;
use Digidirect\CheckoutFields\Model\Config\Data as FieldsConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use \Magento\Framework\Module\Dir\Reader;
use \Magento\Framework\Xml\Parser as MagentoParser;
use \Digidirect\CheckoutFields\Model\Condition\ConditionInterface;

/**
 * Class Parser
 * @package Digidirect\CheckoutFields\Helper\Xml\Fields
 */
class Parser
{
    const XML_LOGGED_IN = 'fieldset_customer_logged';

    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * @var MagentoParser
     */
    protected $fieldsConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var null|array
     */
    protected $fields = null;

    /**
     * @var array
     */
    protected $conditions;

    /**
     * @var array
     */
    protected $validationRuleMapping;

    /**
     * Parser constructor.
     *
     * @param FieldsConfig        $fieldsConfig
     * @param Config              $config
     * @param array               $conditions
     * @param array               $validationRuleMapping
     */
    public function __construct(
        FieldsConfig $fieldsConfig,
        Config $config,
        array $conditions = [],
        array $validationRuleMapping = []
    ) {
        $this->config = $config;
        $this->fieldsConfig = $fieldsConfig;
        $this->conditions = $conditions;
        $this->validationRuleMapping = $validationRuleMapping;
    }

    /**
     * @return Session
     */
    public function getCustomerSession()
    {
        return ObjectManager::getInstance()->get(Session::class);
    }

    /**
     * @param string|null $scopeId
     * @param bool $isPdpFields
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getFields($scopeId = null, $isPdpFields = false)
    {
        if (!$this->config->isEnabled()) {
            return [];
        }
        if (null === $this->fields) {
            $activeFields = $isPdpFields ?
                $this->config->getActivePDPFields($scopeId) : $this->config->getActiveCheckoutFields($scopeId);
            $allFields = $this->getAllFields();

            foreach ($allFields as $code => $field) {
                if (!(isset($activeFields[$code]) && $this->isFieldActive($code, $activeFields[$code]))) {
                    unset($allFields[$code]);
                }
            }
            $this->fields = $allFields;
        }
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getAllFields()
    {
        $parsedArray = $this->fieldsConfig->getFields();
        if (isset($parsedArray['fields'])) {
            return $this->loggedCustomerReplaceFieldset((array)$parsedArray['fields']);
        }
        return [];
    }

    /**
     * @param string $fieldId
     * @return bool|array
     */
    public function getFieldXml($fieldId)
    {
        $fields = $this->getAllFields();
        return $fields[$fieldId] ?? false;
    }

    /**
     * @param string $code
     * @param array $options
     * @return bool
     */
    public function isFieldActive($code, $options = [])
    {
        foreach ($this->conditions as $condition) {
            /** @var ConditionInterface $condition */
            if (!$condition instanceof ConditionInterface) {
                continue;
            }

            if (!$condition->isValid($code, $options)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a string such as 'required-entry validate-number'.
     *
     * @param array $field
     * @return null|string
     */
    public function getValidationClassesHtml(array $field)
    {
        return array_reduce(
            $field['validation']['rule'] ?? [],
            function ($carry, $item) {
                if (empty($item['_attribute']['name']) && empty($item['name'])) {
                    return $carry;
                }
                $name = $item['name'] ?? $item['_attribute']['name'];
                return $carry . ' ' . $name;
            }
        );
    }

    /**
     * @param array $field
     * @return bool
     */
    public function getDependsField(array $field)
    {
        return $field[AbstractType::XML_DEPENDS][AbstractType::XML_DEPENDS_FIELD_ID] ?? false;
    }

    /**
     * @param string $fieldId
     * @return string|bool
     */
    public function getDependsFieldByFieldId($fieldId)
    {
        $xmlArray = $this->getFieldXml($fieldId);
        return $xmlArray ? $this->getDependsField($xmlArray) : false;
    }

    /**
     * @param array $field
     * @return array
     */
    public function getValidationClasses(array $field)
    {
        $config = [];
        if (isset($field[AbstractType::XML_VALIDATION]['rule'])) {
            $rules = $field[AbstractType::XML_VALIDATION]['rule'];
            if (isset($rules['_value'])) {
                $config = array_merge($config, $this->prepareValidationRuleConfig($rules));
            } else {
                foreach ($field[AbstractType::XML_VALIDATION]['rule'] as $rule) {
                    if (isset($rule['_value'])) {
                        $config = array_merge($config, $this->prepareValidationRuleConfig($rule));
                    }
                }
            }
        }
        return $config;
    }

    /**
     * @param array $rule
     * @return array
     */
    public function prepareValidationRuleConfig($rule)
    {
        $config = [];
        if (isset($rule['_attribute']['name'])) {
            $name = ($this->validationRuleMapping[$rule['_attribute']['name']]) ?? $rule['_attribute']['name'];
            $config[$name] = [
                'active' => (bool)$rule['_value'],
                'value' => $rule['_value']
            ];
        }
        return $config;
    }

    /**
     * @return mixed
     */
    protected function loggedCustomerReplaceFieldset(array $fields)
    {
        $isLoggedIn = $this->getCustomerSession()->isLoggedIn();
        foreach ($fields as &$fieldOptions) {
            if (!$isLoggedIn || !isset($fieldOptions[AbstractType::XML_AREA][self::XML_LOGGED_IN])) {
                continue;
            }
            $fieldOptions[AbstractType::XML_AREA][AbstractType::XML_FIELDSET]
                = $fieldOptions[AbstractType::XML_AREA][self::XML_LOGGED_IN];
            unset($fieldOptions[AbstractType::XML_AREA][self::XML_LOGGED_IN]);
        }

        return $fields;
    }
}
