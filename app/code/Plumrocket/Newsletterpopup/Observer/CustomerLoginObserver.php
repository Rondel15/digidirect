<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\Data;

class CustomerLoginObserver implements ObserverInterface
{

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Plumrocket\Newsletterpopup\Helper\Data   $dataHelper
     * @param \Plumrocket\Newsletterpopup\Helper\Config $config
     */
    public function __construct(
        Data $dataHelper,
        Config $config
    ) {
        $this->dataHelper = $dataHelper;
        $this->config = $config;
    }

    /**
     * Set visitor id cookie
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (! $this->config->isModuleEnabled()) {
            return;
        }

        if ($customer = $observer->getCustomer()) {
            $this->dataHelper->visitorId($customer->getId());
        }
    }
}
