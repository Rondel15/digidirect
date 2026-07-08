<?php

namespace Digidirect\Utilities\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class Relation
 * @package Digidirect\Utilities\Block\Adminhtml\Config\Form\Field
 */
class Relation extends AbstractFieldArray
{

    const RENDERER_CLASS_ID = 'class';
    const RENDERER_CLASS_LABEL = 'label';
    const RENDERER_CLASS_CSS = 'css';
    const RENDERER_CLASS_STYLE = 'style';

    /**
     * injectable config
     * @var array
     */
    protected $_config;

    /**
     * @var bool
     */
    private $validate = false;

    /**
     * Relation constructor.
     * @param Context $context
     * @param array $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $config = [],
        array $data = []
    ) {
        $this->_config = $config;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getRenderer()
    {
        if (!$this->validate) {

            $this->_getRenderer();
        }
        return $this->_config;
    }

    /**
     * @return $this
     */
    protected function _getRenderer()
    {
        foreach ($this->_config as $key => $value) {

            if (!isset($value[self::RENDERER_CLASS_ID])) {

                $this->_config[$key][self::RENDERER_CLASS_ID] = false;
                continue;
            }
            if (is_object($value[self::RENDERER_CLASS_ID]) &&
                !($value[self::RENDERER_CLASS_ID] instanceof BlockInterface)) {

                unset($this->_config[$key]);
            }
        }
        $this->validate = true;
        return $this;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        foreach ($this->getRenderer() as $key => $value) {

            $labelCode = self::RENDERER_CLASS_LABEL;
            $classCode = self::RENDERER_CLASS_CSS;
            $styleCode = self::RENDERER_CLASS_STYLE;

            $label = isset($value[$labelCode]) ?
                __($value[$labelCode]) : __(strtoupper($key));

            $class = isset($value[$classCode]) ? $value[$classCode] : '';
            $style = isset($value[$styleCode]) ? $value[$styleCode] : '';

            $this->addColumn(
                $key . '_column',
                [
                    'label' => $label,
                    'renderer' => $value[self::RENDERER_CLASS_ID],
                    'class' => $class,
                    'style' => $style,
                ]
            );
        }

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        foreach ($this->getRenderer() as $key => $value) {
            $renderer = $value[self::RENDERER_CLASS_ID];
            if (is_object($renderer) && method_exists($renderer, 'calcOptionHash')) {
                $columnName = $key . '_column';
                $optionValues = $row->getData($columnName);
                if (!is_array($optionValues)) {
                    $optionValues = [$optionValues];
                }
                $inputName = $this->_getCellInputElementName($columnName);
                $renderer
                    ->setInputName($inputName)
                    ->setInputId($this->_getCellInputElementId('<%- _id %>', $columnName))
                    ->setColumnName($columnName)
                    ->setColumn($this->_columns[$columnName]);
                foreach ($optionValues as $optionValue) {
                    $optionExtraAttr['option_' . $renderer->calcOptionHash($optionValue)] = 'selected="selected"';
                }
            }
        }

        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
