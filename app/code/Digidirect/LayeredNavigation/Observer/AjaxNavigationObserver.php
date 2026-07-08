<?php
namespace Digidirect\LayeredNavigation\Observer;

use Digidirect\LayeredNavigation\Model\AjaxHandler;
use Magento\Framework\Event\ObserverInterface;

class AjaxNavigationObserver implements ObserverInterface
{
    /**
     * @var AjaxHandler
     */
    protected $ajaxHandler;

    /**
     * Class constructor
     * @param AjaxHandler $ajaxHandler
     */
    public function __construct(
        AjaxHandler $ajaxHandler
    ) {
        $this->ajaxHandler = $ajaxHandler;
    }

    /**
     * Execute
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $observer->getEvent()->getLayout();
        $this->ajaxHandler->handleLayout($layout);
    }
}
