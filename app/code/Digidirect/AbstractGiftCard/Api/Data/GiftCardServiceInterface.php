<?php

namespace Digidirect\AbstractGiftCard\Api\Data;

/**
 * GiftCard service method interface.
 */
interface GiftCardServiceInterface
{
    /**
     * Get code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get store id.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get is active.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetServiceName)
     */
    public function isActive();
}
