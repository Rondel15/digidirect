<?php
namespace Digidirect\ProductOverlay\Block;

use Digidirect\ProductOverlay\Helper\Data;

/**
 * Class Stock
 * @package Digidirect\ProductOverlay\Block
 */
class Stock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $overlayHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Stock constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Digidirect\ProductOverlay\Helper\Data $overlayHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Digidirect\ProductOverlay\Helper\Data $overlayHelper,
        array $data = []
    ) {
        $this->overlayHelper = $overlayHelper;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getStockLabels()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->coreRegistry->registry('product');
        $labels = [];
        if (is_object($product) && $product->getId()) {
            $labels = $this->overlayHelper->getStockLabels($product, Data::MODE_PRODUCT);
        }
        return $labels;
    }
}
