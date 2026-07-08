<?php
namespace Digidirect\RelatedProduct\Model\ResourceModel\RelatedProduct;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
{
    /**
     * Initialize resources
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->isEnabledFlat()) {
            $this->_init(
                'Digidirect\RelatedProduct\Model\RelatedProduct',
                'Magento\Catalog\Model\ResourceModel\Product\Flat'
            );
        } else {
            $this->_init('Digidirect\RelatedProduct\Model\RelatedProduct', 'Magento\Catalog\Model\ResourceModel\Product');
        }
        $this->_initTables();
    }

    /**
     * @param array $categoryIds
     * @param array $productIds
     * @return array
     */
    public function getCategoryPositions(array $categoryIds, array $productIds = [])
    {
        if (empty($productIds)) {
            $productIds = $this->getAllIds();
        }

        $resource = $this->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_category_product'),
            ['category_id', 'product_id', 'position']
        )->where(
            'product_id IN (?)',
            $productIds
        )->where(
            'category_id IN (?)',
            $categoryIds
        );

        $select->order('category_id')
            ->order('position')
            ->order('entity_id');

        $categoryPositions = $connection->fetchAll($select);
        $categoryProducts = [];
        foreach ($categoryPositions as $categoryPosition) {
            $categoryProducts[$categoryPosition['category_id']][$categoryPosition['product_id']]
                = $categoryPosition['position'];
        }

        return $categoryProducts;
    }

    /**
     * Add products to filter
     *
     * @param array $products
     * @return $this
     */
    public function addProductsFilter(array $products)
    {
        if (!empty($products)) {
            $identifierField = $this->getProductEntityMetadata()->getIdentifierField();
            $this->getSelect()->where("e.$identifierField IN (?)", $products);
            $this->_hasLinkFilter = false;
        }
        return $this;
    }
}
