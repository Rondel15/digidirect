<?php

namespace Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Field;

/**
 * Class MessageIdentifiers
 * @package Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Field
 */
class MessageIdentifiers extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var RendererType
     */
    protected $_typeRenderer;

    /**
     * @var string
     */
    protected $_blockType;

    /**
     * MessageIdentifiers constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param string $blockType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        $blockType,
        array $data = []
    ) {
        $this->_blockType = $blockType;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve type column renderer
     *
     * @return RendererType
     */
    protected function _getTypeRenderer()
    {
        if (!$this->_typeRenderer) {
            $this->_typeRenderer = $this->getLayout()->createBlock(
                $this->_blockType,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setClass('renderer_type_select');
        }
        return $this->_typeRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'renderer_type_id',
            ['label' => __('Type'), 'renderer' => $this->_getTypeRenderer()]
        );
        $this->addColumn('identifier', ['label' => __('Identifier')]);
        $this->addColumn('phrase', ['label' => __('Phrase')]);
        $this->addColumn('renderer_template', ['label' => __('Template')]);
        $this->addColumn('css_class', ['label' => __('Class')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Identifier');
    }

    /**
     * Prepare existing row data object
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getTypeRenderer()->calcOptionHash($row->getData('renderer_type_id'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
