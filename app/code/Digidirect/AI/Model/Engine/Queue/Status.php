<?php
namespace Digidirect\AI\Model\Engine\Queue;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 *
 * @package Digidirect\AI\Model\Engine\Queue
 */
class Status extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    const STATUS_PENDING = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_FAILED = 3;
    const STATUS_SUCCESS = 4;

    /**
     * Retrieve option array
     *
     * @return []
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_PENDING    => __('Pending'),
            self::STATUS_PROCESSING => __('Processing'),
            self::STATUS_FAILED     => __('Failed'),
            self::STATUS_SUCCESS    => __('Success')
        ];
    }

    /**
     * Retrieve all options
     *
     * @return []
     */
    public static function getAllOptions()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
