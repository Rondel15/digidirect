<?php

namespace Digidirect\AbstractGiftCard\Api;

/**
 * Interface GuestGiftCardAccountManagementInterface
 * @api
 */
interface AbstractGuestGiftCardAccountManagementInterface
{
    /**
     * @param string $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @return bool
     */
    public function addAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    );

    /**
     * @param string $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return float
     */
    public function checkAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    );
}
