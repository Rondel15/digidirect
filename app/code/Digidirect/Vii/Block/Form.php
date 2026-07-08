<?php

namespace Digidirect\Vii\Block;

use \Magento\GiftCardAccount\Model\Giftcardaccount;

/**
 * Class Form
 * @package Digidirect\Vii\Block
 */
class Form extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var \Magento\GiftCardAccount\Helper\Data
     */
    protected $giftCardHelper;

    /**
     * Form constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\GiftCardAccount\Helper\Data $giftCardHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\GiftCardAccount\Helper\Data $giftCardHelper,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * URLs with secure/unsecure protocol switching
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        if (!array_key_exists('_secure', $params)) {
            $params['_secure'] = $this->getRequest()->isSecure();
        }
        return parent::getUrl($route, $params);
    }

    /**
     * @return bool
     */
    public function canRedeem()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getQuoteCards()
    {
        return $this->giftCardHelper->getCards($this->getQuote());
    }

    /**
     * @param Giftcardaccount $giftCard
     * @return string
     */
    public function getRemoveUrl($giftCard)
    {
        return rtrim($this->getUrl(
            'magento_giftcardaccount/cart/remove',
            ['code' => $giftCard[Giftcardaccount::CODE]]
        ), '/');
    }
}
