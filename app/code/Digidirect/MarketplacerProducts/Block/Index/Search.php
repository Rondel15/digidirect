<?php

namespace Digidirect\MarketplacerProducts\Block\Index;

use Magento\Framework\View\Element\Template;

class Search extends Template
{
    protected $imageHelperFactory;
    
    public function __construct(
        Template\Context $context, 
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        array $data = [])
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->listProductBlock = $listProductBlock;
        $this->imageHelperFactory = $imageHelperFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getProductCollectionSearchResult($searchTerm) 
    {
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $productCollection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $productCollection->addAttributeToFilter("marketplacer_seller", array("neq" => 20329));
        $productCollection->addAttributeToFilter('name', array('like' => '%'.$searchTerm.'%'));
        return $productCollection;
    }
    
    public function getAddToCartPostParams($product)
    {
        return $this->listProductBlock->getAddToCartPostParams($product);
    }
    
    public function getProductPrice($product)
    {
        return $this->listProductBlock->getProductPrice($product);
    }
    
    public function getProductImage($product)
    {
        $imageUrl = $this->imageHelperFactory->create()
        ->init($product, 'product_base_image')->getUrl();
        return $imageUrl;
    }
    
}