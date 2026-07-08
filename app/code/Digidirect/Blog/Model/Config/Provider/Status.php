<?php
namespace Digidirect\Blog\Model\Config\Provider;

use Digidirect\Blog\Model\Config\SourceProviderInterface;

/**
 * Class Status
 */
class Status implements SourceProviderInterface
{
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'value' => self::STATUS_DISABLED,
                'label' => __('Disabled')
            ],
            [
                'value' => self::STATUS_ENABLED,
                'label' => __('Enabled')
            ]
        ];
    }
    
    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getOptions() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
