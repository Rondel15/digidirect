<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @deprecated since 2.5.1
 * @see \Plumrocket\Newsletterpopup\Plugin\CouponExpiredPlugin
 */
class CoreCollectionAbstractLoadBeforeObserver implements ObserverInterface
{

    /**
     * Filter expired rules.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
    }
}
