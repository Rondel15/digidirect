<?php

namespace Digidirect\DealsTest\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function execute() {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set("Deals");
        return $resultPage;
    }
}
