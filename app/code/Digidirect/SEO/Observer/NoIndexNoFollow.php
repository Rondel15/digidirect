<?php
namespace Digidirect\SEO\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class NoIndexNoFollow implements ObserverInterface
{
    protected $request;

    protected $layoutFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Page\Config $layoutFactory)
    {
            $this->request = $request;
            $this->layoutFactory = $layoutFactory;
    }
    
    public function execute(Observer $observer)
    {
        $fullActionName = $observer->getFullActionName();
        /* Here we give for category page, you can chages as per your need */
        if ($fullActionName == "checkout_index_index")
        {
            $this->layoutFactory->setRobots('NOINDEX,NOFOLLOW');
        }
    }
}