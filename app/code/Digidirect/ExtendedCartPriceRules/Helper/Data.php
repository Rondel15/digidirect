<?php

namespace Digidirect\ExtendedCartPriceRules\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\PaymentMethodLimit;

/**
 * Class Data
 *
 * @package Digidirect\ExtendedCartPriceRules\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE = '__has_active_special_price';

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var PaymentMethodLimit
     */
    protected $paymentMethodLimit;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Payment\Model\MethodList
     */
    protected $methodList;

    /**
     * @var []
     */
    protected $productInfoByProductId = [];

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ProductResource $productResource
     * @param CheckoutSession $session
     * @param PaymentMethodLimit $paymentMethodLimit
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Payment\Model\MethodList $methodList
     */
    public function __construct(
        Context $context,
        ProductResource $productResource,
        CheckoutSession $session,
        PaymentMethodLimit $paymentMethodLimit,
        StoreManagerInterface $storeManager,
        \Magento\Payment\Model\MethodList $methodList
    ) {
        parent::__construct($context);
        $this->productResource = $productResource;
        $this->checkoutSession = $session;
        $this->paymentMethodLimit = $paymentMethodLimit;
        $this->storeManager = $storeManager;
        $this->methodList = $methodList;
    }

    /**
     * @param ProductInterface $product
     * @param string $attributeCode
     * @return mixed
     */
    public function getProductAttributeValue(ProductInterface $product, $attributeCode)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        if ($product->hasData($attributeCode)) {
            return $product->getData($attributeCode);
        }

        $value = $this->productResource->getAttributeRawValue(
            $product->getId(),
            $attributeCode,
            $product->getStoreId()
        );

        return is_array($value) ? null : $value;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function hasProductActiveSpecialPrice(ProductInterface $product)
    {
        $productId = $product->getId();
        if (!isset($this->productInfoByProductId[$productId])) {
            /**
             * @var $product \Magento\Catalog\Model\Product
             */
            $storeId = $product->getStoreId();
            $price = $this->getProductAttributeValue($product, ProductInterface::PRICE);
            $specialPrice = $this->getProductAttributeValue($product, ProductAttributeInterface::CODE_SPECIAL_PRICE);
            $specialPriceFrom = $this->getProductAttributeValue($product, 'special_from_date');
            $specialPriceTo = $this->getProductAttributeValue($product, 'special_to_date');

            $priceModel = $product->getPriceModel();
            $calculatedPrice = $priceModel->calculateSpecialPrice(
                $price,
                $specialPrice,
                $specialPriceFrom,
                $specialPriceTo,
                $storeId
            );
            $this->productInfoByProductId[$productId] = $price; //$calculatedPrice < $price;
        }

        return $this->productInfoByProductId[$productId];
    }

    /**
     * @param AbstractItem $item
     * @return bool
     */
    public function hasQuoteItemActiveSpecialPrice(AbstractItem $item)
    {
        $product = $item->getProduct();
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $childrenItems = $item->getChildren();
            if (empty($childrenItems)) {
                return false;
            }
            $childItem = current($childrenItems);
            if (!$childItem) {
                return false;
            }
            $product = $childItem->getProduct();
        }
        return $this->hasProductActiveSpecialPrice($product);
    }

    /**
     * Retrieve Quote object
     *
     * @return Quote
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @param null|Quote $quote
     * @return array
     */
    public function getAvailableMethods($quote = null)
    {
        $quote = $quote ?: $this->getQuote();
        if (null === $quote) {
            return [];
        }
        $availableMethods = $this->getMethodList($quote);

        $store = $this->storeManager->getStore($quote->getStoreId());
        $this->paymentMethodLimit->init(
            $store->getWebsiteId(),
            $quote->getCustomerGroupId(),
            $quote->getCouponCode()
        );

        $allowedMethods = $this->paymentMethodLimit->getAvailableMethods($quote);
        if (null === $allowedMethods) {
            $allowedPaymentMethods = [];
            foreach ($availableMethods as $availableMethod) {
                $allowedPaymentMethods[] = $availableMethod->getCode();
            }
            return $allowedPaymentMethods;
        }

        $newAllowedMethods = [];
        foreach ($availableMethods as $availableMethod) {
            /** @var $availableMethod \Magento\Quote\Api\Data\PaymentMethodInterface */
            if (!in_array($availableMethod->getCode(), $allowedMethods)) {
                $newAllowedMethods[] = $availableMethod->getCode();
            }
        }

        return $newAllowedMethods;
    }

    /**
     * Get extend rules data ('Is enable unavailabl payment methods' and
     * 'Message for unavailable payment method') from rule 'Actions' tab
     *
     * @param null|Quote $quote
     * @return array
     */
    public function getExtendRulesData($quote = null)
    {
        $quote = $quote ?: $this->getQuote();
        if (null === $quote) {
            return [];
        }
        try {
            $store = $this->storeManager->getStore($quote->getStoreId());
        } catch (NoSuchEntityException $e) {
            return [];
        }
        $this->paymentMethodLimit->init(
            $store->getWebsiteId(),
            $quote->getCustomerGroupId(),
            $quote->getCouponCode()
        );

        $extendData = $this->paymentMethodLimit->getExtendRulesData($quote);
        return $extendData;
    }

    /**
     * @param Quote $quote
     * @return \Magento\Payment\Model\MethodInterface[]
     */
    public function getMethodList($quote)
    {
        return $this->methodList->getAvailableMethods($quote);
    }
}
