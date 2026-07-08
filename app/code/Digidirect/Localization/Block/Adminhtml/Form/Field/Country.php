<?php

namespace Digidirect\Localization\Block\Adminhtml\Form\Field;

use Magento\Directory\Model\Config\Source\Country as directoryCountry;

/**
 * HTML select element block with country options
 */
class Country extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_country;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param directoryCountry $directoryCountry
     * @param array data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        directoryCountry $directoryCountry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_country = $directoryCountry;
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
            $this->setOptions($this->_country->toOptionArray());
        }
        return parent::_toHtml();
    }
}
