<?php

namespace Digidirect\Digi\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

class SetCustomerLastOrderInfo implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SetCustomerLastOrderInfo constructor.
     * @param Session $customerSession
     * @param LoggerInterface $logger
     */
    public function __construct(CustomerSession $customerSession, LoggerInterface $logger)
    {
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->customerSession->getLastOrderRecord())
                $this->customerSession->unsLastOrderRecord();

            if ($this->customerSession->getTotalOrdersCount())
                $this->customerSession->unsTotalOrdersCount();


        } catch (\Throwable $e) {
            $this->logger->warning($e->__toString());
        }
    }
}
