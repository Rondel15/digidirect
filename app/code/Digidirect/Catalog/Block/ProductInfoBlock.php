<?php
namespace Digidirect\Catalog\Block;

class ProductInfoBlock extends \Magento\Framework\View\Element\Template
{
    protected $_registry;
    protected $_productRepository;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    )
    {        
        $this->_registry = $registry;
        $this->_productRepository = $productRepository;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getCurrentCategory()
    {        
        return $this->_registry->registry('current_category');
    }
    
    public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    }    
    
    public function getProductBySku($sku) {
        return $this->_productRepository->get($sku);
    }
    
}