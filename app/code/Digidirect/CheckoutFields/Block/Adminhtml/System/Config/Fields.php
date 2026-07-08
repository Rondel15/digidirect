<?php

namespace Digidirect\CheckoutFields\Block\Adminhtml\System\Config;

use \Magento\Backend\Block\Template\Context;
use \Digidirect\CheckoutFields\Block\Adminhtml\System\Config\Fields\Checkbox;
use \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use \Magento\Framework\DataObject;

/**
 * Class Fields
 *
 * @package Digidirect\CheckoutFields\Block\Adminhtml\System\Config
 */
class Fields extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    const HIDE_ON_STOREFRONT = 'hide_on_storefront';
    const ACTIVE = 'active';
    const SHOW_ON_PDP = 'show_on_pdp';
    const ALLOW_EDIT_ON_ORDER_DETAIL_PAGE = 'allow_edit_on_order_detail_page';

    /**
     * @var string
     */
    protected $_template = 'system/config/form/field/array.phtml';

    /**
     * @var Checkbox
     */
    protected $checkboxRenderer;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * Fields constructor.
     *
     * @param Context $context
     * @param Checkbox $checkboxRenderer
     * @param Parser $parser
     * @param array $data
     */
    public function __construct(Context $context, Checkbox $checkboxRenderer, Parser $parser, array $data = [])
    {
        $this->checkboxRenderer = $checkboxRenderer;
        $this->parser = $parser;
        parent::__construct($context, $data);
    }

    /**
     * Add columns
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'code',
            ['label' => __('Field Code'), 'renderer' => false, 'class' => 'code_disabled']
        );
        $this->addColumn(
            self::ACTIVE,
            ['label' => __('Active'), 'renderer' => $this->checkboxRenderer]
        );
        $this->addColumn(
            self::HIDE_ON_STOREFRONT,
            ['label' => __('Hide on storefront'), 'renderer' => $this->checkboxRenderer]
        );
        $this->addColumn(
            self::SHOW_ON_PDP,
            ['label' => __('Show on PDP'), 'renderer' => $this->checkboxRenderer]
        );
        $this->addColumn(
            self::ALLOW_EDIT_ON_ORDER_DETAIL_PAGE,
            ['label' => __('Allow Edit On Order Detail Page In Backoffice'), 'renderer' => $this->checkboxRenderer]
        );

        $this->addMoreColumns();
    }

    /**
     * @todo Add styles to css of the module instead of this
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value" style="width: 66%">';
            $html .= $this->_getElementHtml($element);
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';
        return $html;
    }

    /**
     * @return void
     */
    public function addMoreColumns()
    {
        /* Opportunity to add more columns in the plugin */
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of \Magento\Framework\DataObject
     *
     * @return array
     */
    public function getArrayRows()
    {
        $fields = $this->_getFields();
        if ($values = $this->getElement()->getValue()) {
            if (!is_array($values)) {
                $values = unserialize($values);
            }
            foreach ($values as $key => $value) {
                if (isset($fields[$key])) {
                    foreach ($value as $k => $v) {
                        $fields[$key] = array_merge($fields[$key], $this->prepareValuesForArrayRows($k, $v));
                    }
                }
            }
        }
        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        foreach ($fields as $rowId => $row) {
            $rowColumnValues = [];
            foreach ($row as $key => $value) {
                $row[$key] = $value;
                $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
            }
            $row['_id'] = $rowId;
            $row['column_values'] = $rowColumnValues;
            $result[$rowId] = new DataObject($row);
            $this->_prepareArrayRow($result[$rowId]);
        }
        return $result;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function prepareValuesForArrayRows($key, $value)
    {
        // @todo type of a config field (i.e. hide_on_storefront => checkbox)
        return [$key => ['checked' => $value]];
    }

    /**
     * Get fields
     *
     * @return []
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _getFields()
    {
        $fields = $this->parser->getAllFields();
        $result = [];
        foreach (array_keys($fields) as $code) {
            $result[$code] = [
                'code' => [
                    'value' => $code,
                ],
                'active' => [],
            ];
        }
        return $result;
    }
}
