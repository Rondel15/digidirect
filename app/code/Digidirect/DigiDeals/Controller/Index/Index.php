<?php

namespace Digidirect\DigiDeals\Controller\Index;

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
        $resultPage->getConfig()->getTitle()->set("Hot Deals and Discounts at digiDirect - Save Now!");
        $resultPage->getConfig()->setDescription("Find the hottest deals on cameras, lenses, and accessories at digiDirect. Save big with our limited-time offers. Shop now and enjoy massive discounts!");
        return $resultPage;
    }
}
