<?php
/* @TODO:REFACTORING:  add saveProduct($data = null) ,
 * updateProduct($data = null), deleteProduct($id) ,
 * saveAllProducts($updateOnDublicate = true), resetStorage()*/
namespace Digidirect\AI\Model\Lib\Import\Product;

interface EntityInterface
{
    /*
     * =========== Required Attributes for all products =========
     * 'sku'
     * 'product_type'
     * '_attribute_set'
     * ==========================================================
     *
     * For new products all required attributes in Magento are necessary
     */

    /**
     * Create Product
     *
     * @return void
     */
    public function saveProducts();

    /**
     * Delete Product
     *
     * @return void
     */
    public function deleteProduct();

    /**
     * Set Data for further processing
     *
     * @param array $data
     * @return $this
     */
    public function setProductsData(array $data);
}
