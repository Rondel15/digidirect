<?php
namespace Digidirect\InfiniteScroll\Observer\Frontend;

use Digidirect\InfiniteScroll\Model\Handler;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class LayoutLoadBefore implements ObserverInterface
{
    /**
     * @var Handler
     */
    protected $_handler;

    /**
     * LayoutLoadBefore constructor.
     * @param Handler $handler
     */
    public function __construct(Handler $handler)
    {
        $this->_handler = $handler;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $action = $observer->getFullActionName();
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $this->_handler->init($action, $layout);
        return $this;
    }
}
