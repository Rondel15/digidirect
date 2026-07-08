<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

class Edit extends Popups
{
    public function _editAction()
    {
        $model = $this->_getModel();

        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu('Plumrocket_Newsletterpopup::prnewsletterpopup');

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit Popup');
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New Popup');
            $breadcrumbLabel = __('Create Popup');
        }

        if ($model->getId()) {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                __(
                    'Edit Popup "%1"',
                    htmlspecialchars($model->getName())
                )
            );
        } else {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Popup'));
        }

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData($this->_formSessionKey, true);
        if ($values) {
            $model->addData($values);
        }

        $this->_view->renderLayout();
    }
}
