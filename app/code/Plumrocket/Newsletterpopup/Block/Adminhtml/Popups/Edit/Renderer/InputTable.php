<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\DataObject;

class InputTable extends Extended implements RendererInterface
{
    const HIDDEN_ELEMENT_CLASS = 'hidden-input-table';

    protected $_element;
    protected $_containerFieldId = null;
    protected $_rowKey = null;
    protected $_adminFieldClassName = null;

    protected $_collectionFactory;

    // ******************************************
    // *                                        *
    // *           Grid functions               *
    // *                                        *
    // ******************************************
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setMessageBlockVisibility(false);
    }

    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            $column['sortable'] = false;
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                    ->createBlock('Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\InputTable\Column')
                    ->setData($column)
                    ->setId($columnId)
                    ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new \Exception(__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;
        return $this;
    }

    public function canDisplayContainer()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        return Widget::_prepareLayout();
    }

    public function setArray($array)
    {
        $collection = $this->_collectionFactory->create();
        $i = 1;
        foreach ($array as $item) {
            if (!$item instanceof DataObject) {
                $item = new DataObject($item);
            }
            if (!$item->getId()) {
                $item->setId($i);
            }
            $collection->addItem($item);
            $i++;
        }
        $this->setCollection($collection);
        return $this;
    }

    public function getRowKey()
    {
        return $this->_rowKey;
    }

    public function setRowKey($key)
    {
        $this->_rowKey = $key;
        return $this;
    }

    public function getContainerFieldId()
    {
        return $this->_containerFieldId;
    }

    public function setContainerFieldId($name)
    {
        $this->_containerFieldId = $name;
        return $this;
    }

    /**
     * @return null
     */
    public function getAdminFieldClassName()
    {
        return $this->_adminFieldClassName;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setAdminFieldClassName($class)
    {
        $this->_adminFieldClassName = (string)$class;

        return $this;
    }

    // ******************************************
    // *                                        *
    // *           Render functions             *
    // *                                        *
    // ******************************************

    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        $adminFieldClass = $this->getAdminFieldClassName()
            ? 'field-' . $this->getAdminFieldClassName()
            : '';
        // Fix for magento 2.4
        $html = preg_replace('/<\/div>\n?<\/div>\s*?$/', '</div>', $this->_toHtml());
        $contentHtml = $this->_getCss() . $html . $this->_getNoteHtml();

        return '<div class="admin__field ' . $adminFieldClass . ' with-note">'
            . $this->getElement()->getLabelHtml()
            . '<div class="admin__field-control control">'
            . $contentHtml
            . '</div></div>';
    }

    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        // $html = preg_replace('/<div class="messages">.*<\/div>/i', '', $html);
        $html = preg_replace(
            '/(\s+class\s*=\s*["\'](?:\s*|[^"\']*\s+)messages)((?:\s*|\s+[^"\']*)["\'])/isU',
            '$1 ' . self::HIDDEN_ELEMENT_CLASS . ' $2',
            $html
        );
        $html = str_replace(
            '<div class="admin__data-grid-wrap',
            '<div id="' . $this->getHtmlId() . '_wrap" class="admin__data-grid-wrap',
            $html
        );

        return $html;
    }

    protected function _getCss()
    {
        $id = '#' . $this->getHtmlId() . '_wrap';
        return "<style>
            .messages." . self::HIDDEN_ELEMENT_CLASS . "{display:none}
            $id {
                margin-bottom: 0;
                padding-bottom: 0;
                padding-top: 0;
            }
            $id td {
                padding: 1rem;
                vertical-align: middle;
            }
            $id td input.checkbox[disabled] {
                display: none;
            }
            $id tr.not-active td,
            $id tr.not-active input.input-text,
            $id tr.not-active textarea.input-text {
                color: #999999;
            }
        </style>";
    }

    /**
     * @return string
     */
    private function _getNoteHtml()
    {
        return '<div class="note admin__field-note">' . $this->getElement()->getData('note') . '</div>';
    }
}
