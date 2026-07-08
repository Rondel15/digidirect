<?php

namespace Digidirect\Feed\Controller\Adminhtml\Template;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Template;

class Edit extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $model = $this->initModel();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to get template.'));
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }

        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id = $this->getRequest()->getParam('id');
        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This template no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(
            $id ? $model->getName() : __('New Template')
        );

        return $resultPage;
    }
}
