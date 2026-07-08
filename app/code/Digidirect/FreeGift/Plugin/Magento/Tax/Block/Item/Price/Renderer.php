<?php

namespace Digidirect\FreeGift\Plugin\Magento\Tax\Block\Item\Price;

/**
 * Class Renderer
 * @package Digidirect\FreeGift\Plugin\Magento\Tax\Block\Item\Price
 */
class Renderer
{
    /**
     * @var \Digidirect\FreeGift\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Digidirect\FreeGift\Helper\Config
     */
    protected $_helperConfig;

    /**
     * Renderer constructor.
     * @param \Digidirect\FreeGift\Helper\Data $helper
     * @param \Digidirect\FreeGift\Helper\Config $helperConfig
     */
    public function __construct(
        \Digidirect\FreeGift\Helper\Data $helper,
        \Digidirect\FreeGift\Helper\Config $helperConfig
    ) {
        $this->_helper = $helper;
        $this->_helperConfig = $helperConfig;
    }

    /**
     * @param \Magento\Tax\Block\Item\Price\Renderer $subject
     * @param \Closure $procede
     * @param float $price
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundFormatPrice(
        \Magento\Tax\Block\Item\Price\Renderer $subject,
        \Closure $procede,
        $price
    ) {
        $product = $subject->getItem();

        if (($product instanceof \Magento\Quote\Model\Quote\Item ||
            $product instanceof \Magento\Sales\Model\Order\Item) &&
            $this->_helper->isFreeGiftItem($product) &&
            $this->_helperConfig->isHideFreeItemPrice()
        ) {
            $priceMessage = $this->_helperConfig->getMessageForHiddenFreeItemPrice();
            return '<span class="price">' . $priceMessage . '</span>';
        } else {
            return $procede($price);
        }
    }
}
