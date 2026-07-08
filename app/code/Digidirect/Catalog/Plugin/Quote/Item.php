<?php

namespace Digidirect\Catalog\Plugin\Quote;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Catalog\Model\Product;
use Digidirect\FreeGift\Model\Cart\Item as CartItem;

class Item
{
    /**
     * @var CartItem
     */
    protected $_giftItem;
    
    
    protected $logger;

    /**
     * Item constructor.
     *
     * @param CartItem $giftItem
     */
    public function __construct
    (   
        CartItem $giftItem,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_giftItem = $giftItem;
        $this->logger = $logger;
    }

    /**
     * @param QuoteItem $subject
     * @param mixed $value
     * @return array
     */
    public function beforeSetPrice(QuoteItem $subject, $value)
    {
        return [0];
    }
}
