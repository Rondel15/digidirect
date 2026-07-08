<?php

namespace Digidirect\AbstractGiftCard\Api;

/**
 * Abstract GiftCard Entrity Interface.
 */
interface AbstractGiftCardEntityInterface
{
    /**
     * Get related giftcard account.
     *
     * @return \Magento\GiftCardAccount\Model\Giftcardaccount
     */
    public function getGiftCardAccount();

    /**
     * Get gitftcard code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get code of entity's service
     *
     * @return string
     */
    public function getServiceCode();

    /**
     * @param int $code
     * @return $this
     */
    public function setServiceCode($code);

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @param string $pin
     * @return $this
     */
    public function setPin($pin);

    /**
     * @return string
     */
    public function getPin();

    /**
     * @return \Digidirect\AbstractGiftCard\Model\ServiceInterface
     */
    public function getService();
}
