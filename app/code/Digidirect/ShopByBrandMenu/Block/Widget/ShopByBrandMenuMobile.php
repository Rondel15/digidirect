<?php
/**
 * Author: Rondel Y. Dalumpines
 */

namespace Digidirect\ShopByBrandMenu\Block\Widget;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\CategoryFactory;
use Mageplaza\Shopbybrand\Block\Widget\AbstractBrand as AbstractBrand;

/**
 * Class ShopByBrandMenu
 */

class ShopByBrandMenuMobile extends AbstractBrand
{
    /**
     * @var string
     */
    protected $_template = 'Digidirect_ShopByBrandMenu::widget/shopbybrandmenumobile.phtml';

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * CategoryId constructor.
     *
     * @param Context $context
     * @param BrandFactory $brandFactory
     * @param CategoryFactory $categoryFactory
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        BrandFactory $brandFactory,
        CategoryFactory $categoryFactory,
        Helper $helper
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_brandFactory = $brandFactory;

        parent::__construct($context, $helper);
    }

    /**
     * @return string
     */
    public function getOptionIds()
    {
        //$str = 17;//$this->getData('category_id');
        $sql = 'main_table.cat_id IN (2,5,8,11)';
        $result = [];
        $brands = $this->_categoryFactory->create()->getCategoryCollection($sql, null)->getData();
        foreach ($brands as $brand => $item) {
            $result[] = $item['option_id'];
        }

        return implode(',', array_unique($result));
    }

    /**
     * get brand by option IDs
     *
     * @return Collection
     */
    public function getCollection()
    {
        $collection = $this->_brandFactory->create()->getBrandCollection(
            null,
            ['main_table.option_id' => ['in' => $this->getOptionIds()]]
        );

        return $collection;
    }
    
}
