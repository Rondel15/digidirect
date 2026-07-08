<?php

namespace Digidirect\Feed\Controller\Adminhtml\Rule;

use Digidirect\Feed\Controller\Adminhtml\Rule;

class Export extends Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $model = $this->initModel();
            $path = $model->export();
            $this->messageManager->addSuccessMessage(__('Filter rule exported to %1', $path));
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to export the rule.'));
        }

        return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
    }
}
