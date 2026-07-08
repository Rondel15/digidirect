<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;

use Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;

class Delete extends Category
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();
            $model->getResource()->delete($model);

            $this->messageManager->addSuccessMessage(__('Item was successfully deleted'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while trying to delete the category mapping.')
            );
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
