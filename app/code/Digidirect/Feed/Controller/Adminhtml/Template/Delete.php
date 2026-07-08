<?php

namespace Digidirect\Feed\Controller\Adminhtml\Template;

use Digidirect\Feed\Controller\Adminhtml\Template;

class Delete extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $model = $this->initModel();
            if ($model->getId()) {
                $this->templateRepository->delete($model);
                $this->messageManager->addSuccessMessage(__('The template has been deleted.'));
            } else {
                $this->messageManager->addErrorMessage(__('This template no longer exists.'));
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to delete the template.'));
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
    }
}
