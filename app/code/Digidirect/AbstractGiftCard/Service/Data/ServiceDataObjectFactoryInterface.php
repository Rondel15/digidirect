<?php

namespace Digidirect\AbstractGiftCard\Service\Data;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;

interface ServiceDataObjectFactoryInterface
{
    /**
     * Creates Service Data Object
     *
     * @param ServiceInterface $serviceInfo
     * @return ServiceDataObjectInterface
     */
    public function create(ServiceInterface $serviceInfo);
}
