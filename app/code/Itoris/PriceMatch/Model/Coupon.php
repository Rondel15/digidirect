<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Model;
use Magento\SalesRule\Model\Rule;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;

class Coupon
{
    protected $ruleFactory;
    protected $couponFactory;
    protected $timezone;
    protected $groupFactory;
    protected $storeFactory;
    protected $productConditionFactory;
    protected $productRepository;
    protected $productFoundFactory;
    protected $productCombineFactory;
    protected $priceCurrency;
    protected $configurableProduct;

    public function __construct
    (
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\SalesRule\Model\Rule\Condition\ProductFactory $productConditionFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\FoundFactory $productFoundFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $productCombineFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->configurableProduct = $configurableProduct;
        $this->priceCurrency = $priceCurrency;
        $this->couponFactory = $couponFactory;
        $this->timezone = $timezone;
        $this->productCombineFactory = $productCombineFactory;
        $this->productFoundFactory = $productFoundFactory;
        $this->productRepository = $productRepository;
        $this->productConditionFactory = $productConditionFactory;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
    }

    public function createCoupon($item)
    {
        $rule = $this->ruleFactory->create();
        $coupon = $this->couponFactory->create();
        $groupCustomers = $this->groupFactory->create()->getCollection()->getAllIds();



        $websiteId = $this->storeFactory->create()->load($item['store_id'])->getWebsiteId();
        $productId = $item['product_id'];
        $product = $this->productRepository->getById( $item['product_id'],false, $item['store_id']);

        /** @var \Magento\SalesRule\Model\Rule\Condition\Product $productCondition */
        $productCondition = $this->productConditionFactory->create();

        if($product->getTypeId() == TypeConfigurable::TYPE_CODE ){
            $byRequest = \Zend_Json_Decoder::decode( $item['by_request'] );
            $product = $this->configurableProduct->getProductByAttributes($byRequest, $product);
        }

        $productCondition->setType('Magento\SalesRule\Model\Rule\Condition\Product');
        $productCondition->setAttribute('sku');
        $productCondition->setOperator('==');
        $productCondition->setValue($product->getSku());

        /** @var \Magento\SalesRule\Model\Rule\Condition\Combine $conditionProductCombine */
        $conditionProductCombine = $this->productCombineFactory->create();
        $conditionProductCombine->setConditions([$productCondition]);

        $couponGenerateCode= $rule->getCouponCodeGenerator()->generateCode();

        $couponCode = 'PMC-'.$couponGenerateCode . $rule->getCouponCodeGenerator()->getDelimiter() . sprintf(
            '%04u', rand(0, 9999));

        $priceDiff = $item['final_price'] - $item['match_price'];

        $rule->setName(__('Promo Core for Price Match Request #%1', $item['item_id']))
            ->setDescription(__('%1 OFF %2',$this->formatPrice( $priceDiff, $item['store_id'] ), $item['product_name']))
            ->setFromDate($this->timezone->date()->format('Y-m-d'))
            ->setUsesPerCustomer(1)
            ->setCustomerGroupIds(implode(',',$groupCustomers))
            ->setIsActive(1)
            ->setSimpleAction(Rule::BY_FIXED_ACTION)
            ->setDiscountAmount($priceDiff)
            ->setDiscountQty(1)
            ->setWebsiteIds($websiteId)
            ->setProductIds($productId)
            ->setCouponType(Rule::COUPON_TYPE_SPECIFIC)
            ->setUsesPerCoupon(1)
            ->setActions($conditionProductCombine);
        $rule->save();

        $coupon->setRule($rule)->setIsPrimary(true)->setCode($couponCode)
            ->setUsageLimit(1)->setUsagePerCustomer($rule->getUsesPerCustomer());
        $coupon->save();

        return $coupon;
    }

    private function formatPrice($amount, $store){
        return $this->priceCurrency->format(
            $amount,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );
    }
}
