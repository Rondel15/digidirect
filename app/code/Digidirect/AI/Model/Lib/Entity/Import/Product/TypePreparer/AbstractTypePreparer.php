<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product\TypePreparer;

use Digidirect\AI\Model\Lib\Entity\Import\Service\PreparerInterface;
use Magento\ImportExport\Model\Import as Import;
use Magento\CatalogImportExport\Model\Import\Product as ProductImport;

abstract class AbstractTypePreparer implements PreparerInterface
{
    /**
     * @param array $array
     * @return string
     */
    public function mergeOneLevelArray(array $array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                unset($array[$key]);
                continue;
            }
            $value = $key . ProductImport::PAIR_NAME_VALUE_SEPARATOR . $value;
        }
        return implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $array);
    }

    /**
     * @param array $array
     * @return string
     */
    public function mergeTwoLevelArray(array $array)
    {
        foreach ($array as &$values) {
            $values = $this->mergeOneLevelArray($values);
        }
        return implode(ProductImport::PSEUDO_MULTI_LINE_SEPARATOR, $array);
    }
}
