<?php
namespace Digidirect\QuickView\Observer;

/**
 * Class AddQuickViewHandle
 * @package Digidirect\QuickView\Observer
 */
class AddQuickViewHandle implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Digidirect\QuickView\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * AddQuickViewHandle constructor.
     * @param \Digidirect\QuickView\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Digidirect\QuickView\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Add quick view handle
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isModuleEnabled() && $this->request->getParam('quickview')) {
            /** @var \Magento\Framework\View\Layout $layout */
            $layout = $observer->getLayout();
            $layout->getUpdate()->addHandle('quickview_' . $observer->getFullActionName());
        }
    }
}
