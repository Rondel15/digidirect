<?php

namespace Digidirect\AbstractGiftCard\Plugin\GiftCardAccount\Block\Checkout\Cart;

use Magento\GiftCardAccount\Block\Checkout\Cart\Giftcardaccount as MagentoGiftCardAccountBlock;

class Giftcardaccount
{
    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * Giftcardaccount constructor.
     * @param \Digidirect\AbstractGiftCard\Helper\Data $abstractGiftCardHelper
     */
    public function __construct(\Digidirect\AbstractGiftCard\Helper\Data $abstractGiftCardHelper)
    {
        $this->_helper = $abstractGiftCardHelper;
    }

    /**
     * @param MagentoGiftCardAccountBlock $subject
     * @param callable $proceed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return null
     */
    public function beforeToHtml(MagentoGiftCardAccountBlock $subject)
    {
        if (!$this->_helper->isNativeGiftCardsAllowed()) {
            $subject->setData('is_disabled', true);
        }
        return [];
    }
}
