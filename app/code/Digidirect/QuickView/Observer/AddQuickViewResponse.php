<?php
namespace Digidirect\QuickView\Observer;

use Magento\Framework\View\Result\PageFactory;

/**
 * Class AddQuickViewResponse
 * @package Digidirect\QuickView\Observer
 */
class AddQuickViewResponse implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Digidirect\QuickView\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Digidirect\QuickView\Helper\ResponseHandler
     */
    protected $_responseHandler;

    /**
     * AddQuickViewResponse constructor.
     * @param \Digidirect\QuickView\Helper\Data $helper
     * @param \Digidirect\QuickView\Helper\ResponseHandler $_responseHandler
     */
    public function __construct(
        \Digidirect\QuickView\Helper\Data $helper,
        \Digidirect\QuickView\Helper\ResponseHandler $_responseHandler
    ) {
        $this->_helper = $helper;
        $this->_responseHandler = $_responseHandler;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controllerAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        if ($this->_helper->isModuleEnabled() && $controllerAction->getRequest()->getParam('quickview')) {
            $response = $controllerAction->getResponse();
            $this->_responseHandler->sendResponse($response);

            /** @todo eliminate usage of exit statement */
            exit;
        }
    }
}
