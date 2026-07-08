<?php

namespace Digidirect\ProductOverlay\Model;

use Magento\Catalog\Model\Product;

/**
 * Class Rule
 * @package Digidirect\ProductOverlay\Model
 */
class Rule extends \Magento\CatalogRule\Model\Rule
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\ProductOverlay\Model\ResourceModel\Overlays');
        $this->setIdFieldName('entity_id');
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_productsFilter = $product->getId();
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMatchingProductIds() //skip afterGetMatchingProductIds plugin
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productCollectionFactory->create();
            if ($this->_productsFilter) {
                $productCollection->addIdFilter($this->_productsFilter);
            }

            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->_productFactory->create()
                ]
            );
        }

        return $this->_productIds;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function callbackValidateProduct($args)
    {
        $product = $args['product'];
        $product->setData($args['row']);

        $stores = $this->getStores();
        $results = [];

        foreach ($stores as $storeId) {
            $product->setStoreId($storeId);
            $validate = $this->getConditions()->validate($product);
            if ($validate) {
                $results[$storeId] = $validate;
                $this->_productIds[$product->getId()] = $results;
            }
        }

        return $this;
    }
}
