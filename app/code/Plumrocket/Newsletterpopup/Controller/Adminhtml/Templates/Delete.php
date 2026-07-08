<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

class Delete extends Templates
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($this->_delete($id)) {
            $this->messageManager->addSuccess(__('The Theme has been deleted.'));
        } else {
            $this->messageManager->addError(__('The Theme has not been deleted.'));
        }
        $this->_redirect('*/*/index', ['_current' => true]);
    }
}
