<?php
namespace Digidirect\Utilities\Block\Adminhtml\Config\Form\Field\Attribute\Renderer;

use Magento\Framework\Data\OptionSourceInterface as AttributeList;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class AbstractRenderer
 * @package Digidirect\Utilities\Block\Adminhtml\Config\Form\Field\Attribute\Renderer
 */
class AbstractRenderer extends Select
{
    /**
     * @var AttributeList
     */
    protected $_attributeLists;

    /**
     * @var bool
     */
    protected $_removeEmptyOption;

    /**
     * @var bool
     */
    protected $_isMultiple;

    /**
     * AttributeRenderer constructor.
     * @param Context $context
     * @param AttributeList $attributeList
     * @param array $data
     * @param bool $removeEmptyOption
     * @param bool $isMultiple
     */
    public function __construct(
        Context $context,
        AttributeList $attributeList,
        array $data = [],
        $removeEmptyOption = false,
        $isMultiple = false
    ) {
        parent::__construct($context, $data);
        $this->_attributeLists = $attributeList;
        $this->_removeEmptyOption = $removeEmptyOption;
        $this->_isMultiple = $isMultiple;
        $this->setIsRenderToJsTemplate(true);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        if ($this->_isMultiple) {
            $value .= '[]';
        }
        return $this->setName($value);
    }

    /**
     * Set Options before select will rendered
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            if (!$this->_removeEmptyOption) {
                $this->addOption('', __($this->getData('empty_option_label') ?: ' '));
            }

            foreach ($this->_attributeLists->toOptionArray() as $option) {
                if (empty($option['value'])) {
                    continue;
                }
                $this->addOption($option['value'], $option['label']);
            }
        }
        return parent::_beforeToHtml();
    }
}
