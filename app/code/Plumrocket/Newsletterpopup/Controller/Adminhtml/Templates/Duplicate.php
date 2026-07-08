<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

class Duplicate extends Templates
{
    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $newId = $this->_duplicate($id);
                if ($newId && ($id != $newId)) {
                    $id = $newId;
                    $this->messageManager->addSuccess(__('The Theme has been duplicated.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __($e->getMessage()));
            }
        }
        $this->_redirect('*/*/edit', ['id' => $id]);
    }
}
