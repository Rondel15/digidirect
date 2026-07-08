<?php
namespace Digidirect\ShopByBrandMenu\Controller\Index;
 
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
 
 
class ViewMobile extends Action
{
 
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
 
 
    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, JsonFactory $resultJsonFactory)
    {
 
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
 
        parent::__construct($context);
    }
 
 
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $categoryId = $this->getRequest()->getParam('categoryId');
        
 
        $data = array('categoryId' => $categoryId);
 
        $block = $resultPage->getLayout()
                ->createBlock('Digidirect\ShopByBrandMenu\Block\Index\ViewMobile')
                ->setTemplate('Digidirect_ShopByBrandMenu::view-mobile.phtml')
                ->setData('data',$data)
                ->toHtml();
 
        $result->setData(['output' => $block]);
        return $result;
        
    }
 
}