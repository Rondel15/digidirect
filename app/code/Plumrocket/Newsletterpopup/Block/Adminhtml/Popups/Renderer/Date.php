<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Date extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $fieldName = $this->getColumn()->getIndex();
        $value = $row->getData($fieldName);

        if ($value) {
            if ($value == '0000-00-00 00:00:00') {
                $value = '';
            } else {
                $value = $this->formatDate($value, \IntlDateFormatter::MEDIUM);
            }
        }
        return (string)$value;
    }
}
