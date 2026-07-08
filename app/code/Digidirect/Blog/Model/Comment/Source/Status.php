<?php
namespace Digidirect\Blog\Model\Comment\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Status
 */
class Status implements ArrayInterface
{
    const STATUS_APPROVED = 1;

    const STATUS_DISAPPROVED = 0;

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'value' => self::STATUS_APPROVED,
                'label' => __('Approved')
            ],
            [
                'value' => self::STATUS_DISAPPROVED,
                'label' => __('Disapproved')
            ]
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
