<?php
namespace Digidirect\CollectAbstractEntity\Model\System\Config\Source;

use Digidirect\CollectAbstractEntity\Api\Data\CollectFields\Constants;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class CollectField
 * @package Digidirect\CollectAbstractEntity\Model\System\Config\Source
 */
class CollectField implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];

        foreach (Constants::COLLECT_FIELDS_ALL as $field) {
            $options[] = [
                'value' => $field,
                'label' => $this->getCollectFieldLabel($field)
            ];
        }
        return $options;
    }

    /**
     * @param string $field
     * @return \Magento\Framework\Phrase
     */
    public function getCollectFieldLabel($field)
    {
        return __(ucfirst($field));
    }
}
