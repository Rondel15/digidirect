<?php

namespace Digidirect\AbstractGiftCard\Api;

/**
 * GiftCard service list interface.
 */
interface GiftCardServiceListInterface
{
    /**
     * Get list of giftcard services.
     *
     * @param int $storeId
     * @return \Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterface[]
     */
    public function getList($storeId);

    /**
     * Get list of active giftcard services.
     *
     * @param int $storeId
     * @return \Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterface[]
     */
    public function getActiveList($storeId);
}
