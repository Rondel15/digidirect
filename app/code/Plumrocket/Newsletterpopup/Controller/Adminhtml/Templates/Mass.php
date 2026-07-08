<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

class Mass extends Templates
{
    public function execute()
    {
        $action = $this->getRequest()->getParam('action');
        $ids = $this->getRequest()->getParam('template_id');

        if (is_array($ids) && $ids) {
            try {
                foreach ($ids as $n => $id) {
                    switch ($action) {
                        case 'delete':
                            if (!$this->_delete($id)) {
                                unset($ids[$n]);
                                if (empty($_sendNotification)) {
                                    $this->messageManager->addError(__('One or more themes were not removed. They are either "default themes" or being used in popups at the moment.'));
                                    $_sendNotification = true;
                                }
                            }
                            break;
                        case 'duplicate':
                            $this->_duplicate($id);
                            break;
                    }
                }
                $messages =
                [
                    'delete'    => 'Total of %1 record(s) were successfully deleted',
                    'duplicate'    => 'Total of %1 record(s) were successfully duplicated',
                ];

                if ($ids) {
                    $this->messageManager->addSuccess(__($messages[$action], count($ids)));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Please select item(s)'));
        }
        $this->_redirect('*/*/index');
    }
}
