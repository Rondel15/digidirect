<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product\TypePreparer;

class Configurable extends AbstractTypePreparer
{
    const COL_CONFIGURABLE_VAR = 'configurable_variations';

    /**
     * @param array $product
     * @return string
     */
    public function prepareEntity(array &$product)
    {
        $column = self::COL_CONFIGURABLE_VAR;
        if (isset($product[$column]) && is_array($product[$column])) {
            $product[$column] = $this->mergeTwoLevelArray($product[$column]);
        }
        return $product;
    }
}
