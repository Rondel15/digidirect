<?php
/**
 * Author: Rondel
 */

namespace Digidirect\DigiSecondsMenu\Block\Widget;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product\Attribute\Repository;

class DigiSecondsMenu extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_categoryFactory;
    protected $_productCollectionFactory;
    protected $attribute;
    protected $attributeRepository;
    protected $_template = 'Digidirect_DigiSecondsMenu::widget/digiseconds-menu.phtml';

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
}
