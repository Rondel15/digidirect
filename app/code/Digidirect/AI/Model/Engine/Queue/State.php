<?php
namespace Digidirect\AI\Model\Engine\Queue;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class State
 *
 * @package Digidirect\AI\Model\Engine\Queue
 */
class State extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    const STATE_PENDING = 1;
    const STATE_PENDING_DEPENDS = 2;
    const STATE_PROCESSING = 3;
    const STATE_CLOSED = 4;

    /**
     * Retrieve option array
     *
     * @return []
     */
    public static function getOptionArray()
    {
        return [
            self::STATE_PENDING => __('Pending'),
            self::STATE_PENDING_DEPENDS => __('Pending Depends'),
            self::STATE_PROCESSING => __('Processing'),
            self::STATE_CLOSED => __('Closed')
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
