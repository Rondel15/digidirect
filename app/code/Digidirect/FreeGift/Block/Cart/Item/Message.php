<?php
namespace Digidirect\FreeGift\Block\Cart\Item;

use Digidirect\FreeGift\Helper;

/**
 * Class Message
 * @package Digidirect\FreeGift\Block\Cart\Item
 */
class Message extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_cartItem;

    /**
     * @var Helper\Config
     */
    protected $_configHelper;

    /**
     * Message constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Digidirect\FreeGift\Model\Cart\Item $_cartItem
     * @param Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Digidirect\FreeGift\Model\Cart\Item $_cartItem,
        Helper\Config $configHelper,
        array $data
    ) {
        $this->_cartItem = $_cartItem;
        $this->_configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasFreeGiftItem()
    {
        return $this->_cartItem->isFreeGiftItem($this->getItem());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCartMessage()
    {
        $message = $this->_configHelper->getCartMessage();
        $ruleMessage = $this->_cartItem->getCartItemMessage($this->getItem(), true, false);
        if ($ruleMessage !== null) {
            $message = $ruleMessage;
        }

        return $message;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _toHtml()
    {
        if ($this->hasFreeGiftItem()) {
            return parent::_toHtml();
        }
        return '';
    }
}
