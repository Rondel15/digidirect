<?php

namespace Digidirect\DigiDeals\Model\Layer;

class Resolver extends \Magento\Catalog\Model\Layer\Resolver
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Digidirect\DigiDeals\Model\Layer $layer,
        array $layersPool
    ) {
        $this->layer = $layer;
        parent::__construct($objectManager, $layersPool);
    }

    public function create($layerType)
    {
        //$this->layer gets set in the constructor, so this create function doesn't need to do anything.
    }
}
