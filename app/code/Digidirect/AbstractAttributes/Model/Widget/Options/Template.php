<?php
namespace Digidirect\AbstractAttributes\Model\Widget\Options;

/**
 * Class Template
 * @package Digidirect\AbstractAttributes\Model\Widget\Options
 */
class Template implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $optionsConfig;

    /**
     * Template constructor.
     * @param array $optionsConfig
     */
    public function __construct(array $optionsConfig = [])
    {
        $this->optionsConfig = $optionsConfig;
    }

    /**
     * Get mapper array
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->optionsConfig as $code => $data) {
            $result[$code] = !empty($data['label']) ? __($data['label']) : '';
        }
        return $result;
    }

    /**
     * Get templates
     * @param int $code
     * @return array|null
     */
    public function getTemplateData($code)
    {
        if (!$code) {
            return $this->optionsConfig;
        }

        if (isset($this->optionsConfig[$code])) {
            return $this->optionsConfig[$code];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->optionsConfig;
    }
}
