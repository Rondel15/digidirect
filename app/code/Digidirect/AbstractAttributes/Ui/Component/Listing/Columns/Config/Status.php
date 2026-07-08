<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Listing\Columns\Config;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Enabled')], ['value' => 0, 'label' => __('Disabled')]];
    }
}
