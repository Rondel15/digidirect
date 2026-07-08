<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Attribute;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Templates
 * @package Digidirect\AbstractAttributes\Ui\Component\Attribute
 */
class Templates implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $optionsConfig;

    /**
     * Templates constructor.
     * @param array $optionsConfig
     */
    public function __construct(array $optionsConfig = [])
    {
        $this->optionsConfig = $optionsConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $response = [];
        foreach ($this->optionsConfig as $data) {
            $response[] = [
                'label' => $data['label'],
                'value' => $data['path'],
            ];
        }
        return $response;
    }
}
