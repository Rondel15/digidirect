<?php
namespace Digidirect\ExtendedCatalogPriceRule\Block;

use Digidirect\ExtendedCatalogPriceRule\Helper\Data;
use Digidirect\ExtendedCatalogPriceRule\Helper\ViewData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ExtendedRule
 * @package Digidirect\ExtendedCatalogPriceRule\Block
 */
class ExtendedRule extends Template
{
    const DISABLE_PRODUCT_TYPES = 'disable_product_types';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var mixed
     */
    protected $viewHelper;

    /**
     * ExtendedRule constructor.
     * @param Context $context
     * @param Data $helper
     * @param array $data
     * @param ViewData|null $viewHelper
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = [],
        ViewData $viewHelper = null
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->viewHelper = $viewHelper ?: ObjectManager::getInstance()->get(ViewData::class);
    }

    /**
     * @param float $amount
     * @return float|string
     */
    public function formatDiscountAmount($amount)
    {
        return $this->helper->formatDiscountAmountAsHtml($amount);
    }

    /**
     * @param string $description
     * @return string
     */
    public function prepareDescription($description)
    {
        return $this->helper->prepareContent($description);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array|bool
     */
    public function getProductRules(\Magento\Catalog\Model\Product $product)
    {
        $productType = $product->getTypeId();

        if (
            is_array($this->getData(self::DISABLE_PRODUCT_TYPES)) &&
            in_array($productType, $this->getData(self::DISABLE_PRODUCT_TYPES))
        ) {
            return false;
        }

        $productRules = false;
        switch ($productType) {
            case Configurable::TYPE_CODE:
                $productRules = $this->getConfigProductRules($product);
                break;
            default:
                $productRules = $this->getSimpleProductRules($product->getId());
                break;
        }

        return $productRules;
   }

    /**
     * @param $productId
     * @return bool
     */
    public function getSimpleProductRules($productId)
    {
        $ruleData = $this->viewHelper->getExtendedRulesDataForView([$productId]);
        if (!empty($ruleData) && !empty($ruleData[$productId])) {
            return $ruleData[$productId];
        }
        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getConfigProductRules(\Magento\Catalog\Model\Product $product)
    {
        $productId = $product->getId();
        $childrenProducts = $product->getTypeInstance()->getChildrenIds($productId);
        $childrenArray = [];
        foreach ($childrenProducts as $child) {
            $childrenArray = array_merge($childrenArray, $child);
        }

        $productRules = [];
        if (!empty($childrenArray)) {
            $ruleData = $this->viewHelper->getExtendedRulesDataForView($childrenArray);

            foreach ($ruleData as $rules) {
                if (!empty($rules)) {
                   foreach ($rules as $ruleId => $rule) {
                       $rule['product_id'] = $productId;
                       $productRules[$ruleId] = $rule;
                   }
                }
            }
        }
        return $productRules;
    }
}
