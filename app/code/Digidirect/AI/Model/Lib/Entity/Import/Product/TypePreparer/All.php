<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product\TypePreparer;

use Magento\ImportExport\Model\Import as Import;

class All extends AbstractTypePreparer
{
    const COL_ADDITIONAL_ATTRIBUTES = 'additional_attributes';

    /**
     * @param array $product
     * @return string
     */
    public function prepareEntity(array &$product)
    {
        $column = self::COL_ADDITIONAL_ATTRIBUTES;
        if (isset($product[$column]) && is_array($product[$column])) {
            $product[$column] = $this->mergeOneLevelArray($product[$column]);
        }

        foreach ($product as &$value) {
            if (is_array($value)) {
                foreach ($value as $key => $subValue) {
                    if (is_array($subValue)) {
                        unset($value[$key]);
                    }
                }
                $value = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $value);
            }
        }

        return $product;
    }
}
