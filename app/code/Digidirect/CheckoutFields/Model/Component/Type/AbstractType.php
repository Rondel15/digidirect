<?php

namespace Digidirect\CheckoutFields\Model\Component\Type;

use Digidirect\CheckoutFields\Model\Config\Source\Options;

/**
 * Class AbstractType
 * @package Digidirect\CheckoutFields\Model\Component\Type
 */
abstract class AbstractType
{
    const COMPONENT = 'component';
    const CONFIG = 'config';
    const CUSTOM_SCOPE = 'customScope';
    const TEMPLATE = 'template';
    const ELEMENT_TMPL = 'elementTmpl';
    const OPTIONS = 'options';
    const DATA_SCOPE = 'dataScope';
    const LABEL = 'label';
    const PROVIDER = 'provider';
    const VISIBLE = 'visible';
    const VALIDATION = 'validation';
    const SORT_ORDER = 'sortOrder';
    const ID = 'id';
    const FRONTEND_CLASS = 'class';
    const DEPENDS = 'depends';

    const XML_SORT_ORDER = 'sort_order';
    const XML_ID = 'id';
    const XML_FRONTEND_NAME = 'frontend_name';
    const XML_FRONTEND_INPUT = 'frontend_input';
    const XML_FRONTEND_FORMAT = 'frontend_format';
    const XML_CHECKOUT_STEP = 'checkout_step';
    const XML_OPTIONS = 'options';
    const XML_VALIDATION = 'validation';
    const XML_DEPENDS = 'depends';
    const XML_DEPENDS_ACTION = 'action';
    const XML_DEPENDS_FIELD_ID = 'field_id';
    const XML_AREA = 'area';
    const XML_STEP = 'checkout_step';
    const XML_SCOPE = 'custom_scope';
    const XML_FIELDSET = 'fieldset';

    const PROVIDER_VALUE = 'checkoutProvider';
    const ADDITIONAL_CLASSES = 'additionalClasses';

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @var Options|null
     */
    protected $_sourceOptions = null;

    /**
     * AbstractType constructor.
     * @param Options $sourceOptions
     * @param array $data
     */
    public function __construct(
        \Digidirect\CheckoutFields\Model\Config\Source\Options $sourceOptions,
        array $data = []
    ) {
        $this->_data = $data;
        $this->_sourceOptions = $sourceOptions;
    }

    /**
     * @param string $code
     * @param array $options
     * @return array
     */
    public function getChild($code, $options)
    {
        if (!$code || !$options) {
            return [];
        }
        $resultOptions = $this->getDefaultOptions();
        if (isset($options[self::XML_AREA][self::XML_SCOPE])) {
            $resultOptions[self::CONFIG][self::CUSTOM_SCOPE] = $options[self::XML_AREA][self::XML_SCOPE];
            $resultOptions[self::DATA_SCOPE] = $options[self::XML_AREA][self::XML_SCOPE] . '.' . $code;
        }
        $resultOptions[self::CONFIG][self::ID] = $code;
        $resultOptions[self::ID] = $code;

        $resultOptions[self::LABEL] = $options[self::XML_FRONTEND_NAME];

        if (isset($options[self::XML_SORT_ORDER])) {
            $resultOptions[self::SORT_ORDER] = $options[self::XML_SORT_ORDER];
        }
        if (isset($options[self::FRONTEND_CLASS])) {
            $resultOptions[self::ADDITIONAL_CLASSES] = $options[self::FRONTEND_CLASS];
        }
        if (isset($options[self::XML_DEPENDS])) {
            $resultOptions[self::DEPENDS] = $options[self::XML_DEPENDS];
        }

        $this->_addOptions($options, $resultOptions);
        $this->_addValidation($options, $resultOptions);
        return $resultOptions;
    }

    /**
     * Add options if present
     *
     * @param [] $options
     * @param [] $resultOptions
     * @return void
     */
    protected function _addOptions($options, &$resultOptions)
    {
        if (isset($options[self::XML_OPTIONS]['option'])) {
            $htmlOptions = $options[self::XML_OPTIONS]['option'];
            $xmlOptions = [];
            if (isset($htmlOptions['_attribute'])) {
                $xmlOptions[0]['label'] = $htmlOptions['_attribute']['label'];
                $xmlOptions[0]['value'] = $htmlOptions['_attribute']['value'];
            } else {
                foreach ($options[self::XML_OPTIONS]['option'] as $key => $option) {
                    $xmlOptions[$key]['label'] = $option['_attribute']['label'];
                    $xmlOptions[$key]['value'] = $option['_attribute']['value'];
                }
            }
            $resultOptions[self::XML_OPTIONS] = $xmlOptions;
        }
    }

    /**
     * @param [] $options
     * @param [] $resultOptions
     * @return void
     */
    protected function _addValidation($options, &$resultOptions)
    {
        if (isset($options[self::XML_VALIDATION]['rule'])) {
            $validations = [];
            $rules = $options[self::XML_VALIDATION]['rule'];
            if (isset($rules['_value'])) {
                $validations[$rules['_attribute']['name']] = $rules['_value'];
            } else {
                foreach ($options[self::XML_VALIDATION]['rule'] as $rule) {
                    if (isset($rule['_value'])) {
                        $validations[$rule['_attribute']['name']] = $rule['_value'];
                    }
                }
            }
            $resultOptions[self::VALIDATION] = $validations;
        }
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            self::COMPONENT => $this->getComponent(),
            self::CONFIG => [
                self::CUSTOM_SCOPE => '',
                self::TEMPLATE => 'ui/form/field',
                self::ELEMENT_TMPL => $this->getElementTemplate(),
                self::ID => ''
            ],
            self::DATA_SCOPE => '',
            self::LABEL => '',
            self::PROVIDER => self::PROVIDER_VALUE,
            self::VISIBLE => true,
            self::VALIDATION => [],
            self::SORT_ORDER => 0,
            self::ID => '',
            self::OPTIONS => [],
        ];
    }

    /**
     * @return string
     */
    abstract protected function getComponent();

    /**
     * @return string
     */
    abstract protected function getElementTemplate();

    /**
     * @param array $data
     * @param string $key
     * @return array
     */
    public function prepareFieldConfig($data, $key)
    {
        $config = [
            self::LABEL => $data[self::XML_FRONTEND_NAME],
            'name' => $key
        ];
        $this->_addValidationRule($data, $config);
        if (isset($data[self::XML_OPTIONS]['option'])) {
            $this->_sourceOptions->setOptions($data[self::XML_OPTIONS]['option']);
            $config['values'] = $this->_sourceOptions->toOptionArray();
        }
        return $config;
    }

    /**
     *
     * @param array $data
     * @param array $config
     * @return mixed
     */
    protected function _addValidationRule($data, &$config)
    {
        if (isset($data[self::XML_VALIDATION]['rule'])) {
            $rules = $data[self::XML_VALIDATION]['rule'];
            if (isset($rules['_value'])) {
                $config['class'] = $rules['_attribute']['name'];
                if (isset($rules['_attribute']['name']) && $rules['_attribute']['name'] == 'required-entry') {
                    $config['required'] = true;
                }
            } else {
                $html = '';
                foreach ($data[self::XML_VALIDATION]['rule'] as $rule) {
                    if (isset($rule['_value'])) {
                        $html .= $rule['_attribute']['name'] . " ";
                        if (isset($rule['_attribute']['name']) && $rule['_attribute']['name'] == 'required-entry') {
                            $config['required'] = true;
                        }
                    }
                }
                $config['class'] = $html;
            }
        }
        return $config;
    }
}
