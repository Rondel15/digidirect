<?php

namespace Digidirect\FreeGift\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MessageType
 */
class MessageType implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $messageTypes;

    /**
     * MessageType constructor.
     * @param array $messageType
     */
    public function __construct(
      $messageType = []
    ) {
        $this->messageTypes = $messageType;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $return = [];

        foreach ($this->messageTypes as $value => $label) {
            $return[] = ['value' => $value, 'label' => $label];
        }

        return $return;
    }
}
