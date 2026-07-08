<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Wishlist;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\Wishlist;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class ItemCarrier
 *
 * @package Digidirect\ExtendedCartPriceRules\Model\Wishlist
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemCarrier extends \Magento\Wishlist\Model\ItemCarrier
{
    /**
     * @var ExtendedCartPriceRule
     */
    public $extendedCartPriceRule;

    /**
     * ItemCarrier constructor.
     *
     * @param Session $customerSession
     * @param LocaleQuantityProcessor $quantityProcessor
     * @param Cart $cart
     * @param Logger $logger
     * @param WishlistHelper $helper
     * @param CartHelper $cartHelper
     * @param UrlInterface $urlBuilder
     * @param MessageManager $messageManager
     * @param RedirectInterface $redirector
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Session $customerSession,
        LocaleQuantityProcessor $quantityProcessor,
        Cart $cart,
        Logger $logger,
        WishlistHelper $helper,
        CartHelper $cartHelper,
        UrlInterface $urlBuilder,
        MessageManager $messageManager,
        RedirectInterface $redirector,
        ExtendedCartPriceRule $extendedCartPriceRule
    ) {
        parent::__construct(
            $customerSession,
            $quantityProcessor,
            $cart,
            $logger,
            $helper,
            $cartHelper,
            $urlBuilder,
            $messageManager,
            $redirector
        );
        $this->extendedCartPriceRule = $extendedCartPriceRule;
    }

    /**
     * @param Wishlist $wishlist
     * @param array $qtys
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function moveAllToCart(Wishlist $wishlist, $qtys)
    {
        $isOwner = $wishlist->isOwner($this->customerSession->getCustomerId());

        $messages = $addedProducts = $notSalable = [];

        $cart = $this->cart;
        $collection = $wishlist->getItemCollection()->setVisibilityFilter();

        foreach ($collection as $item) {
            /** @var $item \Magento\Wishlist\Model\Item */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();

                // Set qty
                if (isset($qtys[$item->getId()])) {
                    $qty = $this->quantityProcessor->process($qtys[$item->getId()]);
                    if ($qty) {
                        $item->setQty($qty);
                    }
                }
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                // Add to cart
                // we shouldn't delete items from wishlist right away
                if ($item->addToCart($cart)) {
                    $addedProducts[] = $item->getProduct();
                }
            } catch (LocalizedException $e) {
                if ($e instanceof ProductException) {
                    $notSalable[] = $item;
                } else {
                    $messages[] = __('%1 for "%2".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }

                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                if ($cartItem) {
                    $cart->getQuote()->deleteItem($cartItem);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $messages[] = __('We can\'t add this item to your shopping cart right now.');
            }
        }

        if ($isOwner) {
            $indexUrl = $this->helper->getListUrl($wishlist->getId());
        } else {
            $indexUrl = $this->urlBuilder->getUrl('wishlist/shared', ['code' => $wishlist->getSharingCode()]);
        }
        if ($this->cartHelper->getShouldRedirectToCart()) {
            $redirectUrl = $this->cartHelper->getCartUrl();
        } elseif ($this->redirector->getRefererUrl()) {
            $redirectUrl = $this->redirector->getRefererUrl();
        } else {
            $redirectUrl = $indexUrl;
        }

        if ($notSalable) {
            $products = [];
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = __(
                'We couldn\'t add the following product(s) to the shopping cart: %1.',
                join(', ', $products)
            );
        }

        if ($messages) {
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
            $redirectUrl = $indexUrl;
        }

        if ($addedProducts) {
            // save cart and collect totals
            $cart->save()->getQuote()->collectTotals();
            $restrict = $this->extendedCartPriceRule->getRestrictAddToCartErrorMessages($cart->getQuote());

            // save wishlist model for setting date of last update
            try {
                $wishlist->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t update the Wish List right now.'));
                $redirectUrl = $indexUrl;
            }

            if (empty($restrict)) {
                $products = [];
                foreach ($addedProducts as $product) {
                    /** @var $product \Magento\Catalog\Model\Product */
                    $products[] = '"' . $product->getName() . '"';
                }

                $this->messageManager->addSuccess(
                    __(
                        '%1 product(s) have been added to shopping cart: %2.',
                        count($addedProducts),
                        join(', ', $products)
                    )
                );
            }
        }
        $this->helper->calculate();

        return $redirectUrl;
    }
}
