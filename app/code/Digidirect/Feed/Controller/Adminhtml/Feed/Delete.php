<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Digidirect\Feed\Controller\Adminhtml\Feed;

class Delete extends Feed
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
                $this->feedRepository->delete($model);
                $this->messageManager->addSuccessMessage(__('The feed has been deleted.'));
            } else {
                $this->messageManager->addErrorMessage(__('This feed no longer exists.'));
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to delete the feed.'));
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
    }
}
