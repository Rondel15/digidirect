<?php

namespace Digidirect\Feed\Controller\Adminhtml\Template;

use Digidirect\Feed\Controller\Adminhtml\Template;

class MassExport extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $templateIds = $this->getRequest()->getParam('template', []);
            foreach ($templateIds as $templateId) {
                $model = $this->templateRepository->getById($templateId);
                $path = $model->export();
                $this->messageManager->addSuccessMessage(
                    __('Template "%1" has been exported to "%2"', $model->getName(), $path)
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to export template.'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
