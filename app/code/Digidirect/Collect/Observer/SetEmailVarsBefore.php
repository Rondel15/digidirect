<?php

namespace Digidirect\Collect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Digidirect\Collect\Helper\Data as CollectHelper;

class SetEmailVarsBefore implements ObserverInterface
{
    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * SetEmailVarsBefore constructor.
     *
     * @param CollectHelper $collectHelper
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper
    ) {
        $this->_collectHelper = $collectHelper;
    }

    /**
     * Execute
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_collectHelper->isCollectEnable()) {
            $transport = $observer->getTransport();
            $order = $transport->getOrder();
            $collectDescription = $this->_collectHelper->getOrderCollectDescription($order);
            if (is_array($collectDescription)) {
                $collectDescription = implode(',', $collectDescription);
            }
            $transport->addData(['collectDescription' => $collectDescription]);
        }
    }
}
