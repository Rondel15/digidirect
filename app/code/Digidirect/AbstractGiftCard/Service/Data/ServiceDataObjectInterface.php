<?php

namespace Digidirect\AbstractGiftCard\Service\Data;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;

/**
 * Interface ServiceDataObjectInterface
 * @package Digidirect\AbstractGiftCard\Service\Data
 * @api
 */
interface ServiceDataObjectInterface
{
    /**
     * Returns order
     *
     * @return OrderAdapterInterface
     */
    public function getOrder();

    /**
     * Returns service
     *
     * @return ServiceInterface
     */
    public function getService();
}
