<?php
namespace Digidirect\LayeredNavigation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category;

class ProductList extends Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * @return bool
     */
    public function canShowProductList()
    {
        $category = $this->getCurrentCategory();
        if ($category instanceof Category && $category->getDisplayMode()) {
            return in_array($category->getDisplayMode(), [Category::DM_PRODUCT, Category::DM_MIXED]);
        }
        return true;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->getRequest()->getControllerName() === 'category' && $this->getCurrentCategory()) {
            return $this->getCurrentCategory()->getUrl();
        }

        return $this->getUrl('*/*/*', ['_current' => false, '_use_rewrite' => true]);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canShowProductList()) {
            return '';
        }
        return parent::_toHtml();
    }
}
