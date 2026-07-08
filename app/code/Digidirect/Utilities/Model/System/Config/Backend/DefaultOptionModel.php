<?php
namespace Digidirect\Utilities\Model\System\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;

/**
 * Backend for serialized array data
 */
class DefaultOptionModel extends Serialized
{
    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = [];
        $attributes = (array)$this->getValue();
        if (!empty($attributes)) {
            unset($attributes['__empty']);
            foreach ($attributes as $attribute) {
                $value[$this->_prepareKey($attribute)] = $attribute;
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }

    /**
     * @param array $attributes
     * @return string
     */
    protected function _prepareKey($attributes = [])
    {
        if (is_array($attributes) || is_object($attributes))
        {
            foreach ($attributes as &$attribute) {
                if (is_array($attribute)) {
                    $attribute = $this->_prepareKey($attribute);
                }
            }
            return md5($this->getField() . '-' . implode('_', array_values($attributes)));
        }

    }

    /**
     * @param string $value
     * @return array|bool|float|int|mixed|null|string
     */
    public function convertValueToArray($value)
    {
        $this->setValue($value);
        $this->_afterLoad();
        return $this->getValue();
    }
}
