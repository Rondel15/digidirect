<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product\TypePreparer;

use Magento\ImportExport\Model\Import as Import;
use Magento\CatalogImportExport\Model\Import\Product as ProductImport;

class GiftCard extends AbstractTypePreparer
{
    const COL_GIFTCARD_AMOUNT = 'giftcard_amount';

    /**
     * @param array $product
     * @return string
     */
    public function prepareEntity(array &$product)
    {
        if (isset($product[self::COL_GIFTCARD_AMOUNT]) && is_array($product[self::COL_GIFTCARD_AMOUNT])) {
            $product[self::COL_GIFTCARD_AMOUNT] = implode(
                Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                $product[self::COL_GIFTCARD_AMOUNT]
            );
        }
        return $product;
    }
}
