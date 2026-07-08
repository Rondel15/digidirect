<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;

class Edit extends Category
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initPage($resultPage);

        $id = $this->getRequest()->getParam('id');
        $model = $this->initModel();

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This item not exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Mapping'));

        return $resultPage;
    }
}
