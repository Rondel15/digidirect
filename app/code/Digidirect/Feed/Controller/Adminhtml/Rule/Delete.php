<?php

namespace Digidirect\Feed\Controller\Adminhtml\Rule;

use Digidirect\Feed\Controller\Adminhtml\Rule;

class Delete extends Rule
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
                $this->ruleRepository->delete($model);
                $this->messageManager->addSuccessMessage(__('Item was successfully deleted'));
            } else {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to delete the rule.'));
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
    }
}
