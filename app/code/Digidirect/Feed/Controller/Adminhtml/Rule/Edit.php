<?php

namespace Digidirect\Feed\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Rule;
use Magento\Framework\Webapi\Exception;

class Edit extends Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id = $this->getRequest()->getParam('id');
        try {
            $model = $this->initModel();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to get rule.'));
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }

        if ($this->getRequest()->getParam('type')) {
            $model->setType($this->getRequest()->getParam('type'));
        }

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This item not exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Filter'));

        return $resultPage;
    }
}
