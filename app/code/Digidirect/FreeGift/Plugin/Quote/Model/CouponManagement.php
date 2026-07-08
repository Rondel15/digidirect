<?php

namespace Digidirect\FreeGift\Plugin\Quote\Model;

class CouponManagement
{
    /**
     * @var \Digidirect\FreeGift\Model\Cart
     */
    protected $_giftCart;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Digidirect\FreeGift\Model\Cart $giftCart
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Digidirect\FreeGift\Model\Cart $giftCart,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_giftCart = $giftCart;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Quote\Model\CouponManagement $subject
     * @param bool|string $result
     * @return bool|string
     */
    public function afterSet(
        \Magento\Quote\Model\CouponManagement $subject,
        $result
    ) {
        if ($result === true) {
            $items = $this->_giftCart->getNewFreeGiftItems();
            if (!empty($items)) {
                return (string)__(
                    'Your coupon was successfully applied. '
                    . 'Please go to <a href="%1">Cart</a> to see available Free Gifts',
                    $this->_urlBuilder->getUrl('checkout/cart')
                );
            }
        }
        return $result;
    }
}
