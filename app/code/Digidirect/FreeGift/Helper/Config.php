<?php

namespace Digidirect\FreeGift\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Digidirect\FreeGift\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const AUTO_ADD = 'digidirect_freegift/general/auto_add';
    const REDIRECT_TO_CART = 'digidirect_freegift/general/redirect_to_cart';
    const IS_HIDE_FREE_ITEM_PRICE = 'digidirect_freegift/general/is_hide_free_item_price';
    const SHOW_FREE_ITEM_ATTRIBUTES = 'digidirect_freegift/general/show_free_item_attributes';
    const ADD_MESSAGE = 'digidirect_freegift/messages/add_message';
    const AUTO_OPEN_POPUP = 'digidirect_freegift/messages/auto_open_popup';
    const DISPLAY_POPUP_ONCE = 'digidirect_freegift/messages/display_popup_once';
    const DISPLAY_ERROR_MESSAGES = 'digidirect_freegift/messages/display_error_messages';
    const DISPLAY_SUCCESS_MESSAGES = 'digidirect_freegift/messages/display_success_messages';
    const PREFIX = 'digidirect_freegift/messages/prefix';
    const CART_MESSAGE = 'digidirect_freegift/messages/cart_message';
    const POPUP_TEMPLATE = 'digidirect_freegift/messages/popup_template';
    const MESSAGE_FOR_HIDDEN_FREE_ITEM_PRICE = 'digidirect_freegift/messages/message_hidden_free_item_price';
    const MESSAGE_TYPE = 'digidirect_freegift/messages/message_type';

    /**
     * @return bool
     */
    public function isHideFreeItemPrice()
    {
        return $this->scopeConfig->isSetFlag(
            self::IS_HIDE_FREE_ITEM_PRICE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return mixed
     */
    public function getShowFreeItemAttributes()
    {
        return $this->scopeConfig->getValue(
            self::SHOW_FREE_ITEM_ATTRIBUTES,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get a list of needle custom attributes for a Free-gift item.
     *
     * @return array
     */
    public function getFreeItemAttributesArray()
    {
        if ($attributes = $this->getShowFreeItemAttributes()) {
            return explode(',', $attributes);
        }
        return [];
    }

    /**
     * @return string
     */
    public function getMessageForHiddenFreeItemPrice()
    {
        return $this->scopeConfig->getValue(
            self::MESSAGE_FOR_HIDDEN_FREE_ITEM_PRICE,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * @return bool
     */
    public function isAddProductsAutomatically()
    {
        return $this->scopeConfig->isSetFlag(
            self::AUTO_ADD,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->scopeConfig->getValue(
            self::ADD_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isOpenAutomatically()
    {
        return $this->scopeConfig->isSetFlag(
            self::AUTO_OPEN_POPUP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplayPopupOnce()
    {
        return $this->scopeConfig->isSetFlag(
            self::DISPLAY_POPUP_ONCE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplayErrorMessages()
    {
        return $this->scopeConfig->isSetFlag(
            self::DISPLAY_ERROR_MESSAGES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplaySuccessMessages()
    {
        return $this->scopeConfig->isSetFlag(
            self::DISPLAY_SUCCESS_MESSAGES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getFreeItemPrefix()
    {
        return $this->scopeConfig->getValue(
            self::PREFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getCartMessage()
    {
        return $this->scopeConfig->getValue(
            self::CART_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPopupTemplate()
    {
        return $this->scopeConfig->getValue(
            self::POPUP_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isRedirectToCart()
    {
        return $this->scopeConfig->isSetFlag(
            self::REDIRECT_TO_CART,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
        return $this->scopeConfig->getValue(
            self::MESSAGE_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
