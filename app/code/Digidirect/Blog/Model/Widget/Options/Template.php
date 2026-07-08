<?php
namespace Digidirect\Blog\Model\Widget\Options;

/**
 * Class Template
 * @package Digidirect\Blog\Model\Widget\Options
 */
class Template implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Template constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Get mapper array
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->options as $code => $data) {
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
            return $this->options;
        }

        if (isset($this->options[$code])) {
            return $this->options[$code];
        }

        return null;
    }
}
