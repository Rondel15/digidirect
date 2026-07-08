<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

class Duplicate extends Popups
{
    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $newId = $this->_duplicate($id);
                if ($newId && ($id != $newId)) {
                    $id = $newId;
                    $this->messageManager->addSuccess(__('The Popup has been duplicated.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __($e->getMessage()));
            }
        }
        $this->_redirect('*/*/edit', ['id' => $id]);
    }
}
