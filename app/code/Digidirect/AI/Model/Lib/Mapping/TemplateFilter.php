<?php

namespace Digidirect\AI\Model\Lib\Mapping;

use Magento\Email\Model\Template\Filter as EmailTemplateFilter;
use Magento\Framework\Filter\Template;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Directory\Helper\Data as DirectoryDataHelper;

class TemplateFilter extends Template
{
    const NO_MODIFIER = 'raw';

    /**
     * Modifier Callbacks
     *
     * @var array
     */
    protected $modifiers = [
        'settype' => '',
        'boolval' => '',
        'intval' => '',
        'floatval' => '',
        'strval' => '',
        'round' => '',
        'floor' => '',
        'ceil' => '',
        'number_format' => '',
        'nl2br' => '',
        'trim' => '',
        'ltrim' => '',
        'rtrim' => '',
        'uppercase' => 'strtoupper',
        'lowercase' => 'strtolower',
        'strtoupper' => '',
        'strtolower' => '',
        'substr' => '',
        'abs' => '',
        'is_null' => '',
        'empty' => '',
    ];

    /**
     * @var array
     */
    protected $arrayModifiers = [
        'array_filter' => '',
        'count' => '',
        'is_null' => '',
    ];

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId = Store::DEFAULT_STORE_ID;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var array
     */
    protected $tokenizedValues = [];

    /**
     * TemplateFilter constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $timezone
     * @param array $variables
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        $variables = []
    ) {
        parent::__construct($string, $variables);
        $this->modifiers['escape'] = [$this, 'modifierEscape'];
        $this->modifiers['formatDateTime'] = [$this, 'modifierFormatDateTime'];
        $this->modifiers['formatStoreDateTime'] = [$this, 'modifierFormatStoreDateTime'];
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
    }

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     * @throws \Throwable
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function filter($value)
    {
        // "depend", "if", and "template" directives should be first
        foreach ([
                     self::CONSTRUCTION_DEPEND_PATTERN => 'dependDirective',
                     self::CONSTRUCTION_IF_PATTERN => 'ifDirective',
                     self::CONSTRUCTION_TEMPLATE_PATTERN => 'templateDirective',
                 ] as $pattern => $directive) {
            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $callback = [$this, $directive];
                    if (!is_callable($callback)) {
                        continue;
                    }
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (\Throwable $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }

        if (preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $callback = [$this, $construction[1] . 'Directive'];
                if (!is_callable($callback)) {
                    continue;
                }
                try {
                    $replacedValue = call_user_func($callback, $construction);
                } catch (\Throwable $e) {
                    throw $e;
                }
                // first hack... to not scalar values
                if (!is_scalar($replacedValue)) {
                    $value = $replacedValue;
                    break;
                }
                // end of hack
                // second hack. return value as is - not string
                if ($value == $construction[0]) {
                    $value = $replacedValue;
                    continue;
                }
                // end of hack
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }

        $value = $this->afterFilter($value);
        return $value;
    }

    /**
     * @param string $value
     * @return array
     */
    protected function tokenize($value)
    {
        if (!isset($this->tokenizedValues[$value])) {
            $tokenizer = new Template\Tokenizer\Variable();
            $tokenizer->setString($value);
            $this->tokenizedValues[$value] = $tokenizer->tokenize();
        }
        return $this->tokenizedValues[$value];
    }

    /**
     * Return variable value for var construction
     *
     * @param string $value raw parameters
     * @param string $default default value
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getVariable($value, $default = '{no_value_defined}')
    {
        \Magento\Framework\Profiler::start('mapper_template_processing_variables');
        $stackVars = $this->tokenize($value);
        $result = $default;
        $last = 0;
        for ($i = 0; $i < count($stackVars); $i++) {
            if ($i == 0 && isset($this->templateVars[$stackVars[$i]['name']])) {
                // Getting of template value
                $stackVars[$i]['variable'] = &$this->templateVars[$stackVars[$i]['name']];
            } elseif (isset($stackVars[$i - 1]['variable'])
                && is_array($stackVars[$i - 1]['variable'])
                && $stackVars[$i]['type'] == 'property'
            ) {
                //possibility to use array element values using property syntax
                if (array_key_exists($stackVars[$i]['name'], $stackVars[$i - 1]['variable'])) {
                    $stackVars[$i]['variable'] = $stackVars[$i - 1]['variable'][$stackVars[$i]['name']];
                }
                $last = $i;
            } elseif (isset($stackVars[$i - 1]['variable'])
                && $stackVars[$i - 1]['variable'] instanceof \Magento\Framework\DataObject
            ) {
                // If data object calling methods or getting properties
                if ($stackVars[$i]['type'] == 'property') {
                    $caller = 'get' . $this->string->upperCaseWords($stackVars[$i]['name'], '_', '');
                    $stackVars[$i]['variable'] = method_exists(
                        $stackVars[$i - 1]['variable'],
                        $caller
                    ) ? $stackVars[$i - 1]['variable']->{$caller}() : $stackVars[$i - 1]['variable']->getData(
                        $stackVars[$i]['name']
                    );
                } elseif ($stackVars[$i]['type'] == 'method') {
                    // Calling of data object method
                    if (method_exists($stackVars[$i - 1]['variable'], $stackVars[$i]['name'])
                        || substr($stackVars[$i]['name'], 0, 3) == 'get'
                    ) {
                        $stackVars[$i]['args'] = $this->getStackArgs($stackVars[$i]['args']);
                        $stackVars[$i]['variable'] = call_user_func_array(
                            [$stackVars[$i - 1]['variable'], $stackVars[$i]['name']],
                            $stackVars[$i]['args']
                        );
                    }
                }
                $last = $i;
            } elseif (isset($stackVars[$i - 1]['variable']) && $stackVars[$i]['type'] == 'method') {
                // Calling object methods
                if (method_exists($stackVars[$i - 1]['variable'], $stackVars[$i]['name'])) {
                    $stackVars[$i]['args'] = $this->getStackArgs($stackVars[$i]['args']);
                    $stackVars[$i]['variable'] = call_user_func_array(
                        [$stackVars[$i - 1]['variable'], $stackVars[$i]['name']],
                        $stackVars[$i]['args']
                    );
                }
                $last = $i;
            }
        }

        if (isset($stackVars[$last]['variable'])) {
            // If value for construction exists set it
            $result = $stackVars[$last]['variable'];
        }
        \Magento\Framework\Profiler::stop('mapper_template_processing_variables');
        return $result;
    }

    /**
     * @param string[] $construction
     * @return string
     */
    public function ifDirective($construction)
    {
        if (count($this->templateVars) == 0) {
            return $construction[0];
        }

        if ($this->getVariable($construction[1], '')) {
            return $construction[2];
        }

        if (isset($construction[3]) && isset($construction[4])) {
            return $construction[4];
        }

        return '';
    }

    /**
     * Var directive with modifiers support
     *
     * The |trim modifier is applied by default, use |raw to override
     *
     * @param string[] $construction
     * @return string
     */
    public function varDirective($construction)
    {
        // just return the escaped value if no template vars exist to process
        if (count($this->templateVars) == 0) {
            return $construction[0];
        }

        list($directive, $modifiers) = $this->explodeModifiers($construction[2]);
        $variable = $this->getVariable($directive, null);

        $modifiers = (empty($modifiers) && is_scalar($variable)) ? 'trim' : $modifiers;

        return $this->applyModifiers($variable, $modifiers);
    }

    /**
     * Explode modifiers out of a given string
     *
     * This will return the value and modifiers in a two-element array. Where no modifiers are present in the passed
     * value an array with a null modifier string will be returned
     *
     * Syntax: some text value, etc|modifier string
     *
     * Result: ['some text value, etc', 'modifier string']
     *
     * @param string $value
     * @param string $default assumed modifier if none present
     * @return array
     */
    protected function explodeModifiers($value, $default = '')
    {
        $parts = explode('|', $value, 2);
        if (2 === count($parts)) {
            return $parts;
        }
        return [$value, $default];
    }

    /**
     * Apply modifiers one by one, with specified params
     *
     * Modifier syntax: modifier1[:param1:param2:...][|modifier2:...]
     *
     * @param string $value
     * @param string $modifiers
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function applyModifiers($value, $modifiers)
    {
        foreach (explode('|', $modifiers) as $part) {
            if (empty($part)) {
                continue;
            }
            $params = str_getcsv($part, ':');
            $modifier = array_shift($params);

            $callback = null;

            if ($modifier === self::NO_MODIFIER) {
                continue;
            }

            if ($value === null || is_scalar($value)) {
                if (isset($this->modifiers[$modifier])) {
                    $callback = $this->modifiers[$modifier];
                }
            } elseif (is_array($value) && isset($this->arrayModifiers[$modifier])) {
                $callback = $this->arrayModifiers[$modifier];
            } elseif ($value instanceof \Countable && $modifier == 'count') {
                $callback = $modifier;
            }

            if ($callback === null) {
                throw new \InvalidArgumentException("Cannot find '$modifier' modifier");
            }

            array_unshift($params, $value);
            $value = call_user_func_array($callback ?: $modifier, $params);
        }
        return $value;
    }

    /**
     * Escape specified string
     *
     * @param string $value
     * @param string $type
     * @return string
     */
    public function modifierEscape($value, $type = 'html')
    {
        switch ($type) {
            case 'html':
                return htmlspecialchars($value, ENT_QUOTES);

            case 'htmlentities':
                return htmlentities($value, ENT_QUOTES);

            case 'url':
                return rawurlencode($value);
        }
        return $value;
    }

    /**
     * @return string
     */
    protected function _getLocale()
    {
        return $locale = $this->scopeConfig->getValue(
            DirectoryDataHelper::XML_PATH_DEFAULT_LOCALE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    protected function _getTimezone()
    {
        return $this->scopeConfig->getValue(
            DirectoryDataHelper::XML_PATH_DEFAULT_TIMEZONE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @param string $value
     * @param string $format
     * @return string
     */
    public function modifierFormatDateTime($value, $format)
    {
        if (empty($value)) {
            return $value;
        }
        return $this->timezone
            ->date(new \DateTime($value), $this->_getLocale())
            ->format($format);
    }

    /**
     * @param string $value
     * @param string $format
     * @return string
     */
    public function modifierFormatStoreDateTime($value, $format)
    {
        if (empty($value)) {
            return $value;
        }
        return $this->timezone
            ->date(new \DateTime($value), $this->_getLocale())
            ->setTimezone(new \DateTimeZone($this->_getTimezone()))
            ->format($format);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Setter
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Store config directive
     *
     * @param string[] $construction
     * @return string
     */
    public function configDirective($construction)
    {
        $configValue = '';
        $params = $this->getParameters($construction[2]);
        $storeId = $this->getStoreId();
        if (isset($params['path'])) {
            $configValue = $this->scopeConfig->getValue($params['path'], ScopeInterface::SCOPE_STORE, $storeId);
        }
        return $configValue;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->templateVars;
    }

    /**
     * @param array $variables
     * @return $this
     */
    public function addVariables(array $variables)
    {
        $this->templateVars = array_merge($this->templateVars, $variables);
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addVariable($name, $value)
    {
        $this->templateVars[$name] = $value;
        return $this;
    }

    /**
     * @param array $variables
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->templateVars = $variables;
        return $this;
    }
}
