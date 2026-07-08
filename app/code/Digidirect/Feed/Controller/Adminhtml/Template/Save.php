<?php

namespace Digidirect\Feed\Controller\Adminhtml\Template;

use Digidirect\Feed\Controller\Adminhtml\Template;

class Save extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            try {
                $model = $this->initModel();
                $model->addData($data);
                $this->templateRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the template.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while trying to save the templates.')
                );
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('No data to save.'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
