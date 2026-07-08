<?php

namespace Digidirect\Feed\Controller\Adminhtml\Rule;

use Digidirect\Feed\Controller\Adminhtml\Rule;

class Duplicate extends Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();
            $model->duplicate();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to duplicate the rule.'));
            return $resultRedirect->setPath('*/*/');
        }

        $this->messageManager->addSuccessMessage(__('Rule was successfully duplicated'));
        return $resultRedirect->setPath('*/*/');
    }
}
