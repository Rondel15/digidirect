<?php

namespace Digidirect\Utilities\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\Store;
use Digidirect\Utilities\Block\View\Element\Message\Renderer\ExtendedRenderer;

/**
 * Class Message
 * @package Digidirect\Utilities\Helper
 */
class Message extends AbstractHelper
{
    const XML_PATH_MESSAGE_SETTINGS = 'dev/message_castomization/message_settings';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var ExtendedRenderer
     */
    protected $extendedRenderer;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param ExtendedRenderer\Proxy $extendedRenderer
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Math\Random $mathRandom,
        ExtendedRenderer\Proxy $extendedRenderer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mathRandom = $mathRandom;
        $this->extendedRenderer = $extendedRenderer;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param mixed $value
     * @return string
     */
    protected function serializeValue($value)
    {
        if (is_numeric($value)) {
            $data = (float)$value;
            return (string)$data;
        } elseif (is_array($value)) {
            return serialize($value);
        } else {
            return '';
        }
    }

    /**
     * Create a value from a storable representation
     *
     * @param mixed $value
     * @return array
     */
    protected function unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return unserialize($value);
        } else {
            return [];
        }
    }

    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param string|array $value
     * @return bool
     */
    protected function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('renderer_type_id', $row)
                || !array_key_exists('identifier', $row)
                || !array_key_exists('phrase', $row)
                || !array_key_exists('renderer_template', $row)
                || !array_key_exists('css_class', $row)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Encode value to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    protected function encodeArrayFieldValue(array $value)
    {
        /** @var array $result  */
        /** @var string $resultId  */
        $result = [];
        foreach ($value as $identifier => $data) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = [
                'identifier' => $identifier,
                'renderer_type_id' => $data['renderer_type_id'],
                'phrase' => $data['phrase'],
                'renderer_template' => $data['renderer_template'],
                'css_class' => $data['css_class']
            ];
        }
        return $result;
    }

    /**
     * Decode value from used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    protected function decodeArrayFieldValue(array $value)
    {
        /** @var array $result  */
        /** @var array $row */
        $result = [];
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('renderer_type_id', $row)
                || !array_key_exists('identifier', $row)
                || !array_key_exists('phrase', $row)
                || !array_key_exists('renderer_template', $row)
                || !array_key_exists('css_class', $row)
            ) {
                continue;
            }
            $result[$row['identifier']] = [
                'renderer_type_id' => $row['renderer_type_id'],
                'phrase' => $row['phrase'],
                'renderer_template' => $row['renderer_template'],
                'css_class' => $row['css_class']
            ];
        }
        return $result;
    }

    /**
     * Retrieve message settings value from config
     *
     * @param string $messageIdentifier
     * @param null|string|bool|int|Store $store
     * @return array
     */
    public function getConfigValue($messageIdentifier, $store = null)
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MESSAGE_SETTINGS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        $value = $this->unserializeValue($value);
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }
        $result = [];
        foreach ($value as $identifier => $data) {
            if ($identifier == $messageIdentifier) {
                $result = $data;
                break;
            }
        }
        return $result;
    }

    /**
     * Make value readable by \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param string|array $value
     * @return array
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);
        if (!$this->isEncodedArrayFieldValue($value)) {
            $value = $this->encodeArrayFieldValue($value);
        }
        return $value;
    }

    /**
     * Make value ready for store
     *
     * @param string|array $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }
        $value = $this->serializeValue($value);
        return $value;
    }

    /**
     * @param string $message
     * @param string $template
     * @return string
     */
    public function getCustomizedMessage($message, $template = null)
    {
        return $this->extendedRenderer->renderText($message, $template);
    }
}
