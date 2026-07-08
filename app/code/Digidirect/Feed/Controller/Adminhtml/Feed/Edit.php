<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Feed;

class Edit extends Feed
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
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to get feed.'));
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }

        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id = $this->getRequest()->getParam('id');
        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This feed no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(
            $model->getName() ? $model->getName() : __('New Feed')
        );

        return $resultPage;
    }
}
