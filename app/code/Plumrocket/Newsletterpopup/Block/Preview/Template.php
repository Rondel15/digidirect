<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Preview;

use Plumrocket\Newsletterpopup\Block\Template as TemplateBase;

class Template extends TemplateBase
{
    protected $_layoutBased = true;
    protected $_popup = null;

    protected function _isEnabled()
    {
        return true;
    }

    protected function _cacheInit()
    {
        return false;
    }

    public function getPopup()
    {
        if (null === $this->_popup) {
            $request = $this->getRequest();

            $id = (int)$request->getParam('id');
            if (!$id) {
                $id = (int)$request->getParam('entity_id');
            }

            if ($request->getParam('is_template')) {
                $this->_popup = $this->_dataHelper->getPopupTemplateById($id);
            } else {
                $this->_popup = $this->_dataHelper->getPopupById($id);
            }
        }
        return $this->_popup;
    }
}
