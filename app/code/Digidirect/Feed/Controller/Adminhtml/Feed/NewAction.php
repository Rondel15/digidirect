<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Digidirect\Feed\Controller\Adminhtml\Feed;

class NewAction extends Feed
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setPath('*/*/edit');
    }
}
