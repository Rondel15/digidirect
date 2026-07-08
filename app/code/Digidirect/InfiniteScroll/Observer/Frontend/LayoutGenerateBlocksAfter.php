<?php
namespace Digidirect\InfiniteScroll\Observer\Frontend;

use Digidirect\InfiniteScroll\Model\Handler;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class LayoutGenerateBlocksAfter implements ObserverInterface
{
    /**
     * @var Handler
     */
    protected $_handler;

    /**
     * LayoutGenerateBlocksAfter constructor.
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
        $this->_handler->handleRequest($action);
        return $this;
    }
}
