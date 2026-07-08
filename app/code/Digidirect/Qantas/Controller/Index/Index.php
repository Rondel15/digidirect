<?php
/**
 *
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digi\Qantas\Controller\Index;


class Index extends \Magento\Framework\App\Action\Action
{
  
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
 
    
    
    public function __construct(
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Action\Context $context
       
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        
    }

    
    public function execute()
    {
        
       $resultPage = $this->_resultPageFactory->create();
       $resultPage->addHandle('qantas_index_index'); //loads the layout of module_custom_customlayout.xml file with its name
       
       
       return $resultPage;
     
    }
  
    
}