<?php

namespace Digidirect\Collect\Model\Plugin\Quote\Item;

use Closure;

class AroundConvert
{
    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * AroundConvert constructor.
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     */
    public function __construct(\Digidirect\Collect\Helper\Data $collectHelper)
    {
        $this->_collectHelper = $collectHelper;
    }

    /**
     * AroundConvert
     *
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param [] $additional
     * @return \Magento\Sales\Model\Order\Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);

        if ($this->_collectHelper->isCollectEnable()) {
            $orderItem->setCollectPlaceId($item->getCollectPlaceId());
            $orderItem->setCollectPlaceStorageName($item->getCollectPlaceStorageName());
        }

        return $orderItem;
    }
}
