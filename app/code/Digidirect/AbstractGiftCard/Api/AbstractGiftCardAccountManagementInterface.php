<?php

namespace Digidirect\AbstractGiftCard\Api;

/**
 * Interface AbstractGiftCardAccountManagementInterface
 * @api
 */
interface AbstractGiftCardAccountManagementInterface
{
    /**
     * @param int $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return float
     */
    public function checkAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    );

    /**
     * @param int $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @return mixed
     */
    public function addAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    );
}
