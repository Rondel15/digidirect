<?php

namespace Digidirect\ProductOverlay\Plugin\Catalog\Product\View;

/**
 * Class Overlay
 * @package Digidirect\ProductOverlay\Plugin\Catalog\Product\View
 */
class Overlay
{
    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Overlay constructor.
     * @param \Digidirect\ProductOverlay\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Digidirect\ProductOverlay\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_helper      = $helper;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param mixed $result
     * @return string
     */
    public function afterToHtml(\Magento\Catalog\Block\Product\View\Gallery $subject, $result)
    {
        $product = $subject->getProduct();
        $name    = $subject->getNameInLayout();
        if ($product && $name == "product.info.media.image") {
            $result .= $this->_helper->renderProductOverlay($product, 'product');
        }

        return $result;
    }
}
