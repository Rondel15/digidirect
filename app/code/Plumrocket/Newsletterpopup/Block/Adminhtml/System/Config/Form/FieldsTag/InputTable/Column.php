<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\FieldsTag\InputTable;

use Magento\Backend\Block\Widget\Grid\Column\Extended;
use Magento\Framework\DataObject;

class Column extends Extended
{
    protected $_rowKeyValue = null;

    public function getId()
    {
        return $this->getName();
    }

    public function getRowField(DataObject $row)
    {
        if (null !== $this->getGrid()->getRowKey()) {
            $this->_rowKeyValue = $row->getData($this->getGrid()->getRowKey());
        }
        if (!$this->_rowKeyValue) {
            return '';
        }
        return parent::getRowField($row);
    }

    public function getFieldName()
    {
        return $this->getName();
    }

    public function getHtmlName()
    {
        return $this->getName();
    }

    public function getName()
    {
        return sprintf(
            '%s[%s][%s]',
            $this->getGrid()->getContainerFieldId(),
            $this->_rowKeyValue,
            parent::getId()
        );
    }
}
