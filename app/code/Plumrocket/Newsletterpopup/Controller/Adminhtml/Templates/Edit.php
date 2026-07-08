<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

use Plumrocket\Newsletterpopup\Controller\Adminhtml\Templates;

class Edit extends Templates
{
    public function _editAction()
    {
        $model = $this->_getModel();

        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit '.$this->_objectTitle);
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New '.$this->_objectTitle);
            $breadcrumbLabel = __('Create '.$this->_objectTitle);
        }

        if ($model->getId()) {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                __(
                    'Edit ' . $this->_objectTitle . ' "%1"',
                    htmlspecialchars(ucfirst($model->getName()))
                )
            );
        } else {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                __('New ' . $this->_objectTitle)
            );
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
