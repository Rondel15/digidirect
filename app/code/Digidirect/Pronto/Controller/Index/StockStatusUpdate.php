<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\Product;

class StockStatusUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $_productCollectionFactory;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            Product $helper,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
            \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
            \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
            \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
            array $data = [])
    {
            $this->helper = $helper;
            $this->_productCollectionFactory = $productCollectionFactory;
            $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
            $this->sourceItemFactory = $sourceItemFactory;
            $this->sourceDataBySku = $sourceDataBySku;
            $this->productStatus = $productStatus;
            return parent::__construct($context);
    }

    public function execute(){
        set_time_limit(300);
        $collection = $this->_productCollectionFactory->create()->addFieldToSelect('*')
            ->setFlag('has_stock_status_filter', false)
//            ->addAttributeToFilter('sku', array('like' => '109429%'))
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->joinField('stock_item', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id');

        foreach ($collection as $key => $product) {

            $brand_text = $product->getAttributeText("brand");
           
            if(empty($brand_text)){
                echo $product->getName() . " - "  . $product->getSku() . " <br />";
            }
        }

        exit();
    }
}