<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Magento\Backend\App\Action;

class Index extends AbstractEntityController
{
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $attributeSet = $this->_initAttributeSet();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE_PREFIX . $this->_initAttributeSet()->getAttributeSetId());
        $resultPage->getConfig()->getTitle()->prepend($attributeSet->getAttributeSetName());
        return $resultPage;
    }
}
