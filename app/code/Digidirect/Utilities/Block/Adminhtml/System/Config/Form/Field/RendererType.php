<?php

namespace Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Field;

use Digidirect\Utilities\Model\System\Source;

/**
 * Class RendererType
 * @package Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Field
 */
class RendererType extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var Source
     */
    protected $_sourceModel;

    /**
     * RendererType constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param Source $_sourceModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Digidirect\Utilities\Model\System\Source $_sourceModel,
        array $data = []
    ) {
        $this->_sourceModel = $_sourceModel;
        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->_sourceModel->toOptionArray());
        }
        return parent::_toHtml();
    }
}
