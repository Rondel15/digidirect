<?php

namespace Digidirect\Collect\Observer;

use Digidirect\Collect\Helper\Data as CollectHelper;
use Magento\Framework\Event\ObserverInterface;

class UpdateCollectPlaceData implements ObserverInterface
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * QuoteItemHandler
     *
     * @var \Digidirect\Collect\Model\QuoteItemHandler
     */
    protected $_quoteItemHandler;

    /**
     * SetCollectPlaceData constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param CollectHelper $collectHelper
     * @param \Digidirect\Collect\Model\QuoteItemHandler $quoteItemHandler
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Digidirect\Collect\Model\QuoteItemHandler $quoteItemHandler
    ) {
        $this->_request = $requestInterface;
        $this->_collectHelper = $collectHelper;
        $this->_quoteItemHandler = $quoteItemHandler;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return  void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_collectHelper->isCollectEnable()) {
            $this->_quoteItemHandler->changeQuoteItemCollectType(
                $observer->getQuoteItem(),
                $this->_request->getParam('delivery_type'),
                $this->_request->getParam('collect_place_id'),
                $this->_request->getParam('collect_place_storage_name')
            );
        }
    }
}
