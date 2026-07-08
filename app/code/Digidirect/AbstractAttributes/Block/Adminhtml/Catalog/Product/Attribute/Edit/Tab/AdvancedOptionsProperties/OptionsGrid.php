<?php
namespace Digidirect\AbstractAttributes\Block\Adminhtml\Catalog\Product\Attribute\Edit\Tab\AdvancedOptionsProperties;

/**
 * Class OptionsGrid
 * @package Digidirect\AbstractAttributes\Block\Adminhtml\Catalog\Product\Attribute\Edit\Tab\AdvancedOptionsProperties
 */
class OptionsGrid extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Digidirect_AbstractAttributes::catalog/product/attribute/options_grid.phtml';

    /**
     * Registry object
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attribute;

    /**
     * BrandsGrid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_registry = $registry;
    }

    /**
     * Check if attribute is new
     * @return bool
     */
    public function isNew()
    {
        return !$this->getAttributeObject()->getId();
    }

    /**
     * Get new brand url
     * @return string
     */
    public function getNewOptionUrl()
    {
        return $this->_urlBuilder->getUrl('eaa/option/newBackAttribute', [
            'attribute_id' => $this->getAttributeObject()->getId()
        ]);
    }

    /**
     * Retrieve attribute object from registry
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function getAttributeObject()
    {
        if (null === $this->_attribute) {
            $this->_attribute = $this->_registry->registry('entity_attribute');
        }
        return $this->_attribute;
    }
}
