<?php
namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model;

use Digidirect\ExtendedCartPriceRules\Model\IncreaseRuleManagement;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model\Quote as QuotePlugin;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository as Subject;
use Magento\SalesRule\Model\Rule\Condition\Combine;

/**
 * Class QuoteRepository
 *
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model
 */
class QuoteRepository extends AbstractRestrictAddToCart
{
    /**
     * @var IncreaseRuleManagement
     */
    protected $increaseRuleManagement;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var State
     */
    protected $state;

    /**
     * QuoteRepository constructor.
     *
     * @param State $state
     * @param Request $request
     * @param IncreaseRuleManagement $increaseRuleManagement
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     * @param MessageManagerInterface $messageManager
     * @param Combine $condition
     */
    public function __construct(
        State $state,
        Request $request,
        IncreaseRuleManagement $increaseRuleManagement,
        ExtendedCartPriceRule $extendedCartPriceRule,
        MessageManagerInterface $messageManager,
        Combine $condition
    ) {
        $this->state = $state;
        $this->request = $request;
        $this->increaseRuleManagement = $increaseRuleManagement;
        parent::__construct($extendedCartPriceRule, $messageManager, $condition);
    }

    /**
     * @param Subject $subject
     * @param CartInterface $quote
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(Subject $subject, CartInterface $quote)
    {
        $restrict = $this->extendedCartPriceRule->getRestrictAddToCartErrorMessages($quote);
        if (empty($restrict)) {
            if ($quote->getWishlistItemsToAdd()) {
                //delete items from wishlist, because they don't deleted when adding to cart
                foreach ($quote->getWishlistItemsToAdd() as $wishlistItem) {
                    $wishlistItem->delete();
                }
            }

            return [$quote];
        }

        if ($quote->getUpdatedProducts()) {
            foreach ($quote->getUpdatedProducts() as $product) {
                $oldQty = $product->getData(QuotePlugin::QTY_BEFORE_PRODUCT_UPDATED);
                $this->updateQuoteItem($quote, $product, $oldQty);
            }
        } elseif ($quote->getWishlistItemsToAdd()) {
            foreach ($quote->getWishlistItemsToAdd() as $wishlistItem) {
                $this->updateQuoteItem($quote, $wishlistItem->getProduct());
            }
        } else {
            $lastAddedProduct = $quote->getLastAddedProduct();
            if (!$lastAddedProduct) {
                $error = __(
                    'We can\'t save changes in the cart. %1',
                    implode(';', $this->getAllWarningMessages($restrict))
                );
                throw new LocalizedException($error);
            }

            $this->updateQuoteItem($quote, $lastAddedProduct);
        }

        $this->addWarningMessages($restrict);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->setHasError(true);
        $quote->setLastAddedProduct(null);
        $quote->setUpdatedProducts(null);
        $quote->setWishlistItemsToAdd(null);

        return [$quote];
    }

    /**
     * @param Subject $subject
     * @param CartInterface $result
     * @param mixed ...$args
     * @return mixed
     */
    public function afterSave(Subject $subject, $result, ...$args)
    {
        $saveAfterPlaceOrder = false;
        if ($this->state->getAreaCode() == 'webapi_rest') {
            $requestData = $this->request->getRequestData();
            $saveAfterPlaceOrder = isset($requestData['paymentMethod']);
        }
        if (!$saveAfterPlaceOrder) {
            $quote = current($args);
            if (!$quote instanceof CartInterface || !$quote->getIsActive()) {
                return $result;
            }
            $this->increaseRuleManagement->processRule($quote->getEntityId());
        }

        return $result;
    }

    /**
     * @param CartInterface $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param null|int $oldQty
     * @return void
     * @throws LocalizedException
     */
    public function updateQuoteItem($quote, $product, $oldQty = null)
    {
        $quoteItem = $quote->getItemByProduct($product);
        if (!$quoteItem) {
            throw new LocalizedException(__('Can\'t check Cart Price Rules'));
        }

        $qty = $quoteItem->getQty();
        $newQty = $oldQty ? $oldQty : $quoteItem->getQtyToAdd();
        if ($qty > $newQty) {
            $oldQty = $oldQty ?: $qty - $newQty;
            $quoteItem->setQtyToAdd(0);
            $quoteItem->setQty($oldQty);
        } else {
            $quote->deleteItem($quoteItem);
            $quote->getBillingAddress()->unsetData('cached_items_all');
            $quote->getShippingAddress()->unsetData('cached_items_all');
        }
    }
}
