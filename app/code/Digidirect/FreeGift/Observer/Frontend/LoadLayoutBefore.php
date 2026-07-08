<?php

namespace Digidirect\FreeGift\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Digidirect\FreeGift\Helper\Data as DataHelper;

class LoadLayoutBefore implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var array
     */
    protected $_hideHiddenFreeGiftOn;

    /**
     * LoadLayoutBefore constructor.
     * @param Registry $registry
     * @param array $hideHiddenFreeGiftOn
     */
    public function __construct(
        Registry $registry,
        array $hideHiddenFreeGiftOn = []
    ) {
        $this->_registry = $registry;
        $this->_hideHiddenFreeGiftOn = $hideHiddenFreeGiftOn;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $fullActionName = $observer->getEvent()->getData('full_action_name');
        $fullActionName = strtolower($fullActionName);

        if (isset($this->_hideHiddenFreeGiftOn['order_item'])) {
            if (in_array($fullActionName, $this->_hideHiddenFreeGiftOn['order_item'])) {
                $this->_registry->register(DataHelper::REGISTRY_HIDE_HIDDEN_FREE_GIFT_ORDER_ITEMS, true, true);
            }
        }
    }
}
