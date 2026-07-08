<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Label extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();
        $this->setType('label');
    }

    public function getHtml()
    {
        return '
            <div id="popup_code_container"' . ($this->getHidden() ? ' style="display: none;"': '') . ' class="messages">
                <div class="message message-notice notice">
                    <div data-ui-id="messages-message-notice">
                        ' . $this->getValue() . '
                    </div>
                </div>
            </div>';
    }
}
