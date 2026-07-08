<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\History;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\History;

class Mass extends History
{
    public function execute()
    {
        $action = $this->getRequest()->getParam('action');
        $ids = $this->getRequest()->getParam('history_id');

        if (is_array($ids) && $ids) {
            try {
                foreach ($ids as $id) {
                    switch ($action) {
                        case 'delete':
                            $this->_objectManager->create($this->_modelClass)->load($id)->delete();
                            break;
                    }
                }
                $messages =
                [
                    'delete'    => 'Total of %s record(s) were successfully deleted'
                ];

                $this->messageManager->addSuccess(__($messages[$action], count($ids)));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Please select item(s)'));
        }
        $this->_redirect('*/*/index');
    }
}
