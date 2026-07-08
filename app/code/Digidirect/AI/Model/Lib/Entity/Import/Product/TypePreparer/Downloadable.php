<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product\TypePreparer;

class Downloadable extends AbstractTypePreparer
{
    /**
     * Column with downloadable samples
     */
    const COL_DOWNLOADABLE_SAMPLES = 'downloadable_samples';

    /**
     * Column with downloadable links
     */
    const COL_DOWNLOADABLE_LINKS = 'downloadable_links';

    /**
     * @param array $product
     * @return string
     */
    public function prepareEntity(array &$product)
    {
        foreach ([self::COL_DOWNLOADABLE_SAMPLES, self::COL_DOWNLOADABLE_LINKS] as $column) {
            if (isset($product[$column]) && is_array($product[$column])) {
                $product[$column] = $this->mergeTwoLevelArray($product[$column]);
            }
        }
        return $product;
    }
}
