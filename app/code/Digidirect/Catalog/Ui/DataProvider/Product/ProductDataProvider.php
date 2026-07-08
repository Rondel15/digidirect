<?php

namespace Digidirect\Catalog\Ui\DataProvider\Product;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'category_id') {
            $this->getCollection()->addCategoriesFilter(['in' => $filter->getValue()]);
        } else {
            parent::addFilter($filter);
        }
    }
}