<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Product as OriginalProductCondition;

/**
 * Class Product
 */
class Product extends OriginalProductCondition
{
    /**
     * Validate Product Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $abstractModel
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $abstractModel)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $abstractModel->getProduct();
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            if (!$abstractModel->getProductId()) {
                return false;
            }
            $product = $this->productRepository->getById($abstractModel->getProductId());
        }

        //use parent product to get category id
        if ($abstractModel->getParentItem() && $this->getAttribute() == 'category_ids') {
            return $this->validateAttribute($abstractModel->getParentItem()->getProduct()->getAvailableInCategories());
        }

        $product->setData('quote_item_sku', $abstractModel->getSku());
        $abstractModel->setProduct($product);

        $result = parent::validate($abstractModel);

        return $result;
    }

    /**
     * Add special attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_sku'] = __('Cart Item SKU (including custom options SKUs)');
    }
}
