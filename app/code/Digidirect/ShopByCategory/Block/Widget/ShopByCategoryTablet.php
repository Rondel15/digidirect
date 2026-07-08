<?php
/**
 * Author: Rondel
 */

namespace Digidirect\ShopByCategory\Block\Widget;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product\Attribute\Repository;

class ShopByCategoryTablet extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_categoryFactory;
    protected $_productCollectionFactory;
    protected $attribute;
    protected $attributeRepository;
    protected $_template = 'Digidirect_ShopByCategory::widget/shop-by-category-tablet.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Attribute $attribute,
        Repository $attributeRepository,
        array $data = []
    )
    {    
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->attribute = $attribute;
        $this->attributeRepository = $attributeRepository;
        parent::__construct($context, $data);
    }
    
    /* Get product count on a category */
    public function getProductCollectionCount($categoryId) 
    {
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addCategoriesFilter(['in' => $categoryId]);
        $productCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $productCollection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        return $productCollection->count();
    }
    
    public function getBrands()
    {
        $attributeModel = $this->attribute->load(222);
        $attributeCode = $attributeModel->getAttributeCode();
        $options = $this->attributeRepository->get($attributeCode)->getOptions();
        return $options;
    }
    
}
