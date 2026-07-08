<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute as DynamicAttribute;

class Edit extends DynamicAttribute
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id = $this->getRequest()->getParam('id');
        $model = $this->initModel();

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This item not exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Attribute'));

        return $resultPage;
    }
}
