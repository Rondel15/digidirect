<?php

namespace Digidirect\Collect\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetQuoteItemQtyAfter implements ObserverInterface
{
    /**
     * Collect Helper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * QuoteItemHandler
     *
     * @var \Digidirect\Collect\Model\QuoteItemHandler
     */
    protected $_quoteItemHandler;

    /**
     * SetQuoteItemQtyAfter constructor.
     *
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Digidirect\Collect\Model\QuoteItemHandler $quoteItemHandler
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Digidirect\Collect\Model\QuoteItemHandler $quoteItemHandler
    ) {
        $this->_collectHelper = $collectHelper;
        $this->_request = $requestInterface;
        $this->_quoteItemHandler = $quoteItemHandler;
    }

    /**
     * Execute
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_collectHelper->isCollectEnable()) {
            $this->_quoteItemHandler->checkQty(
                $observer->getItem(),
                $this->_request->getParam('collect_place_id'),
                $this->_request->getParam('collect_place_storage_name')
            );
        }
    }
}
