<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\Product;

use Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\ProcessingErrorAggregator as BaseProcessingErrorAggregator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

class ProcessingErrorAggregator extends BaseProcessingErrorAggregator
{
    /**
     * @var array
     */
    public $productItems = [];

    /**
     * @inheritdoc
     */
    public function addError(
        $errorCode,
        $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorDescription = null
    ) {
        if ($rowNumber !== null) {
            $item = $this->getProductItem($rowNumber);
            if ($item) {
                $sku = $item[ProductInterface::SKU] ?? 'sku not found';
                $errorDescription .= " {sku: $sku}";
            }
        }

        return parent::addError(
            $errorCode,
            $errorLevel,
            $rowNumber,
            $columnName,
            $errorMessage,
            $errorDescription
        );
    }

    /**
     * @return array
     */
    public function getProductItems()
    {
        return $this->productItems;
    }

    /**
     * @param integer $key
     * @return array
     */
    public function getProductItem($key)
    {
        return $this->productItems[$key] ?? null;
    }

    /**
     * @param array $productItems
     * @return $this
     */
    public function setProductItems(array $productItems)
    {
        $this->productItems = $productItems;
        return $this;
    }
}
