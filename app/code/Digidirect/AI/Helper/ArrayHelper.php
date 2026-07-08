<?php

namespace Digidirect\AI\Helper;

use Magento\Framework\Exception\LocalizedException;

class ArrayHelper
{
    /**
     * @param array $array
     * @param string $field
     * @param mixed $data
     * @return $this
     * @throws LocalizedException
     */
    public function addColumn(&$array, $field, $data)
    {
        if (is_array($data)) {
            if (count($data) !== count($array)) {
                throw new LocalizedException(__('Data count and Array count must be the same'));
            }
            foreach ($data as $key => $value) {
                $array[$key][$field] = $value;
            }
            return $this;
        }

        foreach ($array as $key => $row) {
            $row[$key][$field] = $data;
        }

        return $this;
    }
}
