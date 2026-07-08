<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Customer\Model;

use Magento\Customer\Model\AccountManagement as OriginalAccountManagement;
use Magento\Checkout\Model\Cart as CustomerCart;

class AccountManagement
{
    /**
     * @var CustomerCart
     */
    protected $_cart;

    /**
     * AccountManagement constructor.
     * @param CustomerCart $cart
     */
    public function __construct(CustomerCart $cart)
    {
        $this->_cart = $cart;
    }

    /**
     * @param OriginalAccountManagement $subject
     * @param \Closure $proceed
     * @param string $email
     * @param int $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsEmailAvailable(
        OriginalAccountManagement $subject,
        \Closure $proceed,
        $email,
        $websiteId = null
    ) {
        $checkoutSession = $this->_cart->getCheckoutSession();
        $prevEmail = $checkoutSession->getData('last_checked_email');
        $checkoutSession->setData('last_checked_email', $email);
        $result = $proceed($email, $websiteId);
        if ($prevEmail != $email) {
            $quote = $this->_cart->getQuote();
            $quote->getShippingAddress()->setEmail($email);
            $quote->getBillingAddress()->setEmail($email);
            if ($quote->hasItems()) {
                $this->_cart->saveQuote();
            }
        }
        return $result;
    }
}
