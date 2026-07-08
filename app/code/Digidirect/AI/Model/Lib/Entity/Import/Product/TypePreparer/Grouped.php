<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product\TypePreparer;

class Grouped extends AbstractTypePreparer
{
    const COL_ASSOCIATED_SKU = 'associated_skus';

    /**
     * @param array $product
     * @return string
     */
    public function prepareEntity(array &$product)
    {
        $column = self::COL_ASSOCIATED_SKU;
        if (isset($product[$column]) && is_array($product[$column])) {
            $product[$column] = $this->mergeOneLevelArray($product[$column]);
        }
        return $product;
    }
}
