<?php

namespace Digidirect\AbstractGiftCard\Model;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;

/**
 * GiftCard interface
 * @api
 */
interface ServiceInterface
{
    /**
     * Retrieve giftcard service code
     *
     * @return string
     */
    public function getCode();

    /**
     * Retrieve giftcard service title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Store id setter
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId);

    /**
     * Store id getter
     * @return int
     */
    public function getStore();

    /**
     * Check send CheckStatusRequest availability
     *
     * @return bool
     */
    public function canCheckStatus();

    /**
     * Check send CreateRequest availability
     *
     * @return bool
     */
    public function canHold();

    /**
     * Check send AcceptRequest availability
     *
     * @return bool
     */
    public function canAccept();

    /**
     * Check send CancelRequest availability
     *
     * @return bool
     */
    public function canCancel();

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund();

    /**
     * Using internal pages for input service data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal();

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseOnFront();

    /**
     * Validate giftcard service information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate();

    /**
     * Check status abstract method
     *
     * @return mixed
     */
    public function checkStatus();

    /**
     * Capture service abstract method
     *
     * @param double $amount
     * @return $this
     */
    public function hold($amount);

    /**
     * Accept service abstract method
     *
     * @param double $amount
     * @param string|null $token
     * @return $this
     */
    public function accept($amount, $token = null);

    /**
     * Cancel service abstract method
     *
     * @param string $reason
     * @param null $token
     * @param null $amount
     * @return $this
     */
    public function cancel($reason = '', $token = null, $amount = null);

    /**
     * Refund specified amount for service
     *
     * @param double $amount
     * @return $this
     */
    public function refund($amount);

    /**
     * Retrieve information from service configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null);

    /**
     * Check whether giftcard service can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null);

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null);
}
