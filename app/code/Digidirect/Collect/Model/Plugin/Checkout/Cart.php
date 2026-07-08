<?php
namespace Digidirect\Collect\Model\Plugin\Checkout;

use Digidirect\Collect\Api\CollectPlaceAddToCartValidationInterface;
use Digidirect\Collect\Helper\Data as CollectHelper;
use Digidirect\Collect\Model\AddToCart\CollectException;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Cart
 * @package Digidirect\Collect\Model\Plugin\Checkout
 */
class Cart
{
    /** @var CollectHelper */
    protected $collectHelper;

    /** @var CollectPlaceAddToCartValidationInterface */
    protected $addToCartValidator;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var Registry */
    protected $registry;

    /**
     * Cart constructor.
     * @param CollectHelper $helper
     * @param ManagerInterface $messageManager
     * @param Registry $registry
     * @param CollectPlaceAddToCartValidationInterface|null $addToCartValidator
     */
    public function __construct(
        CollectHelper $helper,
        ManagerInterface $messageManager,
        Registry $registry,
        CollectPlaceAddToCartValidationInterface $addToCartValidator = null
    ) {
        $this->collectHelper = $helper;
        $this->addToCartValidator = $addToCartValidator;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param int|ProductInterface $productInfo
     * @param array|null $requestInfo
     * @return array
     * @throws \Exception
     */
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        $quote = $subject->getQuote();
        $collectPlace = $requestInfo['collect_place_id'] ?? null;
        $deliveryType = $requestInfo['delivery_type'] ?? null;

        if (!$this->collectHelper->isEnableSingleStoreInCartRestriction() ||
            !$collectPlace ||
            !$deliveryType ||
            ($deliveryType === CollectHelper::DELIVERY_TYPE_DELIVER) ||
            !$productInfo instanceof Product ||
            !$this->addToCartValidator instanceof CollectPlaceAddToCartValidationInterface ||
            !$quote->getId()
        ) {
            return [$productInfo, $requestInfo];
        }

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            /** @var $quoteItem QuoteItem */
            if (!$quoteItem->getCollectPlaceId()) {
                continue;
            }
            if (!$product = $this->prepareCandidateProduct($productInfo, $requestInfo['super_attribute'] ?? [])) {
                throw new \Exception(__('Invalid product options'));
            }

            try {
                $isAvailable = $this->addToCartValidator
                    ->isProductAvailableInPreviouslySelectedStore($quoteItem, $quote, $product, $requestInfo);
            } catch (\Exception $e) {
                $msg = __('We got an error while checking a product availability. Please try again later');
                $this->messageManager->addErrorMessage($msg);
                throw new \Exception($msg);
            }

            $storeName = $quoteItem->getCollectPlaceStorageName() ?? 'Store';
            if ($isAvailable) {
                $msg = $this->collectHelper->getMessageProductIsAvailable($storeName);
                $this->messageManager->addSuccessMessage($msg);
                break;
            } else {
                $msg = $this->collectHelper->getMessageProductIsNotAvailable($storeName);
                $this->setValidationFailedFlag();
                throw new CollectException($msg);
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * @return void
     */
    protected function setValidationFailedFlag()
    {
        if (!$this->registry->registry(CollectHelper::ADD_TO_CART_VALIDATION_FAILED_FLAG)) {
            $this->registry->register(CollectHelper::ADD_TO_CART_VALIDATION_FAILED_FLAG, true);
        }
    }

    /**
     * @param Product $product
     * @param array $attributes
     * @return bool|Product
     */
    protected function prepareCandidateProduct(Product $product, array $attributes = [])
    {
        if ($product->getTypeId() === Configurable::TYPE_CODE && !empty($attributes)) {
            try {
                $product = $this->getChildProduct($product, $attributes);
            } catch (\Exception $exception) {
                return false;
            }
        }
        return $product;
    }

    /**
     * @param Product $product
     * @param array $attributes
     * @return Product
     */
    public function getChildProduct(Product $product, array $attributes)
    {
        return $product->getTypeInstance()->getProductByAttributes($attributes, $product);
    }
}
