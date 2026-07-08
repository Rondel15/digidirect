<?php
namespace Digidirect\Digi\Helper;

/**
 * Class Quote
 * @package Digidirect\Digi\Helper
 */
class Quote extends \Magento\Framework\App\Helper\AbstractHelper
{

    const WEB_ONLY_ATTR_NAME = 'web_only_sale';

    /**
     * CheckoutSession
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Quote constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @return bool
     */
    public function isQuoteHasWebOnlyProducts()
    {
        $quoteItems = $this->_checkoutSession->getQuote()->getItemsCollection();
        foreach ($quoteItems as $quoteItem) {
            $webOnly = $this->isProductWebOnly($quoteItem->getProduct());
            if ($webOnly) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isProductWebOnly(\Magento\Catalog\Model\Product $product)
    {
        return (bool) $product->getData(self::WEB_ONLY_ATTR_NAME);
    }
}
