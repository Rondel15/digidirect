<?php

namespace Digidirect\AI\Model\Lib\Import\Product\Entity;

use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor as DefaultSkuProcessor;

class SkuProcessor extends DefaultSkuProcessor
{
    /**
     * Reload old skus.
     *
     * @return $this
     */
    public function reloadOldSkus()
    {
        $this->newSkus = null;
        return parent::reloadOldSkus();
    }
}
