<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

class Delete extends Popups
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($this->_delete($id)) {
            $this->messageManager->addSuccess(__('The Popup has been deleted.'));
        } else {
            $this->messageManager->addError(__('The Popup has not been deleted.'));
        }
        $this->_redirect('*/*/index', ['_current' => true]);
    }
}
