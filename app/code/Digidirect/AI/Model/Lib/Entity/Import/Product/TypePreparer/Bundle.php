<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product\TypePreparer;

class Bundle extends AbstractTypePreparer
{
    const COL_BUNDLE_VALUES = 'bundle_values';

    /**
     * @param array $product
     * @return string
     */
    public function prepareEntity(array &$product)
    {
        $column = self::COL_BUNDLE_VALUES;
        if (isset($product[$column]) && is_array($product[$column])) {
            $product[$column] = $this->mergeTwoLevelArray($product[$column]);
        }
        return $product;
    }
}
