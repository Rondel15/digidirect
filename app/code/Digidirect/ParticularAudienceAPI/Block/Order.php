<?php

namespace Digidirect\ParticularAudienceAPI\Block;

class Order {
    
    protected $logger;
    
    protected $orderFactory;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
    }

    public function getOrderById($id) {
        $order = $this->orderFactory->create()->loadByIncrementId($id);
    }
    
}