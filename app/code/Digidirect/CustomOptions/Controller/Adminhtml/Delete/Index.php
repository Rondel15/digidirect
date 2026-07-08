<?php
namespace Digidirect\CustomOptions\Controller\Adminhtml\Delete;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
         protected $resultPageFactory = false;      
         protected $customOption;
         
         public function __construct(
                 \Magento\Backend\App\Action\Context $context,
                 \Magento\Framework\View\Result\PageFactory $resultPageFactory,
                 \Digidirect\CustomOptions\Model\CustomOption $customOption
         ) {            
                 $this->customOption = $customOption;
                 $this->resultPageFactory = $resultPageFactory;
                 parent::__construct($context);
         } 
         public function execute()
         {
            //$this->customOption->deleteCustomOption();
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Magento_Catalog::catalog');
            $resultPage->getConfig()->getTitle()->prepend(__('Customizable Options Deleted'));
            return $resultPage;

         }
         
         protected function _isAllowed()
         {
                 return $this->_authorization->isAllowed('Magento_Catalog::catalog');
         }
}