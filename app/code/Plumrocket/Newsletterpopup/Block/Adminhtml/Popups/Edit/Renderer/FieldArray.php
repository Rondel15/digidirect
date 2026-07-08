<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class FieldArray extends AbstractFieldArray
{
    /**
     * @var string
     */
    const NETWORK_ID = 'sendy';

    /**
     * Prepare to render
     *
     * @var void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('list_id', ['label' => __('List ID'), 'class' => 'required-entry']);
        $this->addColumn('name', ['label' => __('List Name'), 'class' => 'required-entry']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add List');
    }
}
