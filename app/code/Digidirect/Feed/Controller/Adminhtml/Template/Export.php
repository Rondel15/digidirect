<?php

namespace Digidirect\Feed\Controller\Adminhtml\Template;

use Digidirect\Feed\Controller\Adminhtml\Template;

class Export extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();
        $path = $model->export();

        $this->messageManager->addSuccessMessage(
            __('Template "%1" has been exported to "%2"', $model->getName(), $path)
        );

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
