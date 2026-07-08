<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Model\HistoryFactory;
use Plumrocket\Newsletterpopup\Model\PopupFactory;

class SaveOrderObserver implements ObserverInterface
{

    /**
     * @var \Plumrocket\Newsletterpopup\Model\HistoryFactory
     */
    private $_historyFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\PopupFactory
     */
    private $_popupFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Plumrocket\Newsletterpopup\Model\HistoryFactory $historyFactory
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory   $popupFactory
     * @param \Plumrocket\Newsletterpopup\Helper\Config        $config
     */
    public function __construct(
        HistoryFactory $historyFactory,
        PopupFactory $popupFactory,
        Config $config
    ) {
        $this->_historyFactory = $historyFactory;
        $this->_popupFactory = $popupFactory;
        $this->config = $config;
    }

    /**
     * Add using coupon code to history.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (! $this->config->isModuleEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();

        if ($code = $order->getCouponCode()) {
            $email = $order->getCustomerEmail();
            $historyItem = null;
            $history = $this->_historyFactory->create();

            if ($email) {
                $historyItem = $history
                    ->getCollection()
                    ->addFieldToFilter('customer_email', $email)
                    ->addFieldToFilter('coupon_code', $code)
                    ->getFirstItem();
            }

            if (null === $historyItem || !$historyItem->getId()) {
                $historyItem = $history->load($code, 'coupon_code');
            }

            if ($historyItem->getId()) {
                if ($order->getState() == Order::STATE_CANCELED || $order->getState() == Order::STATE_HOLDED) {
                    $this->_save($historyItem, 0, 0, 0);
                } else {
                    $this->_save(
                        $historyItem,
                        $order->getIncrementId(),
                        $order->getId(),
                        $order->getGrandTotal()
                    );
                }
            }
        }
    }

    private function _save($historyItem, $incrementId, $orderId, $grandTotal)
    {
        $boolHistoryGT = $historyItem->getData('grand_total') > 0;
        $boolGT = $grandTotal > 0;

        if ($boolGT !== $boolHistoryGT) {
            $popup = $this->_popupFactory->create()->load($historyItem->getPopupId());
            // check if linked popup exists

            if ($popup->getId()) {
                $tr = $grandTotal?
                    $popup->getData('total_revenue') + $grandTotal:
                    $popup->getData('total_revenue') - $historyItem->getData('grand_total');
                $addOrdersCount = $grandTotal? 1: -1;

                $popup
                    ->setData('orders_count', $popup->getData('orders_count') + $addOrdersCount)
                    ->setData('total_revenue', $tr)
                    ->save();
            }

            $historyItem
                ->setData('increment_id', $incrementId)
                ->setData('order_id', $orderId)
                ->setData('grand_total', $grandTotal)
                ->save();
        }
    }
}
