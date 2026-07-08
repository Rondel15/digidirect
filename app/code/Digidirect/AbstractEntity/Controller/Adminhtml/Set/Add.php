<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Set;

use Digidirect\AbstractEntity\Controller\Adminhtml\Set;
use Digidirect\AbstractEntity\Block\Adminhtml\Set\Toolbar\Add as AddBlock;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Add extends Set
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param AbstractEntityResource $abstractEntityResource
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AbstractEntityResource $abstractEntityResource,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry, $abstractEntityResource);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_setTypeId();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Catalog::catalog_attributes_sets');
        $resultPage->getConfig()->getTitle()->prepend(__('New Abstract Entity'));
        $resultPage->addContent($resultPage->getLayout()->createBlock(AddBlock::class));
        return $resultPage;
    }
}
