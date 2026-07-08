<?php

namespace Digidirect\FreeGift\Block;

class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Digidirect\FreeGift\Model\Cart
     */
    protected $_giftCart;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Add constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Digidirect\FreeGift\Model\Cart $giftCart
     * @param \Digidirect\FreeGift\Model\Registry $giftRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Digidirect\FreeGift\Model\Cart $giftCart,
        \Digidirect\FreeGift\Model\Registry $giftRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_giftCart = $giftCart;
        $this->_giftRegistry = $giftRegistry;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->_giftRegistry->reset();
        $this->_giftCart->getQuote()->collectTotals();
        $items = $this->_giftCart->getNewFreeGiftItems();
        if (count($items)) {
            $this->_checkoutSession->setRedirectDoNotNeeded(true);
            return parent::_toHtml();
        }

        return '';
    }
}
