<?php

namespace Digidirect\FreeGift\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Digidirect\FreeGift\Helper\Data as DataHelper;

class ControllerActionPredispatchSalesOrderReorder implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * LoadLayoutBefore constructor.
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->_registry = $registry;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->_registry->register(DataHelper::REGISTRY_HIDE_FREE_GIFT_ORDER_ITEMS, true, true);
    }
}
