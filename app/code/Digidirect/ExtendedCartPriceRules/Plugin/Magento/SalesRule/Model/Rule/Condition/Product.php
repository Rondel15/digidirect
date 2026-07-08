<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\SalesRule\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Product as ConditionProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Digidirect\ExtendedCartPriceRules\Helper\Data as DataHelper;

class Product
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * Product constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param DataHelper $dataHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DataHelper $dataHelper
    ) {
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param ConditionProduct $conditionProduct
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadAttributeOptions(ConditionProduct $conditionProduct, $result)
    {
        $attributes = $conditionProduct->getAttributeOption();
        $attributes[DataHelper::HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE] = __('Has Active Special Price');

        asort($attributes);
        $conditionProduct->setAttributeOption($attributes);

        return $result;
    }

    /**
     * @param ConditionProduct $conditionProduct
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetValueSelectOptions(ConditionProduct $conditionProduct, \Closure $proceed)
    {
        $attrCode = $conditionProduct->getAttribute();
        if ($attrCode === DataHelper::HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE) {
            $hashedOptions = [
                0 => __('No'),
                1 => __('Yes'),
            ];

            $selectOptions = [];
            foreach ($hashedOptions as $value => $label) {
                $selectOptions[] = [
                    'label' => $label,
                    'value' => $value,
                ];
            }

            $conditionProduct->setData('value_select_options', $selectOptions);
            $conditionProduct->setData('value_option', $hashedOptions);
        }

        return $proceed();
    }

    /**
     * @param ConditionProduct $conditionProduct
     * @param \Closure $proceed
     * @return mixed|string
     */
    public function aroundGetInputType(ConditionProduct $conditionProduct, \Closure $proceed)
    {
        $attrCode = $conditionProduct->getAttribute();
        if ($attrCode === DataHelper::HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE) {
            return 'select';
        }
        return $proceed();
    }

    /**
     * @param ConditionProduct $conditionProduct
     * @param \Closure $proceed
     * @return mixed|string
     */
    public function aroundGetValueElementType(ConditionProduct $conditionProduct, \Closure $proceed)
    {
        $attrCode = $conditionProduct->getAttribute();
        if ($attrCode === DataHelper::HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE) {
            return 'select';
        }
        return $proceed();
    }

    /**
     * @param ConditionProduct $conditionProduct
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return array
     */
    public function beforeValidate(ConditionProduct $conditionProduct, \Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $conditionProduct->getAttribute();
        if ($attrCode === DataHelper::HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE) {
            $product = $model->getProduct();
            if ($model instanceof \Magento\Quote\Model\Quote\Item\AbstractItem) {
                $hasActiveSpecialPrice = $this->dataHelper->hasQuoteItemActiveSpecialPrice($model);
            } else {
                if (!$product instanceof \Magento\Catalog\Model\Product) {
                    $product = $this->productRepository->getById($model->getProductId());
                }
                $hasActiveSpecialPrice = $this->dataHelper->hasProductActiveSpecialPrice($product);
            }
            $product->setData(DataHelper::HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE, (int)$hasActiveSpecialPrice);
            return [$model];
        }

        return [$model];
    }
}
